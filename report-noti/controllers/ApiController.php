<?php

namespace app\controllers;

use app\models\Booking;
use app\models\Customer;
use app\services\BookingService;
use yii\filters\AccessControl;
use yii\web\Response;
use Yii;

class ApiController extends \yii\web\Controller
{
    protected $bookingService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->bookingService = new BookingService();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['actionDbStatistic', 'actionRevTrip'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionRevTrip()
    {
        return 'a';
    }

    public function actionCatch()
    {
        $response = Yii::$app->response;
        $response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isGet) {
            $response->data = ['status' => 'error', 'message' => 'Invalid request method.'];

            return $response;
        }
        $params = Yii::$app->request->get();
        $model = new Booking();
        $model->attributes = [
            'pickup_address' => $params['start-point'],
            'destination_address' => $params['end-point'],
            'round_trip' => isset($params['roundtrip']),
            'is_have_bill' => isset($params['vat']),
            'customer_name' => $params['your-name'],
            'customer_phone' => $params['your-phone'],
            'pickup_time' => $params['ngaydatxe'],
            'type_of_car' => $params['loai_xe'],
            'type' => SOURCE_TRIP_TYPE_MAIL_1,
        ];
        $bookingDuplicate = $model->findBookingDuplicate($model);
        $model->status = STATUS_BOOKING_CREATE;

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            if ($bookingDuplicate == 0) {
                $model->save();

                // Tạo customer nếu chưa tồn tại
                $this->bookingService->createCustomerFromBooking($model);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
        $response->data = ($bookingDuplicate == 0) ? ['status' => 'success', 'message' => 'API request successfully processed.', 'data' => $model] : ['status' => 'error', 'message' => 'The trip already exists in the system.'];

        return $response;
    }
}
