<?php

namespace app\services;

use app\helpers\MyHelper;
use app\helpers\MyStringHelper;
use app\models\Driver;
use app\models\MessageZns;
use app\models\PayTransactionApi;
use app\models\Status;
use ErrorException;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * Class PayTransactionService
 *
 * This class handles the trip-related operations.
 */
class PayTransactionService
{
    private const DEBUG_LOG_MAX_BYTES = 1048576;

    public SystemConfigurationService $systemConfigurationService;
    public BankTransactionService $bankTransactionService;
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->systemConfigurationService = new SystemConfigurationService();
        $this->bankTransactionService = new BankTransactionService();
        $this->notificationService = new NotificationService();
    }

    public function minusMoneySystem($postData, $admin): array
    {
        $systemConfiguration = $this->systemConfigurationService->getAllConfiguration();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $money = 0;
            $bankTransaction = $this->bankTransactionService->getBankTransaction($admin, $postData['type_bank']);
            $payTransaction = $this->storeCreateDefault($postData, $admin, $bankTransaction, 'minus');
            if (isset($bankTransaction['account_balance'])) {
                $money = $bankTransaction['account_balance'] - abs($postData['money']);
                $payTransaction->account_balance_after = $money;
            }
            if ($payTransaction->save()) {
                if (isset($bankTransaction['account_balance'])) {
                    Yii::$app->db->createCommand()
                        ->update('bank_transaction', ['account_balance' => $money], [
                            'admin_id' => $admin['id'],
                            'type_bank' => $postData['type_bank'],
                        ])
                        ->execute();
                }
                $transaction->commit();
                $this->sendMessageTelegram($payTransaction, $bankTransaction, true, 'Tru tien he thong thanh cong');
                Yii::$app->response->statusCode = Status::STATUS_OK;

                return [
                    'status' => Status::STATUS_OK,
                    'message' => 'Tru tien he thong thanh cong!',
                    'data' => [
                        'money_system' => $money,
                        'money' => (isset($postData['money']) ? -abs($postData['money']) : 0),
                        'id_pay_transaction' => (isset($postData['id_pay_transaction']) ? $postData['id_pay_transaction'] : ''),
                        'type_bank' => (isset($postData['type_bank']) ? $postData['type_bank'] : ''),
                        'content_bank' => (isset($postData['content_bank']) ? $postData['content_bank'] : ''),
                        'account_balance' => $money,
                        'user_id' => $admin->id,
                        'error' => false,
                    ],
                ];
            }
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
        $this->sendMessageTelegram($payTransaction, $bankTransaction, true, 'Tru tien he thong that bai');
        Yii::$app->response->statusCode = Status::STATUS_OK;

        return [
            'status' => Status::STATUS_OK,
            'message' => MESSAGE['minus_money_fail'],
            'data' => ['error' => true],
        ];
    }

    public function createPayTransaction($postData, $admin)
    {
        try {
            $check = false;
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $isOtpTransaction = $this->isOtpPostData($postData);
                if ($isOtpTransaction) {
                    $postData['type_bank'] = (string)MB_ONLINE_OTP_BANK;
                    if (! isset($postData['money']) || $postData['money'] === '') {
                        $postData['money'] = 1;
                    }
                }
                $bankTransaction = $this->bankTransactionService->getBankTransaction($admin, $postData['type_bank']);
                $telegramBankTransaction = $bankTransaction;
                if (
                    $isOtpTransaction &&
                    (
                        empty($telegramBankTransaction) ||
                        empty($telegramBankTransaction['token_tele']) ||
                        empty($telegramBankTransaction['chat_tele'])
                    )
                ) {
                    $telegramBankTransaction = $this->bankTransactionService->getBankTransaction($admin, 3);
                }
                $payTransaction = $this->storeCreateDefault($postData, $admin, $bankTransaction, 'recharge');
                $systemConfiguration = $this->systemConfigurationService->getAllConfiguration();
                $driver = $isOtpTransaction ? null : $this->findDriver($postData['phone']);
                // Cáº­p nháº­t tiá»n lÃ¡i xe (cÃ³ khuyáº¿n mÃ£i vÃ  khÃ´ng cÃ³ khuyáº¿n mÃ£i)
                $money = $isOtpTransaction ? 0 : (int)$this->calculatePromotionMoney($systemConfiguration, $payTransaction->money);

                if ($isOtpTransaction) {
                    $payTransaction->driver_id = 0;
                    $payTransaction->status = 1;
                    $payTransaction->message = 'Nhan OTP thanh cong!';
                    $check = true;
                } elseif ($driver) {
                    $payTransaction = $this->storeCreateSuccess($driver, $payTransaction);
                    $payTransaction->money_before = $driver->money;
                    $driver->money = $driver->money + $payTransaction->money + $money;
                    $payTransaction->money_after = $driver->money;

                    // Kiá»ƒm tra sá»‘ tiá»n trong tÃ i khoáº£n cÃ³ khá»›p khÃ´ng
                    if ($payTransaction->account_balance_before + $payTransaction->money != $payTransaction->account_balance_after) {
                        $payTransaction->message = 'So du khong hop le!';
                        $payTransaction->flag = TRANSACTION_FLAG_WARNING;
                    }
                } else {
                    $payTransaction->driver_id = 0;
                    $payTransaction->status = 0;
                    $payTransaction->message = 'Khong tim thay tai xe phu hop!';
                }

                // Táº¡o giao dá»‹ch náº¡p tiá»n
                if ($payTransaction->save()) {
                    if (! $isOtpTransaction && isset($bankTransaction['check_driver']) && $bankTransaction['check_driver']) {
                        if ($driver && $payTransaction->status) {
                            $driver->save();
                            $check = true;
                        }

                        if (isset($driver) && $driver != null) {
                            $data = [
                                'type' => NOTIFICATION_PAY_TYPE,
                                'money' => ($payTransaction->money + $money) / 10000,
                                'total_money' => isset($driver->money) ? $driver->money : 0,
                            ];
                            $this->notificationService->sendNotificationByUsername($driver, $admin, null, 'Nap tien thanh cong', 'He thong da nap ' . MyStringHelper::convertIntegerToPrice($payTransaction->money + $money) . 'd vao tai khoan cua tai xe ' . $driver->display_name, '', $data);
                        }
                    }

                    if (! $isOtpTransaction) {
                        Yii::$app->db->createCommand()->update('bank_transaction', ['account_balance' => $payTransaction->account_balance_after], [
                            'admin_id' => $admin['id'],
                            'type_bank' => $postData['type_bank'],
                        ])->execute();
                    }

                    $this->sendMessageTelegram($payTransaction, $telegramBankTransaction, $check);

                    if (! $isOtpTransaction && (! isset($bankTransaction['check_driver']) || ! $bankTransaction['check_driver'])) {
                        $check = true;
                    }

                    $transaction->commit();
                } else {
                    $saveErrors = json_encode($payTransaction->getErrors(), JSON_UNESCAPED_UNICODE);
                    $this->writeTelegramDebugLog(
                        'save_failed pay_transaction_id=' . ($postData['id_pay_transaction'] ?? '') .
                        ', type_bank=' . ($postData['type_bank'] ?? '') .
                        ', user_id=' . ($admin['id'] ?? '') .
                        ', errors=' . $saveErrors
                    );
                    $transaction->rollBack();
                    $check = false;
                    $payTransaction->message = 'Luu giao dich that bai: ' . $saveErrors;
                }
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }

            if ($check) {
                return $this->successResponse($payTransaction, $bankTransaction);
            }

            return $this->errorResponse(
                (isset($payTransaction['message']) ? $payTransaction['message'] : 'Co loi xay ra!'),
                (isset($payTransaction) ? $payTransaction : [])
            );
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('PayTransactionService - createPayTransaction() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function calculatePromotionMoney($systemConfiguration, $money = 0): float
    {
        $currentTime = time() + 7 * 3600;
        $promotion = 0;
        if (! empty($systemConfiguration['recharge_promotion'])) {
            $discountInfo = json_decode($systemConfiguration['recharge_promotion'], true);
            if (strtotime($systemConfiguration['recharge_time_start']) <= $currentTime && strtotime($systemConfiguration['recharge_time_end']) >= $currentTime) {
                foreach ($discountInfo as $key => $info) {
                    if ($money >= $info['value'] && (isset($discountInfo[$key + 1]) && $money < $discountInfo[$key + 1]['value'])) {
                        $promotion = $money * ($info['percent'] / 100);

                        break;
                    } elseif ($money >= $info['value'] && ! isset($discountInfo[$key + 1])) {
                        $promotion = $money * ($info['percent'] / 100);

                        break;
                    }
                }
            }
        }

        return round($promotion);
    }

    public function storeCreateDefault($postData, $admin, $bankTransaction, $method = ''): PayTransactionApi
    {
        $payTransaction = new PayTransactionApi();
        $payTransaction->type = PAY_SMS;
        $payTransaction->id_pay_transaction = (isset($postData['id_pay_transaction']) ? $postData['id_pay_transaction'] : '');
        $payTransaction->money = (isset($postData['money']) ? MyStringHelper::convertStringToInteger($postData['money']) : 0);
        $payTransaction->phone = (isset($postData['phone']) ? $postData['phone'] : '');
        $payTransaction->type_bank = (isset($postData['type_bank']) ? (string)$postData['type_bank'] : '');
        $payTransaction->content_bank = (isset($postData['content_bank']) ? $postData['content_bank'] : '');
        $payTransaction->driver_id = 0;
        $payTransaction->admin_id_accepted = 0;
        $payTransaction->status = 0;
        $payTransaction->message = 'Khong tim thay tai xe phu hop!';
        $payTransaction->account_balance_before = (isset($bankTransaction['account_balance']) ? $bankTransaction['account_balance'] : 0);
        $payTransaction->account_balance_after = (int)(isset($postData['account_balance']) ? $postData['account_balance'] : 0);
        $payTransaction->user_id = $admin['id'];

        if ($method == 'minus') {
            $payTransaction->message = 'Tru tien he thong thanh cong!';
            $payTransaction->status = 1;
            $payTransaction->money = (isset($postData['money']) ? -abs(MyStringHelper::convertStringToInteger($postData['money'])) : 0);
            $payTransaction->accepted_at = date('Y-m-d H:i:s');
        }

        return $payTransaction;
    }

    public function storeCreateSuccess($driver, $payTransaction)
    {
        $payTransaction->driver_id = $driver->id;
        $payTransaction->status = 1;
        $payTransaction->message = 'Tu dong nap tien cho tai xe thanh cong!';
        $payTransaction->accepted_at = date('Y-m-d H:i:s');

        return $payTransaction;
    }

    /**
     * Find a driver by phone number.
     *
     * This function searches for a driver in the database using the provided phone number.
     *
     * @param string $phone The phone number of the driver to search for.
     * @return array|ActiveRecord|null
     */
    public function findDriver(string $phone)
    {
        return Driver::find()->where(['username' => $phone])->one();
    }

    /**
     * Create a success response for a pay transaction.
     *
     * This function constructs a success response for a pay transaction, including details of the transaction
     * and an appropriate success message.
     *
     * @param PayTransactionApi $payTransaction The pay transaction to include in the response.
     * @return array The success response.
     */
    public function successResponse($payTransaction, $bankTransaction)
    {
        Yii::$app->response->statusCode = Status::STATUS_OK;
        $response = [
            'status' => Status::STATUS_OK,
            'data' => [
                'id' => $payTransaction->id,
                'type' => $payTransaction->type,
                'created_on' => $payTransaction->created_on,
                'id_pay_transaction' => $payTransaction->id_pay_transaction,
                'money' => $payTransaction->money,
                'phone' => $payTransaction->phone,
                'type_bank' => $payTransaction->type_bank,
                'content_bank' => $payTransaction->content_bank,
                'driver' => $payTransaction->driver,
                'admin_id_accepted' => $payTransaction->admin_id_accepted,
                'user_id' => $payTransaction->user_id,
                'status' => $payTransaction->status,
                'accepted_at' => $payTransaction->accepted_at,
                'result' => $payTransaction->message,
                'error' => false,
            ],
        ];
        if ($this->isOtpTransaction($payTransaction)) {
            $response['message'] = 'Nhan OTP thanh cong!';
        } elseif ($payTransaction->driver) {
            $response['message'] = 'Tu dong nap tien cho tai xe thanh cong!';
        } else {
            $response['message'] = 'Khong tim thay tai xe phu hop!';
        }
        if (! $this->isOtpTransaction($payTransaction) && (! isset($bankTransaction['check_driver']) || ! $bankTransaction['check_driver'])) {
            $response['message'] = 'Nap tien he thong thanh cong!';
        }

        return $response;
    }

    /**
     * Create an error response with a message and data.
     *
     * This function constructs an error response with the specified error message and data.
     *
     * @param string $message The error message to include in the response.
     * @param array $data Additional data to include in the response.
     * @return array The error response.
     */
    public function errorResponse($message, $payTransaction)
    {
        Yii::$app->response->statusCode = Status::STATUS_OK;

        return [
            'status' => Status::STATUS_OK,
            'message' => $message,
            'data' => [
                'id' => $payTransaction->id,
                'type' => $payTransaction->type,
                'created_on' => $payTransaction->created_on,
                'id_pay_transaction' => $payTransaction->id_pay_transaction,
                'money' => $payTransaction->money,
                'phone' => $payTransaction->phone,
                'type_bank' => $payTransaction->type_bank,
                'content_bank' => $payTransaction->content_bank,
                'driver' => $payTransaction->driver,
                'admin_id_accepted' => $payTransaction->admin_id_accepted,
                'status' => $payTransaction->status,
                'accepted_at' => $payTransaction->accepted_at,
                'result' => $payTransaction->message,
                'error' => true,
            ],
        ];
    }

    /**
     * Validate input data for the pay transaction.
     *
     * This function validates the input data for the pay transaction creation.
     *
     * @param array $data The input data to validate.
     * @return array An array of validation errors, if any.
     */
    public function validateInput(array $data): array
    {
        $requiredFields = ['id_pay_transaction', 'money', 'type_bank'];
        $errors = [];
        $isOtpTransaction = $this->isOtpPostData($data);
        if ($isOtpTransaction) {
            $data['type_bank'] = (string)MB_ONLINE_OTP_BANK;
            if (! isset($data['money']) || $data['money'] === '') {
                $data['money'] = 1;
            }
        }

        foreach ($requiredFields as $field) {
            if (! array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '' || ($field === 'money' && ! $isOtpTransaction && $data[$field] === '0')) {
                $errors[$field] = "$field la bat buoc.";
            }
        }

        if (! empty($data['money']) && ! is_numeric($data['money'])) {
            $errors['money'] = 'Dinh dang so tien khong hop le.';
        }

        if (! $this->checkIdPayTransaction($data['id_pay_transaction'], $data['type_bank'])) {
            $errors['id_pay_transaction'] = 'Chuyen da duoc nap vao he thong.';
        }

        if (! $this->checkContentPayTransaction($data['content_bank'], $data['type_bank'])) {
            $errors['content'] = 'Chuyen da duoc nap vao he thong.';
        }

        return $errors;
    }

    public function validateMinusSystem($data): array
    {
        $requiredFields = ['id_pay_transaction', 'money', 'type_bank'];
        $errors = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "$field la bat buoc.";
            }
        }

        if (! empty($data['money']) && ! is_numeric($data['money'])) {
            $errors['money'] = 'Dinh dang so tien khong hop le.';
        }

        return $errors;
    }

    public function sendMessageZns($payTransaction, $system, $driver, $status = 'fail')
    {
        $template_data = [];
        $template_id = $system['recharge_template_failed'];
        if ($status == 'success') {
            $template_data = [
                'driver_name' => ($driver->display_name ?? ''),
                'point' => round($payTransaction->money / 10000, 2),
                'total_point' => (isset($driver->money) ? round($driver->money / 10000, 2) : ''),
                'phone_number' => $payTransaction->phone,
            ];
            $template_id = $system['recharge_template_success'];
        } else {
            $template_data = [
                'driver_name' => ($driver->display_name ?? 'Khong xac dinh'),
                'msg_fail' => MESSAGE['api_transaction_account_balance_fail_driver'],
                'point' => round($payTransaction->money / 10000, 2),
                'phone_number' => $payTransaction->phone,
            ];
        }

        $data = [
            'phone' => '84' . substr($payTransaction->phone, 1),
            'template_id' => $template_id,
            'template_data' => $template_data,
            'tracking_id' => $payTransaction->id,
        ];
        $json_data = json_encode($data);
        $curl = curl_init(URL_SEND_ZNS);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_data),
                'access_token: ' . $system['zalo_access_token'],
            ],
        ]);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        $model = new MessageZns();
        $model->setAttributes([
            'trip_id' => $payTransaction->id,
            'template_id' => $template_id,
            'phone' => $payTransaction->phone,
            'code' => $response['error'] ?? '',
            'message' => $response['message'] ?? '',
            'template_data' => json_encode($data['template_data']),
        ]);
        $model->save();
    }

    public function sendMessageTelegram($payTransaction, $bankTransaction, $check, $message_tele = '')
    {
        $message = $this->buildMessage($payTransaction, $check, $message_tele, $bankTransaction);
        $this->writeTelegramDebugLog(
            'start pay_transaction_id=' . ($payTransaction->id ?? '') .
            ', type_bank=' . ($payTransaction->type_bank ?? '') .
            ', user_id=' . ($payTransaction->user_id ?? '') .
            ', has_token=' . (! empty($bankTransaction['token_tele']) ? 'yes' : 'no') .
            ', has_chat=' . (! empty($bankTransaction['chat_tele']) ? 'yes' : 'no')
        );

        if (empty($bankTransaction['token_tele']) || empty($bankTransaction['chat_tele'])) {
            $this->writeTelegramDebugLog(
                'config missing pay_transaction_id=' . ($payTransaction->id ?? '') .
                ', type_bank=' . ($payTransaction->type_bank ?? '') .
                ', user_id=' . ($payTransaction->user_id ?? '')
            );
            return;
        }

        $curl = curl_init('https://api.telegram.org/bot' . $bankTransaction['token_tele'] . '/sendMessage');
        $data = json_encode([
            'chat_id' => $bankTransaction['chat_tele'],
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data),
            ],
        ]);
        $telegramResponse = curl_exec($curl);
        $telegramError = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $this->writeTelegramDebugLog(
            'sent pay_transaction_id=' . ($payTransaction->id ?? '') .
            ', http_code=' . $httpCode .
            ', curl_error=' . $telegramError .
            ', response=' . $this->summarizeTelegramResponse((string)$telegramResponse)
        );
    }

    private function summarizeTelegramResponse(string $response): string
    {
        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            return json_encode([
                'ok' => $decoded['ok'] ?? null,
                'message_id' => $decoded['result']['message_id'] ?? null,
                'chat_id' => $decoded['result']['chat']['id'] ?? null,
                'error_code' => $decoded['error_code'] ?? null,
                'description' => $decoded['description'] ?? null,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return substr($response, 0, 300);
    }

    private function writeTelegramDebugLog(string $message): void
    {
        $logDir = Yii::getAlias('@app/log');
        if (! is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $logFile = $logDir . DIRECTORY_SEPARATOR . 'telegram.log';
        $this->rotateDebugLog($logFile);

        @file_put_contents(
            $logFile,
            date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL,
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
    /**
     * Get Phone Number From input string
     *
     * This function Get Phone Number From input string.
     *
     * @param $text
     * @return string An array of validation errors, if any.
     */
    public function getPhoneNumber($content)
    {
        // $phoneNumberPattern = '/\b(?:HD|hd|hD|Hd)?(03|05|07|08|09)[0-9]{8}(?:\s)?\b/';
        // preg_match($phoneNumberPattern, $text, $matches);
        // if (isset($matches[0])) {
        //     $phoneNumber = preg_replace('/^HD|hd/', '', $matches[0]);
        //     return trim($phoneNumber);
        // } else {
        //     return "";
        // }

        if (preg_match('/\bhd(\d{10})\b/i', $content, $matches)) {
            $phoneNumber = $matches[1]; // Láº¥y sá»‘ Ä‘iá»‡n thoáº¡i sau "hd"

            return trim($phoneNumber);
        } elseif (preg_match('/\b\d{10}\b/', $content, $matches)) {
            $phoneNumber = $matches[0]; // Láº¥y sá»‘ Ä‘iá»‡n thoáº¡i Ä‘áº§u tiÃªn tÃ¬m tháº¥y

            return trim($phoneNumber);
        } else {
            return '';
        }
    }

    private function buildMessage($payTransaction, $check, $message_tele, $bankTransaction): string
    {
        if ($this->isOtpTransaction($payTransaction)) {
            return $this->buildOtpMessage($payTransaction, $message_tele);
        }

        if ($check || ! isset($bankTransaction['check_driver']) || ! $bankTransaction['check_driver']) {
            return (! empty($message_tele) ? $message_tele : 'Nap tien thanh cong') . '
' . $payTransaction->content_bank;
        } else {
            return (! empty($message_tele) ? $message_tele : 'Nap tien that bai') . '
Ly do: <b>' . $payTransaction->message . '</b>
' . $payTransaction->content_bank;
        }
    }

    private function buildOtpMessage($payTransaction, $message_tele = ''): string
    {
        $otpCode = $this->extractOtpCode($payTransaction->phone ?: $payTransaction->content_bank);
        $amountLine = ((int)$payTransaction->money > 1)
            ? "\nSo tien: <b>" . htmlspecialchars((string)$payTransaction->money, ENT_QUOTES, 'UTF-8') . "</b>"
            : '';

        return (! empty($message_tele) ? $message_tele : 'Nhan OTP thanh cong') .
            "\nMa OTP: <b>" . htmlspecialchars($otpCode, ENT_QUOTES, 'UTF-8') . "</b>" .
            $amountLine .
            "\nNoi dung: " . htmlspecialchars((string)$payTransaction->content_bank, ENT_QUOTES, 'UTF-8');
    }

    private function isOtpPostData(array $data): bool
    {
        return (int)($data['type_bank'] ?? 0) === MB_ONLINE_OTP_BANK
            || stripos($data['content_bank'] ?? '', 'otp') !== false;
    }

    private function isOtpTransaction($payTransaction): bool
    {
        return (int)($payTransaction->type_bank ?? 0) === MB_ONLINE_OTP_BANK
            || stripos($payTransaction->content_bank ?? '', 'otp') !== false;
    }

    private function extractOtpCode(string $content): string
    {
        if (preg_match('/\b\d{4,10}\b/', $content, $matches)) {
            return $matches[0];
        }

        if (preg_match('/\b\d[\d\s-]{3,12}\d\b/', $content, $matches)) {
            return preg_replace('/\D/', '', $matches[0]);
        }

        return '';
    }

    /**
     * @throws DateMalformedStringException
     */
    private function checkIdPayTransaction($idPayTransaction, $typeBank): bool
    {
        $now = new \DateTime();
        $threeDaysAgo = (clone $now)->modify('-3 days')->format('Y-m-d H:i:s');
        $nowFormatted = $now->format('Y-m-d H:i:s');

        $exists = (new Query())
            ->from('pay_transaction')
            ->where([
                'id_pay_transaction' => $idPayTransaction,
                'type_bank' => $typeBank,
            ])
            ->andWhere(['between', 'created_on', $threeDaysAgo, $nowFormatted])
            ->exists();

        return $exists > 0 ? false : true;
    }

    private function checkContentPayTransaction($content, $typeBank)
    {
        $exists = (new Query())
            ->from('pay_transaction')
            ->where([
                'content_bank' => $content,
                'type_bank' => $typeBank,
            ])
            ->exists();


        return $exists > 0 ? false : true;
    }
}

