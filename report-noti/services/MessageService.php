<?php

namespace app\services;

use app\models\Admin;
use app\models\Driver;
use app\models\DriverToken;
use app\models\Message;
use app\models\Trip;
use app\repositories\DriverTokenRepository;
use app\repositories\NotificationLogsRepository;
use Yii;

class MessageService
{
    protected $driverTokenRepository;
    protected $notificationService;
    protected $driverService;

    public function __construct()
    {
        $this->driverTokenRepository = new DriverTokenRepository();
        $this->notificationService = new NotificationService();
        $this->driverService = new DriverService();
    }

    public function sendMessageForDriver(Message $message)
    {
        $driver = $this->driverService->getDriverByUsername($message->phone);

        if ($driver) {
            $adminId = Yii::$app->user->id;
            $admin = Admin::findOne($adminId);
            return $this->notificationService->sendNotificationByUsername($driver, $admin, null, $message->title, $message->content, '', []);
        }
        return [];
    }
}
