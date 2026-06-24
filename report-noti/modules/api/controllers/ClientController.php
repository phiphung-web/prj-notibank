<?php

namespace app\modules\api\controllers;

use app\helpers\MyHelper;
use app\models\Agency;
use app\models\Booking;
use app\models\Customer;
use app\services\BookingService;
use Yii;
use yii\base\ErrorException;
use yii\rest\Controller;
use yii\web\Response;

class ClientController extends Controller
{
    protected $bookingService;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->bookingService = new BookingService();
    }

    public function actionCatch()
    {
        try {
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;

            if (! Yii::$app->request->isPost) {
                $response->data = ['status' => 'error', 'message' => 'Invalid request method.'];

                return $response;
            }
            $params = Yii::$app->request->post();

            // Kiểm tra ngày đi
            $bookingDateTime = date('Y-m-d', strtotime($params['ngaydatxe']));
            if ($bookingDateTime < gmdate('Y-m-d', time() + 7 * 3600)) {
                $response->data = [
                    'status' => 'error',
                    'message' => 'Xin vui lòng chọn thời gian đi lớn hơn hoặc bằng ngày hiện tại.',
                ];

                return $response;
            }

            $model = new Booking();
            if (isset($params['token']) && ! empty($params['token'])) {
                $agency = $this->getAgencyByToken($params['token']);
            }

            $reversedArray = array_flip(TYPE_OF_CAR_LIST);
            $model->attributes = [
                'pickup_address' => $params['start-point'],
                'destination_address' => $params['end-point'],
                'round_trip' => isset($params['roundtrip']) ? $params['roundtrip'] : 0,
                'is_have_bill' => (bool)(isset($params['vat']) ? $params['vat'] : (isset($params['is_have_bill']) ? $params['is_have_bill'] : false)),
                'customer_name' => $params['your-name'],
                'customer_phone' => $params['your-phone'],
                'pickup_time' => date('Y-m-d H:i:s', strtotime($params['ngaydatxe'])),
                'type_of_car' => $reversedArray[$params['loai_xe']],
                'price_customer' => isset($params['price']) ? $params['price'] : 0,
                'agency_id' => (isset($agency->id) ? $agency->id : 0),
                'created_on' => gmdate('Y-m-d H:i:s', time() + 7 * 3600),
                'is_collect_money' => (isset($agency->agency_debt) && $agency->agency_debt ? false : true),
                'note' => isset($params['note']) ? $params['note'] : '',
                'type' => (isset($agency->id) ? SOURCE_TRIP_TYPE_AGENCY : (isset($params['source']) ? $params['source'] : SOURCE_TRIP_TYPE_MAIL_1)),
                'utm_source' => (isset($params['utm-source']) ? $params['utm-source'] : ''),
                'utm_campaign' => (isset($params['utm-campaign']) ? $params['utm-campaign'] : ''),
                'utm_medium' => (isset($params['utm-medium']) ? $params['utm-medium'] : ''),
                'remote_ip' => (isset($params['remote-ip']) ? $params['remote-ip'] : ''),
                'url' => (isset($params['url']) ? $params['url'] : ''),
                'voucher' => (isset($params['voucher']) ? $params['voucher'] : ''),
                'stop_point' => (isset($params['pause']) ? json_encode($params['pause']) : json_encode([])),
                'tracking_info' => (isset($params['tracking-info']) ? $params['tracking-info'] : ''),
                'website' => (isset($params['website']) ? $params['website'] : ''),
            ];
            $bookingDuplicate = $model->findBookingDuplicate($model);
            $model->status = STATUS_BOOKING_CREATE;
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                if ($bookingDuplicate == 0) {
                    $result = $model->save();
                    if (! $result) {
                        return ['status' => 'error', 'message' => 'Đăng ký thông tin thất bại.'];
                    }

                    // Tạo customer nếu chưa tồn tại
                    $this->bookingService->createCustomerFromBooking($model);
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            }
            $response->data = ($bookingDuplicate == 0) ? ['status' => 'success', 'message' => 'Đăng ký thông tin thành công.', 'data' => $model] : ['status' => 'error', 'message' => 'Thông tin của bạn đã được đăng ký trên hệ thống.'];

            return $response;
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('ClientController - actionCatch() - ' . $e->getMessage());
        }
    }



    public function getAgencyByToken($token = '')
    {
        try {
            return Agency::findOne(['token' => $token]);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('ClientController - actionCatch() - ' . $e->getMessage());
        }
    }
}
