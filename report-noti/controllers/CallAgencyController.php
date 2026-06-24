<?php

namespace app\controllers;

use app\models\AreaRelationship;
use app\models\Customer;
use app\models\Trip;
use app\services\CallService;
use Yii;
use yii\db\Query;
use yii\web\Response;

class CallAgencyController extends BaseController
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
        $data = Yii::$app->request->post();
        if (! Yii::$app->user->can('DAI_LY_ROLE') || (isset($data['search']) && $data['search'])) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (Yii::$app->request->isAjax) {
                $phone = $data['phone'];
                if ($phone) {
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

                        return [
                            'customer' => $customer,
                            'trip_future' => $trip_future,
                            'trip_old' => $trip_old,
                            'count_month' => $count_month,
                            'count_all' => $count_all,
                        ];
                    }
                }
            }
        }

        return [];
    }

    public function actionSearchAreaRelationship($term)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $query = new \yii\db\Query();
        $results = $query->select(['area_relationship.id', 'area_relationship.street', 'area.area_name'])
            ->from('area_relationship')
            ->leftJoin('area', 'area_relationship.area_id = area.id')
            ->leftJoin('vn_province', 'area_relationship.provinceid = vn_province.provinceid')
            ->where(['like', 'area_relationship.street', $term])
            ->orWhere(['like', 'area.area_name', $term])
            ->groupBy('area_relationship.id')
            ->all();

        $formattedResults = [];
        $check = [];
        foreach ($results as $result) {
            if (! in_array($result['street'] . ' - ' . $result['area_name'], $check)) {
                $formattedResults[] = [
                    'id' => $result['id'],
                    'street' => "{$result['street']} - {$result['area_name']}",
                ];
            }
            $check[] = $result['street'] . ' - ' . $result['area_name'];
        }

        return ['results' => $formattedResults];
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
