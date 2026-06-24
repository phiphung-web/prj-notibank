<?php

namespace app\controllers;

use app\models\AreaRelationship;
use app\models\Customer;
use app\models\Trip;
use app\services\CallService;
use Yii;
use yii\db\Query;
use yii\web\Response;

class CallController extends BaseController
{
    public $callService;

    public function init()
    {
        parent::init();
        $this->callService = new CallService();
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionGetdata()
    {
        $booking = [];
        $data = Yii::$app->request->post();
        if (! Yii::$app->user->can('DAI_LY_ROLE') || (isset($data['search']) && $data['search'])) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (Yii::$app->request->isAjax) {
                $phone = $data['phone'];
                if ($phone) {
                    $booking = (new Query())
                        ->select(['booking.*'])
                        ->from('booking')
                        ->where(['like', 'booking.customer_phone', $phone])
                        ->andWhere(['!=', 'booking.status', TRIP_STATUS_CONFIRM])
                        ->orderBy(['pickup_time' => SORT_DESC])
                        ->limit(3)
                        ->all();
                    $customer = Customer::find()->where(['like', 'phone', $phone])->one();
                    if ($customer) {
                        $trip_future = (new Query())
                            ->select(['trip.*'])
                            ->from('trip')
                            ->where(['like', 'customer_phone', $phone])
                            ->andWhere(['>=', 'pickup_time', date('Y-m-d H:i:s')])
                            ->andWhere(['<>', 'status', STATUS_TRIP_PENDING])
                            ->orderBy(['pickup_time' => SORT_ASC])
                            ->all();

                        $trip_old = (new Query())
                            ->select(['trip.*'])
                            ->from('trip')
                            ->where(['like', 'customer_phone', $phone])
                            ->andWhere(['<', 'pickup_time', date('Y-m-d H:i:s')])
                            ->andWhere(['<>', 'status', STATUS_TRIP_PENDING])
                            ->orderBy(['pickup_time' => SORT_DESC])
                            ->limit(3)
                            ->all();

                        $count_month = Trip::find()
                            ->where(['like', 'customer_phone', $phone])
                            ->andWhere(['trip.status' => [STATUS_TRIP_COMPLETE, STATUS_TRIP_DONE]])
                            ->andWhere(['between', 'pickup_time', date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59')])
                            ->count();

                        $count_all = Trip::find()
                            ->where(['like', 'customer_phone', $phone])
                            ->andWhere(['NOT IN', 'trip.status', [STATUS_TRIP_EXPIRE, STATUS_TRIP_CANCEL, STATUS_BID_PENDING]])
                            ->count();

                        $param = [
                            'customer' => $customer,
                            'trip_future' => $trip_future,
                            'trip_old' => $trip_old,
                            'count_month' => $count_month,
                            'count_all' => $count_all,
                            'booking' => $booking,
                        ];

                        if (isset($data['idCallBack']) && $data['idCallBack'] > 0 && $data['phoneCallBack'] == $data['phone']) {
                            $param['idCallBack'] = $data['idCallBack'];
                        }

                        return $param;
                    }
                }
            }
        }

        $param = ['booking' => $booking];
        if (isset($data['idCallBack']) && $data['idCallBack'] > 0 && $data['phoneCallBack'] == $data['phone']) {
            $param['idCallBack'] = $data['idCallBack'];
        }

        return $param;
    }

    public function actionSearchAreaRelationship()
    {
        $keyword = Yii::$app->request->get('keyword');
        Yii::$app->response->format = Response::FORMAT_JSON;

        return ['results' => $this->callService->searchAddress(! empty($keyword) ? $keyword : '')];
    }

    public function actionGetDetailArea()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $tableArea = [];
        $response = ['success' => false, 'data' => $tableArea];
        if (Yii::$app->request->isAjax) {
            $areaRelationshipId = Yii::$app->request->get('area_id');
            $schedule = Yii::$app->request->get('schedule');
            $address = Yii::$app->request->get('address');
            $phone = Yii::$app->request->get('phone');
            $scheduleList = Yii::$app->request->get('scheduleList');
            $idCallBack = Yii::$app->request->get('idCallBack');
            $areaRelationship = AreaRelationship::findOne(['id' => $areaRelationshipId]);
            if ($areaRelationship !== null) {
                $areaRelationshipList = AreaRelationship::find()->where([
                    'area_id' => $areaRelationship->area_id,
                    'area_relationship.districtid' => $areaRelationship->districtid,
                    'area_relationship.provinceid' => $areaRelationship->provinceid,
                    'area_relationship.street' => $areaRelationship->street,
                    'area_relationship.address' => $address,
                ])->joinWith(['areaConfigurationByTime', 'areaConfigurationByAddress', 'area'])->orderBy(['area_relationship.schedule' => SORT_ASC]);
                if (! empty($schedule)) {
                    $areaRelationshipList->andWhere(['IN', 'schedule', $schedule]);
                }
                $dataArea = $areaRelationshipList->asArray()->all();
                if (isset($dataArea) && is_array($dataArea) && count($dataArea)) {
                    foreach ($dataArea as $item) {
                        $tableArea[$item['type_of_car']] = [
                            'name' => TYPE_OF_CAR_LIST[$item['type_of_car']],
                            'id' => $item['type_of_car'],
                            'data' => [],
                        ];
                    }
                    foreach ($tableArea as $key => $value) {
                        foreach ($dataArea as $item) {
                            if ($item['type_of_car'] == $value['id']) {
                                $tableArea[$key]['data'][] = $item;
                            }
                        }
                    }
                }
                $response = [
                    'success' => true,
                    'dataArea' => $this->callService->renderHtmlCall($tableArea, $schedule, $phone, $idCallBack, $scheduleList),
                ];
            }
        }

        return $response;
    }
}
