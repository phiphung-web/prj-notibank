<?php

namespace app\modules\cronjob\controllers;

use app\services\NotificationService;
use app\services\SendMessageZnsService;
use app\services\SystemConfigurationService;
use app\services\TripService;
use yii\rest\ActiveController;

class MessageZnsController extends ActiveController
{
    public $modelClass = 'app\models\PayTransactionApi';
    public $systemConfigurationService;
    public $sendMessageZnsService;
    public $notificationService;
    public $tripService;

    public function init()
    {
        parent::init();
        $this->systemConfigurationService = new SystemConfigurationService();
        $this->sendMessageZnsService = new SendMessageZnsService();
        $this->tripService = new TripService();
        $this->notificationService = new NotificationService();
    }

    public function actionNotifyDriver()
    {
        $trips = $this->tripService->getTripsStartingIn20Minutes();
        pre($trips);
        $system = $this->systemConfigurationService->getAllConfiguration();
        $tripSend = [];
        if (isset($trips) && is_array($trips) && count($trips)) {
            foreach ($trips as $value) {
                if ($value['code'] != '0' && $value['code'] != '-118') {
                    $tripSend[] = $this->sendMessageZnsService->sendMessageNotifyDriver($value, $system);
                }
            }
        }

        pre($tripSend);
    }
}
