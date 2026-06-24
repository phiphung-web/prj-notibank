<?php

namespace app\controllers;

use app\models\AreaRelationship;
use app\models\Booking;
use app\models\CalculationFormula;
use app\models\Customer;
use app\models\SystemConfiguration;
use app\models\Trip;
use app\services\BookingService;
use app\services\CalculationFormulaService;
use app\services\CallService;
use app\services\SearchService;
use yii\db\Query;
use yii\web\Response;
use Yii;

class CallQuoteController extends BaseController
{
    public $callService;
    public $calculationFormulaService;
    public $searchService;
    public $bookingService;

    public function init()
    {
        parent::init();
        $this->callService = new CallService();
        $this->calculationFormulaService = new CalculationFormulaService();
        $this->searchService = new SearchService();
        $this->bookingService = new BookingService();
    }

    public function actionIndex()
    {
        $reason_reject_array = $this->getReasonReject();
        $source_call = $this->getSourceCall();

        return $this->render('index', compact(['reason_reject_array', 'source_call']));
    }

    public function actionGetdata()
    {
        $booking = [];
        $data = Yii::$app->request->post();
        if (!Yii::$app->user->can('DAI_LY_ROLE') || (isset($data['search']) && $data['search'])) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if (Yii::$app->request->isAjax) {
                $phone = $data['phone'];
                if ($phone) {
                    $booking = (new Query())
                        ->select(['booking.*'])
                        ->from('booking')
                        ->where(['like', 'booking.customer_phone', $phone])
                        ->leftJoin('type_of_car', 'booking.type_of_car = type_of_car.id')
                        ->leftJoin('trip', 'booking.id = trip.booking_id')
                        ->andWhere(['trip.booking_id' => null])
                        ->orderBy(['pickup_time' => SORT_DESC])
                        ->limit(3)
                        ->all();
                    $customer = Customer::find()
                        ->where(['like', 'phone', $phone])
                        ->one();
                    if ($customer) {
                        $trip_future = (new Query())
                            ->select(['trip.*'])
                            ->from('trip')
                            ->where(['like', 'customer_phone', $phone])
                            ->andWhere(['>=', 'pickup_time', date('Y-m-d H:i:s')])
                            ->andWhere(['<>', 'status', STATUS_TRIP_PENDING])
                            ->leftJoin('type_of_car', 'trip.type_of_car = type_of_car.id')
                            ->orderBy(['pickup_time' => SORT_ASC])
                            ->all();

                        $trip_old = (new Query())
                            ->select(['trip.*'])
                            ->from('trip')
                            ->where(['like', 'customer_phone', $phone])
                            ->andWhere(['<', 'pickup_time', date('Y-m-d H:i:s')])
                            ->andWhere(['<>', 'status', STATUS_TRIP_PENDING])
                            ->leftJoin('type_of_car', 'trip.type_of_car = type_of_car.id')
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

        return ['results' => $this->callService->searchAddress(!empty($keyword) ? $keyword : '')];
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
                $areaRelationshipList = AreaRelationship::find()
                    ->where([
                        'area_id' => $areaRelationship->area_id,
                        'area_relationship.districtid' => $areaRelationship->districtid,
                        'area_relationship.provinceid' => $areaRelationship->provinceid,
                        'area_relationship.street' => $areaRelationship->street,
                        'area_relationship.address' => $address,
                    ])
                    ->joinWith(['areaConfigurationByTime', 'areaConfigurationByAddress', 'area'])
                    ->orderBy(['area_relationship.schedule' => SORT_ASC]);
                if (!empty($schedule)) {
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

    public function actionFindPrice()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $query = [];
        $params = [
            'distance' => Yii::$app->request->get('distance') ? floatval(Yii::$app->request->get('distance')) : 0,
            'voucher' => Yii::$app->request->get('voucher') ? Yii::$app->request->get('voucher') : '0',
            'surcharge' => intval(str_replace('.', '', Yii::$app->request->get('surcharge') ? Yii::$app->request->get('surcharge') : 0)),
            'overnight' => Yii::$app->request->get('overnight'),
            'hourWait' => intval(str_replace('.', '', Yii::$app->request->get('hourWait') ? Yii::$app->request->get('hourWait') : 0)),
            'phone' => Yii::$app->request->get('phone') ? Yii::$app->request->get('phone') : '',
            'pickupAddress' => Yii::$app->request->get('pickupAddress') ? Yii::$app->request->get('pickupAddress') : '',
            'pickup_time' => Yii::$app->request->get('pickup_time') ?: '',
            'destinationAddress' => Yii::$app->request->get('destinationAddress') ? Yii::$app->request->get('destinationAddress') : '',
            'idCallBack' => Yii::$app->request->get('idCallBack'),
            'schedule' => Yii::$app->request->get('scheduleData'),
        ];
        $query = [
            'schedule' => Yii::$app->request->get('scheduleData'),
            'distance' => Yii::$app->request->get('distance') ? floatval(Yii::$app->request->get('distance')) : 0,
            ];

        Yii::info($params, __METHOD__);
        $searchModel = new CalculationFormula();
        $dataProvider = $searchModel->getNewCalculationFormula($query);
        $response = [
            'success' => true,
            'data' => $this->callService->renderHtmlCallQuote($dataProvider, $params),
        ];

        return $response;
    }

    public function actionGetDistance()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pickupAddress = Yii::$app->request->get('pickupAddress');
        $destinationAddress = Yii::$app->request->get('destinationAddress');

        if (empty($pickupAddress) || empty($destinationAddress)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập đầy đủ điểm đi và điểm đến',
                'distance' => 0
            ];
        }

        try {
            $params = [
                'addressStart' => $pickupAddress,
                'addressEnd' => $destinationAddress
            ];

            $distance = $this->searchService->getDistance($params);
            return [
                'success' => true,
                'distance' => $distance,
                'message' => 'Lấy khoảng cách thành công'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tính khoảng cách: ' . $e->getMessage(),
                'distance' => 0
            ];
        }
    }

    public function getSourceCall()
    {
        $source[SOURCE_TRIP_TYPE_CALL_1] = explode(
            '|',
            SystemConfiguration::find()
                ->select('content')
                ->where(['keyword' => 'call_phone_web_1'])
                ->scalar(),
        );

        $source[SOURCE_TRIP_TYPE_CALL_2] = explode(
            '|',
            SystemConfiguration::find()
                ->select('content')
                ->where(['keyword' => 'call_phone_web_2'])
                ->scalar(),
        );

        return $source;
    }

    public function getReasonReject()
    {
        $reason_reject = SystemConfiguration::find()
            ->select('content')
            ->where(['keyword' => 'reason_reject'])
            ->scalar();
        // Construct a new list of reasons for rejection by combining predefined constants and retrieved data
        $reason_reject = CHOOSE_REASON . '|' . $reason_reject;
        $reason_reject_array = explode('|', $reason_reject);

        $reason_reject_array[999] = ADD_TYPE_REJECT;

        return $reason_reject_array;
    }

    public function actionUpdateBooking()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $response = new Response();
        $response->format = Response::FORMAT_JSON;  // Đặt định dạng phản hồi là JSON

        try {
            $params = Yii::$app->request->post('form');
            $tripRelate = Trip::find()
                ->where(['customer_phone' => $params['customer_phone']])
                ->one();

            $model = new Booking();
            $model->modified_on = date('Y-m-d H:i:s');
            $model->created_on = date('Y-m-d H:i:s');
            $model->pickup_time = date('Y-m-d H:i:s');
            $model->round_trip = 0;
            $model->price_customer = 0;
            $model->note = $params['note'];
            $model->status = $params['status'];
            $model->type_reject = $params['type_reject'];
            $model->customer_phone = $params['customer_phone'];
            $model->is_have_bill = false;
            $model->is_collect_money = false;
            $model->agency_id = 0;
            $model->type = $params['source_trip'];
            $model->customer_name = (isset($tripRelate->customer_name) ? $tripRelate->customer_name : 'Khách mới');
            $model->pickup_address = (isset($tripRelate->pickup_address) ? $tripRelate->pickup_address : '');
            $model->type_of_car = (isset($tripRelate->type_of_car) ? $tripRelate->type_of_car : 1);
            $model->area = (isset($tripRelate->area) ? $tripRelate->area : '');
            $model->destination_address = (isset($tripRelate->destination_address) ? $tripRelate->destination_address : '');
            if ($model->save()) {
                // Tạo customer nếu chưa tồn tại
                $this->bookingService->createCustomerFromBooking($model);

                $transaction->commit();
                $response->data = ['status' => true, 'message' => 'Booking updated successfully.'];
            } else {
                $transaction->rollBack();
                $response->data = ['status' => false, 'message' => 'Failed to update booking.'];
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $response->data = ['status' => false, 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $response->data = ['status' => false, 'message' => $e->getMessage()];
        }

        return $response;
    }
}
