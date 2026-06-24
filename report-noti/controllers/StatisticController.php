<?php

namespace app\controllers;

use app\models\Agency;
use app\models\Booking;
use app\models\Customer;
use app\models\RequestCallBack;
use app\models\SearchBooking;
use app\models\SystemConfiguration;
use app\services\BookingService;
use app\services\CustomerServiceService;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use Yii;

class StatisticController extends BaseController
{
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
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    protected $bookingService;
    protected $customerServiceService;

    public function init()
    {
        parent::init();
        $this->bookingService = new BookingService();
        $this->customerServiceService = new CustomerServiceService();
    }

    /**
     * Retrieve a list of reasons for rejection.
     *
     * @return array An array containing reasons for rejection.
     */
    public function getReasonReject()
    {
        // Retrieve the 'content' from the 'SystemConfiguration' table where 'keyword' is 'reason_reject'
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

    public function actionIndex()
    {
        $searchModel = new SearchBooking();
        $reason_reject_array = $this->getReasonReject();
        $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $param = Yii::$app->request->queryParams;
        if (isset($param['SearchBooking']['status']) && $param['SearchBooking']['status'] !== STATUS_BOOKING_REJECT) {
            unset($param['SearchBooking']['type_reject']);
        }
        if (isset($param['SearchBooking']['status']) && $param['SearchBooking']['status'] == STATUS_BOOKING_WAITING) {
            $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d', strtotime('+2 days'));
        }
        // get agency
        $agencyList = [];
        $dataAgency = Agency::find()
            ->select(['id', 'name', 'status'])
            ->all();

        if (!empty($dataAgency)) {
            $agencyList = ArrayHelper::map($dataAgency, 'id', 'name');
        }

        // get data booking
        $dataProvider = $searchModel->search($param);

        return $this->render('index', compact('searchModel', 'dataProvider', 'reason_reject_array', 'agencyList'));
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'booking_cancel', [
                'id' => $model->id,
                'action' => ACTION_LIST['CANCEL'],
            ]),
            'action' => 'CANCEL',
        ]);

        return $this->redirect(['index']);
    }

    /**
     * Deletes many existing Statistic model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param mixed $arr_id
     * @return mixed
     */
    public function actionDeletelist($arr_id)
    {
        $arr_id = explode(',', $arr_id);
        Booking::deleteAll(['id' => $arr_id]);

        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'booking_delete', [
                'id' => $arr_id,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Booking::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Create a new booking.
     *
     * @return mixed The response, typically a redirection to the booking index page.
     * @throws Exception When encountering errors in the process.
     */
    public function actionCreate(Request $request)
    {
        $model = new Booking();
        $modelRequestCallBack = new RequestCallBack();
        if ($model->load(Yii::$app->request->post())) {
            $validationResult = $this->validateBooking([
                'model' => $model,
            ]);
            // If validation fails, render the appropriate view and display validation errors
            if ($validationResult === true) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    $model = $this->bookingService->storeBooking($model, 'create');
                    $model->save();

                    // Tạo customer nếu chưa tồn tại
                    $this->bookingService->createCustomerFromBooking($model);

                    // Kiểm tra tồn tại id của chăm sóc khách hàng thì xác nhận đã chăm sóc
                    if ($model && !empty($request->get('trip_id'))) {
                        $this->customerServiceService->actionConfirm($request->get('customer_service_id'), $request->get('trip_id'));
                    }

                    if (isset($_GET['idCallBack']) && !empty($_GET['idCallBack'])) {
                        $this->bookingService->updateCancelRequestCallBack();
                    }

                    $transaction->commit();
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'booking_create', [
                            'id' => $model->id,
                            'pickup_address' => $model->pickup_address,
                            'destination_address' => $model->destination_address,
                            'pickup_time' => $model->pickup_time,
                            'action' => ACTION_LIST['create'],
                        ]),
                        'action' => 'create',
                    ]);

                    return $this->redirect(['index']);
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }
        }

        if (isset($_GET['idCallBack'])) {
            $modelRequestCallBack = RequestCallBack::findOne(['id' => $_GET['idCallBack']]);
        }

        // Get additional booking data from the request parameters
        $data_booking = Yii::$app->request->get();
        if (isset($data_booking) && is_array($data_booking) && count($data_booking)) {
            // If there's customer phone data, retrieve customer information
            if (!empty($model) && $data_booking['customer_phone'] != '') {
                $customer = Customer::find()
                    ->where(['phone' => $data_booking['customer_phone']])
                    ->one();
                $model->customer_name = isset($customer['display_name']) ? $customer['display_name'] : '';
            }
            $model->customer_phone = isset($data_booking['customer_phone']) ? $data_booking['customer_phone'] : '';
            $model->price_customer = isset($data_booking['price_customer']) ? $data_booking['price_customer'] : '';
            $model->round_trip = isset($data_booking['round_trip']) ? $data_booking['round_trip'] : '';
            $model->pickup_address = isset($data_booking['pickup_address']) ? $data_booking['pickup_address'] : '';
            $model->area = isset($data_booking['area']) ? $data_booking['area'] : '';
            $model->destination_address = isset($data_booking['destination_address']) ? $data_booking['destination_address'] : '';
            $model->type_of_car = isset($data_booking['type_of_car']) ? $data_booking['type_of_car'] : '';
        }
        // Get the list of reasons for rejection
        $reason_reject_array = $this->getReasonReject();

        return $this->render('create', compact('model', 'modelRequestCallBack', 'reason_reject_array'));
    }

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionUpdateStatus($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);

        if ($model->load($request->post())) {
            $params = $request->post('Booking');

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->modified_on = date('Y-m-d H:i:s');
                $model->price_customer = (int) str_replace('.', '', $model->price_customer);
                $model->price_bid = (int) str_replace('.', '', $model->price_bid);
                $model->note = $params['note'];
                $model->status = $params['status'];
                $model->type_reject = isset($params['type_reject']) ? $params['type_reject'] : 0;
                if (isset($params['pickup_time']) && !empty($params['pickup_time'])) {
                    $model->pickup_time = $params['pickup_time'];
                }
                $model->save();
                $transaction->commit();
                if (isset($_POST['idCallBack'])) {
                    $dataRequestCallBack = RequestCallBack::find()->where([
                        'id' => $_POST['idCallBack'],
                        'status' => REQUEST_CALL_BACK_WAITING,
                    ])->one();
                    if ($dataRequestCallBack) {
                        $dataRequestCallBack->status = REQUEST_CALL_BACK_CONFIRM;
                        $dataRequestCallBack->save();
                    }
                }

                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'booking_update_status', [
                        'id' => $model->id,
                        'status' => (!empty(STATUS_BOOKING[$model->status]) ? STATUS_BOOKING[$model->status] : 'Không xác định'),
                        'action' => ACTION_LIST['update'],
                    ]),
                    'action' => 'update',
                ]);

                return $this->redirect(['statistic/index']);
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            }
        }

        return $this->redirect(['statistic/index']);
    }

    /**
     * Validate trip parameters
     *
     * @param array $params
     * @return bool
     */
    protected function validateBooking($params = []): bool
    {
        $check = true;
        if ($params['model']->type == SOURCE_TRIP_TYPE_AGENCY) {
            if ($params['model']->agency_id == null) {
                $params['model']->addError('agency_id', 'Vui lòng chọn nguồn đại lý');
                $check = false;
            }
        }
        if (isset($params['model']->customer_phone) && !preg_match('/^(?:\+)?[0-9]+$/', $params['model']->customer_phone)) {
            $params['model']->addError('customer_phone', 'Định dạng số điện thoại không hợp lệ.');
            $check = false;
        }
        if ($params['model']->status == 'REJECT' && $params['model']->type_reject == 0) {
            $params['model']->addError('type_reject', 'Vui lòng chọn loại từ chối!');
            $check = false;
        }

        return $check;
    }

    /**
     * @throws Exception
     */
    public function actionShow($id)
    {
        $userId = Yii::$app->user->id;
        $model = Booking::findOne($id);

        if ($model->load(Yii::$app->request->post())) {
            $validationResult = $this->validateBooking([
                'model' => $model,
            ]);

            if ($validationResult === true) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    $model = $this->bookingService->storeBooking($model, 'update');
                    $model->save();
                    $transaction->commit();

                    return $this->redirect(['index']);
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }
        }

        // Get the list of reasons for rejection
        $reason_reject_array = $this->getReasonReject();

        return $this->render('edit', [
            'model' => $model,
            'reason_reject_array' => $reason_reject_array,
        ]);
    }

    public function actionCheckNewBooking($since = 0)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $this->bookingService->getNewBookings();
    }

    public function actionMarkWaiting()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $id = (int) Yii::$app->request->post('id');
        if (!$id) {
            return ResponseHelper::renderResponse(HTTP_BAD_REQUEST, 'Thiếu id', []);
        }

        $b = Booking::findOne($id);
        if (!$b) {
            return ResponseHelper::renderResponse(HTTP_NOT_FOUND, 'Không tìm thấy booking', []);
        }

        if (!Yii::$app->user->can('booking.update')) {
            return ResponseHelper::renderResponse(HTTP_FORBIDDEN, 'Không có quyền', []);
        }

        if ($b->status !== STATUS_BOOKING_CREATE) {
            return ResponseHelper::renderResponse(HTTP_CONFLICT, 'Booking đã được xử lý', []);
        }

        $b->status = STATUS_BOOKING_WAITING;
        $b->modified_on = date('Y-m-d H:i:s');

        if ($b->save(false) === false) {
            return ResponseHelper::renderResponse(HTTP_INTERNAL_SERVER_ERROR, 'Không thể cập nhật trạng thái', []);
        }

        return ResponseHelper::renderResponse(HTTP_OK, 'OK', [
            'id' => (int) $b->id,
            'status' => $b->status,
            'modified_on' => $b->modified_on,
        ]);
    }
}
