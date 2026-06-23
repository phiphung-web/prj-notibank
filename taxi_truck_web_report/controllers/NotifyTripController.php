<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\models\Trip;
use app\services\SystemConfigurationService;
use Yii;
use yii\base\ErrorException;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * NotifyTripController
 */
class NotifyTripController extends BaseController
{
    protected $tripService;
    public $systemConfigurationService;

    public function __construct($id, $module, $config = [])
    {
        $this->tripService = Yii::$app->tripService;
        parent::__construct($id, $module, $config);
    }

    public function init()
    {
        parent::init();
        $this->systemConfigurationService = new SystemConfigurationService();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * .
     * @return mixed
     */
    public function actionIndex()
    {
        try {
            $systemVip = $this->systemConfigurationService->getConfigByKeyword('driver_rank_VIP');
            $systemGold = $this->systemConfigurationService->getConfigByKeyword('driver_rank_GOLD');
            $tripsGold = $this->tripService->getTripsStartingInXMinutes($systemGold, 'GOLD');
            // $tripsVip = $this->tripService->getTripsStartingInXMinutes($systemVip, "VIP");
            $tripsVip = $this->tripService->getTripNormal(15);

            return $this->render('/notify-trip/index', compact(['tripsGold', 'tripsVip', 'systemVip', 'systemGold']));
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('NotifyTripController - actionIndex() - ' . $e->getMessage());
        }
    }

    public function actionReload()
    {
        try {
            $systemVip = $this->systemConfigurationService->getConfigByKeyword('driver_rank_VIP');
            $systemGold = $this->systemConfigurationService->getConfigByKeyword('driver_rank_GOLD');
            $tripsGold = $this->tripService->getTripsStartingInXMinutes($systemGold, 'GOLD');
            // $tripsVip = $this->tripService->getTripsStartingInXMinutes($systemVip, "VIP");
            $tripsVip = $this->tripService->getTripNormal(15);

            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'GOLD' => $tripsGold,
                'VIP' => $tripsVip,
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('NotifyTripController - actionReload() - ' . $e->getMessage());
        }
    }

    public function actionUpdateStatus()
    {
        try {
            $id = Yii::$app->request->post('id');
            $type = Yii::$app->request->post('type');
            $trip = Trip::findOne($id);

            if ($trip) {
                if ($type == 'GOLD') {
                    $condition = ['send_gold' => true];
                } else {
                    $condition = ['send_vip' => true];
                }
                Yii::$app->db->createCommand()
                    ->update('trip', $condition, ['id' => $id])
                    ->execute();
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ['success' => true];
            } else {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ['success' => false, 'error' => 'Không tìm thấy chuyến xe.'];
            }
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('NotifyTripController - actionUpdateStatus() - ' . $e->getMessage());
        }
    }
}
