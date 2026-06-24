<?php

namespace app\controllers;

use app\helpers\MyStringHelper;
use app\models\Driver;
use app\models\Trip;
use app\services\DashboardService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DashBoardController extends BaseController
{
    private $dashboardService;

    public function init()
    {
        parent::init();
        $this->dashboardService = new DashboardService();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [],
            ],
        ];
    }

    public function actionIndex()
    {
        if (isset($this->roleCurrentUser[DAI_LY_ROLE])) {
            return $this->redirect('/call-agency');
        } else {
            return $this->render('index');
        }
    }

    // public function actionGetStatisticMoneyTripFull()
    // {
    //     return json_encode($this->dashboardService->statisticMoneyTripFull());
    // }

    // public function actionGetStatisticMoneyTripDay()
    // {
    //     return json_encode($this->dashboardService->statisticMoneyTripDay());
    // }

    public function actionDbStatistic()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $obj = new \stdClass();
        $obj->numDriver = MyStringHelper::convertIntegerToPrice(Driver::find()->count());
        $obj->totalMoney = MyStringHelper::convertIntegerToPrice(Driver::find()->sum('money'));
        $obj->totalTrip = MyStringHelper::convertIntegerToPrice(Trip::find()->where(['status' => 'DONE'])->count());
        $obj->revenue = MyStringHelper::convertIntegerToPrice(\Yii::$app->db->createCommand("select sum(A.price_customer - B.price)  from trip A left join bid B ON A.id = B.trip_id  where A.status = 'DONE' and B.status = 'SUCCESS'")->queryScalar());

        return $obj;
    }

    public function actionGetStatisticDay()
    {
        $date = Yii::$app->request->post('date');
        $date = explode(' to ', $date);
        $startDate = date('Y-m-d 00:00:00', strtotime($date[0]));
        $endDate = date('Y-m-d 23:59:59', strtotime($date[1]));

        return json_encode($this->dashboardService->statisticMoneyTripByPeriod($startDate, $endDate));
    }

    public function actionGetTotalTrip()
    {
        $date = Yii::$app->request->post('date');
        $date = explode(' to ', $date);
        $startDate = date('Y-m-d 00:00:00', strtotime($date[0]));
        $endDate = date('Y-m-d 23:59:59', strtotime($date[1]));

        return json_encode($this->dashboardService->totalTripByPeriod($startDate, $endDate));
    }
}
