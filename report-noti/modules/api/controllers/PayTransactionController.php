<?php

namespace app\modules\api\controllers;

use app\helpers\BehaviorsFromParamsHelper;
use app\helpers\MyHelper;
use app\models\AccessToken;
use app\models\PayTransactionApi;
use app\models\Status;
use app\services\PayTransactionService;
use app\services\SystemConfigurationService;
use Yii;
use yii\base\ErrorException;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;

class PayTransactionController extends ActiveController
{
    private const DEBUG_LOG_MAX_BYTES = 1048576;

    public $modelClass = 'app\models\PayTransactionApi';
    public $systemConfigurationService;
    public $payTransactionService;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = BehaviorsFromParamsHelper::behaviors($behaviors);

        return $behaviors;
    }

    public function init()
    {
        parent::init();
        $this->systemConfigurationService = new SystemConfigurationService();
        $this->payTransactionService = new PayTransactionService();
    }

    /**
     * List pay transactions for SMS payments.
     *
     * This action lists pay transactions for SMS payments, and it requires the user to have the '/api/pay-transaction/list' permission.
     *
     * @throws MethodNotAllowedHttpException if the request method is not GET.
     * @throws ForbiddenHttpException if the user does not have the required permission.
     *
     * @return array The response containing a list of pay transactions.
     */
    public function actionList()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }
        if (! Yii::$app->user->can('/api/pay-transaction/list') && ! Yii::$app->user->can('/api/pay-transaction/*')) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        try {
            $page = Yii::$app->request->get('page', 1);
            $perpage = Yii::$app->request->get('perpage', 10);
            $status = Yii::$app->request->get('status');
            $query = PayTransactionApi::find();
            $query->andWhere(['type' => PAY_SMS]);
            if (isset($status)) {
                $query->andWhere(['status' => Yii::$app->request->get('status')]);
            }
            $query->andFilterWhere(['is_disabled' => 0]);
            $offset = ($page - 1) * $perpage;
            $payTransactions = $query->offset($offset)
                ->limit($perpage)
                ->all();
            $totalCount = $query->count();
            $totalPages = ceil($totalCount / $perpage);
            $response = [
                'status' => Status::STATUS_OK,
                'message' => 'Thành công',
                'data' => [
                    'page' => $page,
                    'page_size' => $perpage,
                    'total_pages' => $totalPages,
                    'total_count' => $totalCount,
                    'transactions' => $payTransactions,
                ],
            ];

            return $response;
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('PayTransactionController - actionList() - ' . $e->getMessage());
        }
    }

    /**
     * Process a recharge for a pay transaction.
     *
     * This action handles the recharge process for a pay transaction. It expects a POST request with valid parameters.
     * If the parameters are valid, it creates a new pay transaction, associates it with a driver (if found),
     * updates the driver's balance, and returns a success response. Otherwise, it returns an error response.
     *
     * @throws MethodNotAllowedHttpException if the request method is not POST.
     *
     * @return array The response containing either a success message or an error message.
     */
    public function actionRecharge()
    {
        if (! Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $postData = Yii::$app->request->post();
            $isOtpTransaction = (int)($postData['type_bank'] ?? 0) === MB_ONLINE_OTP_BANK
                || stripos($postData['content_bank'] ?? '', 'otp') !== false;
            $this->writeRechargeDebugLog('received', $postData, [
                'is_otp' => $isOtpTransaction,
                'has_authorization' => ! empty(Yii::$app->request->getHeaders()->get('authorization')),
            ]);
            if (! $isOtpTransaction && (! isset($postData['phone']) || ! preg_match('/(03|05|07|08|09)[0-9]{8}/', $postData['phone']) || strlen($postData['phone']) != 10)) {
                $postData['phone'] = $this->payTransactionService->getPhoneNumber($postData['content_bank']);
            }
            $admin = AccessToken::findByToken(Yii::$app->request->getHeaders()->get('authorization'));
            $errors = $this->payTransactionService->validateInput($postData);
            if (! empty($errors)) {
                $this->writeRechargeDebugLog('validation_failed', $postData, [
                    'errors' => $errors,
                    'admin_id' => $admin['id'] ?? null,
                ]);
                Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;

                return [
                    'success' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Invalid parameter',
                    'errors' => $errors,
                ];
            }

            $response = $this->payTransactionService->createPayTransaction($postData, $admin);
            $this->writeRechargeDebugLog('processed', $postData, [
                'admin_id' => $admin['id'] ?? null,
                'response_message' => $response['message'] ?? null,
                'response_error' => $response['data']['error'] ?? null,
            ]);

            return $response;
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('PayTransactionController - actionRecharge() - ' . $e->getMessage());
        }
    }

    public function actionMinusSystem()
    {
        if (! Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $postData = Yii::$app->request->post();
            $admin = AccessToken::findByToken(Yii::$app->request->getHeaders()->get('authorization'));
            $errors = $this->payTransactionService->validateMinusSystem($postData);
            if (! empty($errors)) {
                Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;

                return [
                    'success' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Invalid parameter',
                    'errors' => $errors,
                ];
            }

            return $this->payTransactionService->minusMoneySystem($postData, $admin);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('PayTransactionController - actionMinusSystem() - ' . $e->getMessage());
        }
    }

    private function writeRechargeDebugLog(string $stage, array $postData, array $context = []): void
    {
        $content = (string)($postData['content_bank'] ?? '');
        $payload = array_merge([
            'stage' => $stage,
            'created_at' => date('Y-m-d H:i:s'),
            'type_bank' => $postData['type_bank'] ?? null,
            'money' => $postData['money'] ?? null,
            'account_balance' => $postData['account_balance'] ?? null,
            'id_pay_transaction' => $postData['id_pay_transaction'] ?? null,
            'has_phone' => ! empty($postData['phone']),
            'content_length' => strlen($content),
            'content_contains_otp' => stripos($content, 'otp') !== false,
        ], $context);

        $logDir = Yii::getAlias('@app/log');
        if (! is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $logFile = $logDir . DIRECTORY_SEPARATOR . 'recharge_debug.log';
        $this->rotateDebugLog($logFile);

        @file_put_contents(
            $logFile,
            json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }

    private function rotateDebugLog(string $logFile): void
    {
        if (! is_file($logFile) || filesize($logFile) < self::DEBUG_LOG_MAX_BYTES) {
            return;
        }

        $backupFile = $logFile . '.1';
        if (is_file($backupFile)) {
            @unlink($backupFile);
        }
        @rename($logFile, $backupFile);
    }
}
