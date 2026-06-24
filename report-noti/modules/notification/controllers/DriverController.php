<?php

namespace app\modules\notification\controllers;

use app\models\Driver;
use app\services\NotificationService;
use Yii;
use yii\rest\Controller;

class DriverController extends Controller
{
    protected string $modelClass = 'app\models\NotificationLogs';
    protected NotificationService $notificationService;

    public function init(): void
    {
        parent::init();
        $this->notificationService = new NotificationService();
    }

    public function actionSaveToken(): \yii\web\Response
    {
        $request = Yii::$app->request;
        $token = $request->get('token', 'Default Title');
        $username = $request->get('username', '');

        $result = $this->notificationService->saveToken($username, $token);

        return $this->asJson($result);
    }

    public function actionSend(): \yii\web\Response
    {
        $request = Yii::$app->request;
        $title = $request->get('title', 'Default Title');
        $body = $request->get('body', 'Default Body');
        $username = $request->get('username', '');

        $driver = Driver::findOne(['username' => $username]);
        if (! $driver || ! $driver->token_fcm) {
            return $this->asJson([
                'status' => 'error',
                'message' => 'Không tìm thấy lái xe phù hợp',
            ]);
        }

        $result = $this->notificationService->sendNotificationByUsername($driver, null, null, $title, $body);

        return $this->asJson($result);
    }
}
