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
            if (! $isOtpTransaction && (! isset($postData['phone']) || ! preg_match('/(03|05|07|08|09)[0-9]{8}/', $postData['phone']) || strlen($postData['phone']) != 10)) {
                $postData['phone'] = $this->payTransactionService->getPhoneNumber($postData['content_bank']);
            }
            $admin = AccessToken::findByToken(Yii::$app->request->getHeaders()->get('authorization'));
            $errors = $this->payTransactionService->validateInput($postData);
            if (! empty($errors)) {
                Yii::$app->response->statusCode = Status::STATUS_BAD_REQUEST;

                return [
                    'success' => Status::STATUS_BAD_REQUEST,
                    'message' => 'Invalid parameter',
                    'errors' => $errors,
                ];
            }

            return $this->payTransactionService->createPayTransaction($postData, $admin);
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
}
