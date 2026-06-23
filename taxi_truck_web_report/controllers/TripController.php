<?php

namespace app\controllers;

use app\services\TripService;
use app\helpers\MyHelper;
use yii\helpers\ArrayHelper;
use app\helpers\MyStringHelper;
use app\jobs\OpenTripJob;
use app\models\Admin;
use app\models\Bid;
use app\models\Booking;
use app\models\Car;
use app\models\ConfigAutoSale;
use app\models\Driver;
use app\models\DriverSub;
use app\models\MessageZns;
use app\models\RequestCallBack;
use app\models\SearchTrip;
use app\models\SystemConfiguration;
use app\models\Trip;
use app\models\TripDelete;
use app\models\TripGroup;
use app\models\TripReturn;
use app\services\BidService;
use app\services\BookingService;
use app\services\CustomerService;
use app\services\CustomerServiceService;
use app\services\DriverService;
use app\services\SendMessageZnsService;
use app\services\TripGroupService;
use app\services\NotificationService;
use app\services\VoucherService;
use DateTime;
use DateTimeZone;
use Yii;
use yii\base\ErrorException;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

// use app\services\NotificationService;

/**
 * TripController implements the CRUD actions for Trip model.
 */
class TripController extends BaseController
{
    protected $tripService;
    public $sendMessageZnsService;
    public $customerService;
    public $bidService;
    public $tripGroupService;
    public $voucherService;
    protected $customerServiceService;
    public $bookingService;

    public $driverService;
    // private NotificationService $notificationService;

    public function __construct($id, $module, $config = [])
    {
        $this->tripService = Yii::$app->tripService;
        parent::__construct($id, $module, $config);
    }

    public function init()
    {
        parent::init();
        $this->voucherService = new VoucherService();
        $this->sendMessageZnsService = new SendMessageZnsService();
        $this->bidService = new BidService();
        $this->customerService = new CustomerService();
        $this->customerServiceService = new CustomerServiceService();
        $this->tripGroupService = new TripGroupService();
        $this->bookingService = new BookingService();
        $this->driverService = new DriverService();
        // $this->notificationService = new NotificationService();
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Lists all Trip models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchTrip();
        $tripService = new TripService();
        $searchModel->pickupTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $userList = $tripService->getUserList();

        return $this->render('index-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'userList' => $userList,
        ]);
    }

    public function actionMoney($id = 0)
    {
        $model = Trip::find()->where([
            'trip.id' => $id,
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ])->joinWith(['tripGroup', 'agency', 'bid'])->one();
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (isset($post['status_trip']) && !empty($post['status_trip'])) {
                $result = $this->tripService->updateDebtTrip($model, $post);
            }
            Yii::$app->session->setFlash('success', 'Cập nhật thành công!');

            return $this->redirect(['money', 'id' => $id]);
        }

        return $this->render('money', compact('model'));
    }

    public function actionSearchData()
    {
        $searchModel = new SearchTrip();
        $searchModel->pickupTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        $userList = ArrayHelper::map(
            Admin::find()->orderBy('username')->all(),
            'id',
            'username'
        );

        if (Yii::$app->request->isAjax) {
            $tripModel = new Trip();
            $tripList = $dataProvider->getModels();
            $htmlNew = $this->renderPartial('table', compact(['searchModel', 'tripList', 'tripModel', 'dataProvider']));

            return $htmlNew;
        } else {
            return $this->render('index-list', compact([
                'searchModel',
                'dataProvider',
                'userList',
            ]));
        }
    }

    /**
     * Perform return action for a trip.
     *
     * @param int $id The ID of the trip to be processed.
     *
     * @return mixed The response, usually a redirection to the trip index page.
     * @throws \Exception When encountering errors in the process.
     */
    public function actionReturn($id)
    {
        $model = Trip::findOne($id);
        $bid = $this->bidService->getBidNewestByTripId($id);

        if (!Yii::$app->user->can('ADMIN_ROLE') && !Yii::$app->user->can('QUAN_LY_NHUNG_ROLE') && ($model->status !== 'DONE' || !$bid)) {
            throw new \Exception($model->status !== 'DONE' ? 'Lịch này chưa nhận' : 'Có lỗi xảy ra');
        }

        $modelTripGroup = $this->tripGroupService->getTripGroup($model);
        $tripReturn = new TripReturn();
        $tripReturn->money = $model->price_customer - $bid->price;
        $dataOld = [
            'price_customer' => $model->price_customer,
        ];
        $request_post = Yii::$app->request->post();
        if ($model->load($request_post) && $modelTripGroup->load($request_post) && $tripReturn->load($request_post)) {
            $bid = $this->bidService->getBidNewestByTripId($id);
            // Kiểm tra nếu cập nhật giá tiền khi chuyến đã được bid thì báo lỗi
            if (isset($bid->status) && $bid->status == STATUS_BID_SUCCESS && $dataOld['price_customer'] != MyStringHelper::convertStringToInteger($model->price_customer)) {
                Yii::$app->session->setFlash('error', 'Chuyến xe đã được bid, xin vui lòng không thay đổi giá báo khách!');

                return $this->refresh();
            }

            $validationResult = $this->tripService->validateTrip([
                'modelTripGroup' => $modelTripGroup,
                'model' => $model,
                'tripReturn' => (isset($tripReturn) ? $tripReturn : []),
                'bid' => $bid,
            ]);
            if ($validationResult === false) {
                return $this->render('return', compact('model', 'modelTripGroup', 'tripReturn'));
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $params = [
                    'tripReturn' => $tripReturn,
                    'refund' => (bool) $tripReturn->refund,
                ];
                $result = $this->tripService->processTripReturn($model, $modelTripGroup, $bid, $params);
                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }

                $model = $result['model'];
                if (isset($_POST['check_cancel']) && $_POST['check_cancel']) {
                    $model->status = STATUS_TRIP_CANCEL;
                }
                if (!$model->save()) {
                    $errors = $model->getFirstErrors();
                    throw new \Exception('Lỗi khi lưu chuyến đi: ' . implode(', ', $errors));
                }

                $transaction->commit();

                // Lưu log
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_putback_return', [
                        'id' => $model->id,
                        'phone' => $model->customer_phone,
                        'type' => $tripReturn->refund ? 'hoàn ' . MyStringHelper::convertIntegerToPrice($tripReturn->money) . 'đ' : 'không hoàn tiền',
                    ]),
                    'action' => 'update',
                ]);

                return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                MyHelper::sendErrorToTelegramBot('TripController - actionReturn() - ' . $e->getMessage());
                throw $e;
            } catch (\Throwable $e) {
                MyHelper::sendErrorToTelegramBot('TripController - actionReturn() - ' . $e->getMessage());
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('return', [
            'model' => $model,
            'modelTripGroup' => $modelTripGroup,
            'tripReturn' => $tripReturn,
            'module' => 'trip',
        ]);
    }

    public function actionCancel()
    {
        $params = Yii::$app->request->post();
        $id = $params['id'];
        $note = $params['note'];
        $model = Trip::findOne($id);
        if ($model->status == STATUS_TRIP_DONE || $model->status == STATUS_TRIP_COMPLETE) {
            throw new \Exception(' Lịch đã điều!');
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            $command = Yii::$app->db->createCommand('update trip set status = :status, note = :note where id = :id');
            $command->bindParam('id', $id);
            $status = STATUS_TRIP_CANCEL;
            $command->bindParam('status', $status);
            $command->bindParam('note', $note);
            $command->execute();

            $command = Yii::$app->db->createCommand("UPDATE bid set status = 'INVALID' WHERE trip_id = :id");
            $command->bindParam('id', $id);
            $command->execute();

            $transaction->commit();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'trip_action', [
                    'id' => $model->id,
                    'phone' => $model->customer_phone,
                    'action' => ACTION_LIST['cancel'],
                ]),
                'action' => 'cancel',
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return $this->redirect(['index']);
    }

    /**
     * Confirm a trip and update relevant statuses and debt collection.
     *
     * @param int $id The ID of the trip to be confirmed.
     *
     * @return mixed The response, usually a redirection to the trip index page.
     */
    public function actionConfirm()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model = $this->findModel($_GET['id']);
            $modelTripGroup = $this->tripGroupService->getTripGroup($model);

            // Kiểm tra nếu bid qua zalo thì trạng thái là thành công, ngược lại là mở bán
            $status = ($modelTripGroup->zalo_seller_id > 0 && $modelTripGroup->group_zalo_id > 0 ? STATUS_TRIP_COMPLETE : STATUS_TRIP_OPEN);

            // Check if the trip is in pending status
            if ($model->status == STATUS_TRIP_PENDING) {
                $driverIdCreated = 0;

                // Thêm mới khách hàng
                $this->customerService->createCustomer($model);

                // Cập nhật trạng thái booking (nếu có)
                $idBooking = !empty($model->booking_id) ? $model->booking_id : (isset($_GET['id_booking']) ? $_GET['id_booking'] : null);
                $modelBooking = $idBooking ? $this->bookingService->findOne($idBooking) : $this->bookingService->findByCustomerPhone($model->customer_phone);
                if ($modelBooking) {
                    $modelBooking->status = STATUS_BOOKING_CONFIRM;
                    $modelBooking->price_customer = (string) $modelBooking->price_customer;
                    $modelBooking->save();
                }
                $driverIdCreated = (isset($modelBooking->driver_id_created) ? $modelBooking->driver_id_created : 0);
                $idBooking = (isset($modelBooking->id) ? $modelBooking->id : 0);

                // Cập nhật trạng thái yêu cầu gọi lại (nếu có)
                if (isset($_GET['idCallBack']) && ($modelRequestCallBack = RequestCallBack::findOne(['id' => $_GET['idCallBack'], 'status' => REQUEST_CALL_BACK_WAITING]))) {
                    $modelRequestCallBack->status = REQUEST_CALL_BACK_CONFIRM;
                    $modelRequestCallBack->save();
                }
                // Kiểm tra tồn tại id của chăm sóc khách hàng thì xác nhận đã chăm sóc
                if ($model && !empty($this->request->get('trip_id'))) {
                    $this->customerServiceService->actionConfirm($this->request->get('customer_service_id'), $this->request->get('trip_id'));
                }

                // Cập nhật trạng thái chuyến đi vào DB
                if (
                    Yii::$app->db->createCommand()
                    ->update('trip', [
                        'status' => $status,
                        'booking_id' => (int) $idBooking,
                        'driver_id_created' => $driverIdCreated,
                    ], ['id' => $_GET['id']])
                    ->execute()
                ) {
                    if ($status === STATUS_TRIP_OPEN) {
                        $this->driverService->queueOpenTripJobs($model);
                    }
                }

                // Tạo zalo bid và gửi tin nhắn thông tin tài xế qua zalo cho KH (nếu có)
                if ($modelTripGroup->zalo_seller_id > 0 && $model->trip_group_id > 0) {
                    $this->bidService->createBidTripZalo($model, new Bid());
                    $this->sendMessageZnsService->sendMessageDriverZns([
                        'customer_phone' => $model->customer_phone,
                        'tracking_id' => $model->id,
                        'driver_name' => $modelTripGroup->driver_name,
                        'phone_number' => $modelTripGroup->driver_phone,
                        'license_plates' => $modelTripGroup->license_plates,
                        'vehicle_type' => isset(TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car]) ? TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car] : 'Không rõ',
                        'customer_name' => $model->customer_name,
                    ]);
                }

                // Gửi tin nhắn thông tin chuyến xe qua zalo cho KH
                $this->sendMessageZnsService->tripSendMessageZNS($model);

                // Lưu log
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_create', [
                        'id' => $model->id,
                        'sold' => $modelTripGroup->zalo_seller_id > 0 && $model->trip_group_id > 0 ? 'bán qua zalo' : '',
                        'phone' => $model->customer_phone,
                    ]),
                    'action' => 'create',
                ]);
            }
            $transaction->commit();
            // Tạo đường dẫn cho các nút hành động
            if (isset($_GET['Trip']) && is_array($_GET['Trip']) && count($_GET['Trip'])) {
                $trip = $_GET['Trip'];
                $trip['pickup_address'] = $_GET['Trip']['destination_address'];
                $trip['destination_address'] = $_GET['Trip']['pickup_address'];
                $trip['source_trip'] = SOURCE_TRIP_TYPE_CUSTOMER;
                unset($trip['id']);

                return $this->redirect('/trip/create?' . http_build_query($trip));
            } elseif (isset($_GET['Booking']) && is_array($_GET['Booking']) && count($_GET['Booking'])) {
                $booking = $_GET['Booking'];
                $booking['pickup_address'] = $_GET['Booking']['destination_address'];
                $booking['phone'] = $_GET['Booking']['customer_phone'];
                $booking['destination_address'] = $_GET['Booking']['pickup_address'];
                $booking['note'] = $_GET['Booking']['description'];
                $booking['source_trip'] = SOURCE_TRIP_TYPE_CUSTOMER;
                $booking['status'] = $_GET['status'];
                unset($booking['id']);

                return $this->redirect('/statistic/create?' . http_build_query($booking));
            } else {
                return $this->redirect(['index']);
            }
        } catch (ErrorException $e) {
            $transaction->rollBack();
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripController - actionConfirm() - ' . $e->getMessage());
        }
    }

    /**
     * Displays a single Trip model for viewing information or editing.
     *
     * @param int $id The ID of the Trip model.
     * @param string $flag The flag indicating the view/edit mode.
     * @return mixed The rendered view.
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $modelTripGroup = $this->tripGroupService->getTripGroup($model);

        return $this->render('view', [
            'model' => $model,
            'modelTripGroup' => $modelTripGroup,
            'flag' => 'accept',
        ]);
    }

    /**
     * Displays a single Trip model for viewing information.
     *
     * @param int $id The ID of the Trip model.
     * @return mixed The rendered view.
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionOnlyView($id)
    {
        $model = $this->findModel($id);
        $user = Admin::find()->where(['admin.id' => $model->userid_created])->one();
        $modelTripGroup = $this->tripGroupService->getTripGroup($model);
        $logs = $this->getUserActionLogs($id, $model->created_on, $model->pickup_time);

        return $this->render('view', [
            'model' => $model,
            'modelTripGroup' => $modelTripGroup,
            'user' => $user,
            'flag' => 'view',
            'logs' => $logs,
        ]);
    }

    private function getUserActionLogs($modelId, $createdTime, $pickupTime)
    {
        $logs = [];
        $logPath = Yii::getAlias('@app/log/user_logs');
        $createdTimeStr = date('Y-m-d', strtotime($createdTime));
        $pickupTimeStr = date('Y-m-d', strtotime($pickupTime . ' + 3 day'));
        for ($date = $createdTimeStr; $date <= $pickupTimeStr; $date = date('Y-m-d', strtotime($date . ' + 1 day'))) {
            $logFile = $logPath . '/' . $date . '.json';
            if (file_exists($logFile)) {
                $data = json_decode(file_get_contents($logFile), true);
                if (is_array($data)) {
                    foreach ($data as $logEntry) {
                        $message = isset($logEntry['message']) ? $logEntry['message'] : '';
                        if ($message !== '') {
                            if (strpos($message, $modelId) !== false) {
                                $logs[] = [
                                    'action' => $logEntry['action'],
                                    'created_on' => $logEntry['created_on'],
                                    'user_name' => $logEntry['user_name'],
                                    'message' => $message,
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $logs;
    }

    /**
     * Create a new trip.
     *
     * This action handles the creation of a new trip, which can be a standalone trip or associated with a booking.
     *
     * @return mixed The response, which can be a rendering of the trip creation form or a redirection to the trip view page.
     * @throws \Exception When encountering errors in the process.
     */
    public function actionCreate()
    {
        $model = new Trip();
        $modelTripGroup = new TripGroup();
        $modelRequestCallBack = new RequestCallBack();
        $modelBooking = [];
        $method = 'create';

        // Lấy thông tin lịch booking hiển thị ra giao diện
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $modelBooking = Booking::findOne($id)->toArray();
            $model->agency_id = (isset($modelBooking['agency_id']) && !empty($modelBooking['agency_id']) ? $modelBooking['agency_id'] : 0);
            $model->booking_id = $modelBooking['id'];
            $method = 'booking';

            if ($modelBooking['type'] == SOURCE_TRIP_TYPE_DRIVER && !empty($modelBooking['driver_id_created'])) {
                $modelBooking['driver_created'] = Driver::find()
                    ->where(['id' => $modelBooking['driver_id_created']])
                    ->asArray()
                    ->one();
            }
        }
        if (isset($_GET['idCallBack'])) {
            $modelRequestCallBack = RequestCallBack::findOne(['id' => $_GET['idCallBack']]);
        }
        // Lấy thông tin id từ yêu cầu gọi lại để cập nhật trạng thái sau khi xác nhận đơn hàng
        if (isset($_GET['idCallBack'])) {
            $model->call_back_id = (!empty($_GET['idCallBack']) ? $_GET['idCallBack'] : 0);
        }

        if ($model->load(Yii::$app->request->post()) && $modelTripGroup->load(Yii::$app->request->post())) {
            $validationResult = $this->tripService->validateTrip([
                'modelTripGroup' => $modelTripGroup,
                'model' => $model,
            ]);

            // Nếu xác thực không thành công, hiển thị lỗi ở màn tạo chuyến
            if ($validationResult === false) {
                return $this->render('create', compact('model', 'modelTripGroup', 'modelBooking', 'method'));
            } else {
                $tripDuplicate = $model->findTripDuplicate($model);
                Yii::info('Số chuyến trùng: ' . $tripDuplicate);
                if ($tripDuplicate > 0) {
                    Yii::info('Chuyến xe đã tồn tại trong hệ thống! Vui lòng kiểm tra lại.');
                    $model->addError('#', 'Chuyến xe đã tồn tại trong hệ thống! Vui lòng kiểm tra lại.');
                    return $this->render('create', compact('model', 'modelTripGroup', 'modelBooking', 'method'));
                }

                $transaction = Yii::$app->db->beginTransaction();

                try {
                    // Lưu thông tin bán qua zalo
                    if ($modelTripGroup->zalo_seller_id > 0) {
                        $modelTripGroup->price = str_replace('.', '', $modelTripGroup->price);
                        $modelTripGroup->save();
                    }

                    // Xử lý dữ liệu để lưu thông tin chuyến xe vào DB
                    $model = $this->tripService->storeDataTrip($model, $modelTripGroup, 'create');
                    if (!empty($model->voucher)) {
                        $voucher = $this->voucherService->searchByCodeAndNotUsed($model->voucher);
                        if ($voucher) {
                            if ($voucher->type == VOUCHER_VND_TYPE) {
                                $model->price_customer = (string) max($model->price_customer - $voucher->value, 0);
                            }
                        }
                    }
                    $model->save();
                    $transaction->commit();
                    // Chuyển hướng đến trang xác nhận chuyến đi
                    $params = ['view', 'id' => $model->id];
                    if (isset($_GET['id'])) {
                        $params['id_booking'] = $_GET['id'];
                    }
                    if (isset($_GET['idCallBack'])) {
                        $params['idCallBack'] = $_GET['idCallBack'];
                    }
                    if (isset($_GET['trip_id'])) {
                        $params['trip_id'] = $_GET['trip_id'];
                    }
                    if (isset($_GET['customer_service_id'])) {
                        $params['customer_service_id'] = $_GET['customer_service_id'];
                    }

                    return $this->redirect($params);
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelTripGroup' => $modelTripGroup,
            'modelBooking' => $modelBooking,
            'modelRequestCallBack' => $modelRequestCallBack,
            'method' => $method,
            'module' => 'trip',
        ]);
    }


    public function actionAddManual($id)
    {
        $bid = new Bid();
        if ($bid->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            $model = Trip::findOne($id);
            $driver = Driver::findOne($bid->driver_id);
            // $adminId = Yii::$app->user->id;
            // $admin = Admin::findOne($adminId);
            try {
                if ($model == null || $model->status == 'DONE') {
                    throw new \Exception('Lịch này không tồn tại');
                }

                $b = $this->bidService->getBidSuccessByTripId($model->id);

                if ($b instanceof Bid) {
                    throw new \Exception('Lịch này không tồn tại');
                }

                if (empty($bid->price) || $bid->price === 0) {
                    Yii::$app->session->setFlash('error', 'Chưa nhập giá!');

                    return $this->refresh();
                }

                if (empty($bid->driver_id)) {
                    Yii::$app->session->setFlash('error', 'Xin vui lòng chọn tài xế!');

                    return $this->refresh();
                }

                // Validate Zalo message settings
                if (isset($bid->send_zalo_message) && $bid->send_zalo_message != 1) {
                    if (empty(trim($bid->zalo_disable_reason))) {
                        Yii::$app->session->setFlash('error', 'Vui lòng nhập lý do không gửi tin nhắn Zalo cho khách hàng!');
                        return $this->refresh();
                    }
                }
                // if ($bid->price < 0 || $model->price_customer - $bid->price < 0) {
                //     Yii::$app->session->setFlash('error', 'Giá không hợp lệ!');

                //     return $this->refresh();
                // }

                // if($driver['money'] < 200000){
                //     $this->notificationService->sendNotificationByUsername($driver, $admin, null, 'Cảnh báo', 'Tài khoản của quý khách còn dưới 200k, xin vui lòng nạp thêm', '', null);
                // }

                // update bid
                $bid->price = MyStringHelper::convertStringToInteger($bid->price);
                $bid->money_before = $driver->money;
                $bid->money_after = $model->is_collect_money ? $driver->money - ($model->price_customer - $bid->price) : $driver->money; // Nếu trạng thái là thu tiền khách thì trừ tiền lái xe
                $bid->status = 'SUCCESS';
                $bid->trip_id = $id;
                $bid->price_customer = $model->price_customer;
                $bid->save();

                if ($model->is_collect_money) {
                    $collectedMoneyAt = date_format(date_create('now', new DateTimeZone('Asia/Ho_Chi_Minh')), 'Y-m-d H:i');
                    $command = Yii::$app->db->createCommand()
                        ->update('trip', ['status' => 'DONE', 'collected_money' => 1, 'collected_money_at' => $collectedMoneyAt], ['id' => $id])
                        ->execute();
                } else {
                    $command = Yii::$app->db->createCommand()->update('trip', ['status' => 'DONE'], ['id' => $id])->execute();
                }

                // Nếu trạng thái là thu tiền thì trừ tiền lái xe
                if ($model->is_collect_money) {
                    $command = Yii::$app->db->createCommand('UPDATE driver set money = money - :money WHERE id = :id');
                    $m = $model->price_customer - $bid->price;
                    $d = $bid->driver_id;
                    $command->bindParam('id', $d);
                    $command->bindParam('money', $m);
                    $command->execute();
                }

                // if ($driver->driver_ban == STATUS_DRIVER_BAN) {
                $car = Car::findOne($driver->car_id);

                $params = [
                    'customer_phone' => $model->customer_phone,
                    'tracking_id' => $model->id,
                    'driver_name' => $driver->display_name,
                    'phone_number' => $driver->username,
                    'license_plates' => $car->bks,
                    'vehicle_type' => isset(TYPE_OF_CAR_LIST[$car->type_of_car]) ? TYPE_OF_CAR_LIST[$car->type_of_car] : 'Không rõ',
                    'customer_name' => $model->customer_name,
                    'send_message' => isset($bid->send_zalo_message) && $bid->send_zalo_message == 1,
                    'reason' => isset($bid->zalo_disable_reason) && !empty(trim($bid->zalo_disable_reason)) ? $bid->zalo_disable_reason : null,
                ];

                $response = $this->sendMessageZnsService->sendMessageDriverZns($params);
                // }

                $transaction->commit();

                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_add_manual', [
                        'id' => $model->id,
                        'phone' => $model->customer_phone,
                    ]),
                    'action' => 'update',
                ]);
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }

            $this->redirect(['index']);
        }

        return $this->render('add-manual', [
            'model' => $bid,
            'ref' => Yii::$app->request->referrer,
        ]);
    }

    /**
     * Updates an existing Trip model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        try {
            // Lấy dữ liệu từ hệ thống
            $model = $this->findModel($id);
            $modelTripGroup = $this->tripGroupService->getTripGroup($model);
            $dataOld = [
                'agency_id' => $model->agency_id,
                'source_trip' => $model->source_trip,
                'price_customer' => $model->price_customer,
                'is_collect_money' => $model->is_collect_money,
                'customer_name' => $model->customer_name,
                'customer_phone' => $model->customer_phone,
            ];
            $params = Yii::$app->request->post();

            // Cập nhật dữ liệu từ form vào hệ thống
            if ($model->load($params) && $modelTripGroup->load($params)) {
                // Validate dữ liệu
                $validationResult = $this->tripService->validateTrip([
                    'modelTripGroup' => $modelTripGroup,
                    'model' => $model,
                ]);

                if ($validationResult === false) {
                    return $this->render('update', compact('model', 'modelTripGroup'));
                }

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $bid = $this->bidService->getBidNewestByTripId($model->id);

                    $isPriceChanged = $dataOld['price_customer'] != MyStringHelper::convertStringToInteger($model->price_customer);
                    $isCollectChanged = $dataOld['is_collect_money'] != $model->is_collect_money;

                    // Không cho phép update giá khi chuyến đã được điều (DONE hoặc COMPLETE)
                    if (($isPriceChanged || $isCollectChanged) && ($model->status == STATUS_TRIP_DONE || $model->status == STATUS_TRIP_COMPLETE)) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Không thể cập nhật giá hoặc hình thức thu tiền khi chuyến đã được điều vì nó ảnh hưởng đến số tiền mà lái xe đã mua chuyến! Hãy liên hệ với quản lý để sử dụng chức năng update giá.');
                        return $this->render('update', [
                            'model' => $model,
                            'modelTripGroup' => $modelTripGroup,
                            'module' => 'trip',
                        ]);
                    }

                    // Nếu bid qua zalo thì sẽ xử lý ở đây
                    $modelTripGroup = $this->tripService->processZaloSeller($modelTripGroup, $model);

                    // Chuẩn bị dữ liệu cập nhật chuyến xe
                    $model = $this->tripService->storeDataTrip($model, $modelTripGroup);

                    // Cập nhật tiền bid khi ghép chuyến zalo và từ zalo ghép về thường
                    if (isset($bid) && $bid->price != $model->price_bid && $model->trip_group_id > 0) {
                        $this->bidService->createBidTripZalo($model, $bid);
                    } elseif (!isset($bid) && $model->trip_group_id > 0) {
                        $this->bidService->createBidTripZalo($model, new Bid());
                    }

                    // Determine driver debt settlement and collection flags based on conditions
                    $model = $this->tripService->update_driver_debt($model, $modelTripGroup, $dataOld, 'update');
                    if (!$model->save()) {
                        throw new \Exception('Lỗi khi cập nhật chuyến xe: ' . implode(', ', $model->getFirstErrors()));
                    }

                    $transaction->commit();

                    // Lưu log
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_action', [
                            'id' => $model->id,
                            'action' => ACTION_LIST['update'],
                            'phone' => $model->customer_phone,
                        ]),
                        'action' => 'update',
                    ]);

                    $changed = (
                        $dataOld['customer_name'] !== $model->customer_name ||
                        $dataOld['customer_phone'] !== $model->customer_phone ||
                        (int) $dataOld['price_customer'] !== (int) str_replace('.', '', $model->price_customer)
                    );
                    if ($changed) {
                        $this->sendMessageZnsService->tripSendMessageZNS($model);
                    }

                    // Redirect về trang đã filter trước đó
                    $searchModel = new SearchTrip();
                    $searchModel->load(Yii::$app->request->queryParams);
                    $returnUrl = Yii::$app->request->post('returnUrl');
                    if (!empty($returnUrl)) {
                        return $this->redirect($returnUrl);
                    } else {
                        return $this->redirect(['index', 'SearchTrip' => $searchModel]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }

            return $this->render('update', [
                'model' => $model,
                'modelTripGroup' => $modelTripGroup,
                'module' => 'trip',
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripController - actionUpdate() - ' . $e->getMessage());
        }
    }

    /**
     * Update pickup time, customer price and bid price after trip has a successful bid.
     * Recalculate driver balance when collecting money and notify driver about the change.
     */
    public function actionUpdateBidPrice()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $request = Yii::$app->request;

        $tripId = (int) $request->post('trip_id');
        $pickupTimeInput = $request->post('pickup_time');
        $priceCustomerInput = $request->post('price_customer');
        $priceBidInput = $request->post('price_bid');

        $trip = Trip::findOne($tripId);
        if (!$trip instanceof Trip) {
            return ['success' => false, 'message' => 'Không tìm thấy chuyến xe'];
        }

        $bid = Bid::find()->where(['trip_id' => $tripId, 'status' => STATUS_BID_SUCCESS])->orderBy(['id' => SORT_DESC])->one();
        if (!$bid instanceof Bid) {
            return ['success' => false, 'message' => 'Chuyến chưa có bid thành công'];
        }

        $priceCustomer = MyStringHelper::convertStringToInteger($priceCustomerInput);
        $priceBid = MyStringHelper::convertStringToInteger($priceBidInput);
        $pickupTimestamp = strtotime($pickupTimeInput);

        $validationResult = $this->tripService->validateTrip([
            'model' => $trip
        ]);
        if ($validationResult === false) {
            return ['success' => false, 'message' => 'Dữ liệu không hợp lệ'];
        }
        $driver = Driver::findOne($bid->driver_id);
        if (!$driver instanceof Driver) {
            return ['success' => false, 'message' => 'Không tìm thấy tài xế'];
        }

        if ((int) $trip->price_customer === $priceCustomer && (int) $trip->price_bid === $priceBid && strtotime($trip->pickup_time) === $pickupTimestamp) {
            return ['success' => false, 'message' => 'Dữ liệu không thay đổi'];
        }

        // Delegate business logic to TripService
        $admin = Yii::$app->user->identity instanceof Admin ? Yii::$app->user->identity : null;

        return $this->tripService->updateBidPrice($trip, $bid, $priceCustomer, $priceBid, $pickupTimestamp, $admin);
    }

    /**
     * Updates display column in Trip model.
     * @param int $id
     * @return mixed
     */
    public function actionUpdateDisplay()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        if ($model != null) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $display = $model->display == 0 ? 1 : 0;
                $command = Yii::$app->db->createCommand()
                    ->update('trip', ['display' => $display], ['id' => $id])
                    ->execute();

                $transaction->commit();
                $display_money = $model->display == 1 ? 'Ẩn' : 'Hiện';
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_update_display_money', [
                        'id' => $model->id,
                        'display_money' => $display_money,
                        'phone' => $model->customer_phone,
                    ]),
                    'action' => 'update',
                ]);

                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                // Handle the exception, log or display an error message
            }
        }

        return false;
    }
    /**
     * Updates collected_money column in Trip model.
     * @param int $id
     * @return mixed
     */
    public function actionUpdateCollectedMoney()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        if ($model != null) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $collectedMoney = $model->collected_money == 0 ? 1 : 0;
                $collectedMoneyAt = $model->collected_money == 0 ? date_format(date_create('now', new DateTimeZone('Asia/Ho_Chi_Minh')), 'Y-m-d H:i') : null;
                $command = Yii::$app->db->createCommand()
                    ->update('trip', ['collected_money' => $collectedMoney, 'collected_money_at' => $collectedMoneyAt], ['id' => $id])
                    ->execute();

                $transaction->commit();
                $display_money = $model->collected_money == 0 ? 'Đã thu' : 'Chưa thu';
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_update_display_money', [
                        'id' => $model->id,
                        'display_money' => $display_money,
                        'phone' => $model->customer_phone,
                    ]),
                    'action' => 'update',
                ]);

                return json_encode([
                    'status' => true,
                    'collectedMoneyAt' => $collectedMoneyAt,
                ]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                // Handle the exception, log or display an error message
            }
        }

        return false;
    }





    public function actionTest($id)
    {
        $searchModel = new SearchTrip();
        $searchModel->load(Yii::$app->request->queryParams);

        return $this->redirect(['index', 'SearchTrip' => $searchModel]);
    }

    /**
     * Deletes an existing Trip model and related records.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return bool|string True on successful deletion, an error message if deletion fails, or false if the trip data is not found.
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $params = Yii::$app->request->post();
        $id = $params['id'];
        $note = $params['note'];
        $model = $this->findModel($id);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Create a new TripDelete instance and fetch trip data
            $tripDelete = new TripDelete();
            $searchTripById = $tripDelete->searchTripById($id);

            // If trip data is found, save the deletion log
            if ($searchTripById) {
                // Delete related bid records if they exist
                if (isset($searchTripById['bidAll']) && is_array($searchTripById['bidAll'])) {
                    $list_id = [];
                    foreach ($searchTripById['bidAll'] as $bid_item) {
                        if (isset($bid_item['id'])) {
                            $list_id[] = $bid_item['id'];
                        }
                    }
                    Bid::deleteAll(['id' => $list_id]);
                }

                // Delete related trip return records if they exist
                if (isset($searchTripById['tripReturnAll']) && $searchTripById['tripReturnAll'] != '') {
                    $list_id = [];
                    foreach ($searchTripById['tripReturnAll'] as $trip_return) {
                        if (isset($trip_return['id'])) {
                            $list_id[] = $trip_return['id'];
                        }
                    }
                    TripReturn::deleteAll(['id' => $list_id]);
                }

                // Delete related trip group record if it exists
                if (isset($searchTripById['tripGroup']) && $searchTripById['tripGroup'] != '') {
                    $tripGroup_id = $searchTripById['tripGroup']['id'] ?? '';
                    $tripGroup = TripGroup::findOne($tripGroup_id);
                    $tripGroup->delete();
                }

                // Save the deletion log
                $tripDelete->user_id = Yii::$app->user->id;
                $tripDelete->note = $note;
                $tripDelete->data_trip = json_encode($searchTripById);
                $tripDelete->save();

                $model->delete();

                $transaction->commit();

                // Log the user's action
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_action', [
                        'id' => $model->id,
                        'phone' => $model->customer_phone,
                        'action' => ACTION_LIST['delete'],
                    ]),
                    'action' => 'delete',
                ]);

                return true;
            }
            // Return false if trip data is not found
            return false;
        } catch (\Exception $e) {
            $transaction->rollBack();

            return $e;
        }
    }

    /**
     * Copy an existing Trip model.
     * If copy is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionCopy($id)
    {
        $originalTrip = $this->findModel($id);
        $model = new Trip();
        $modelTripGroup = new TripGroup();

        // Copy data from original trip
        $model->attributes = $originalTrip->attributes;
        $model->id = null; // Reset ID for new trip
        $model->status = 'OPEN'; // Reset status to OPEN
        $model->created_on = null; // Reset created time
        $model->modified_on = null; // Reset modified time
        $model->userid_created = null; // Reset user created
        $model->userid_updated = null; // Reset user updated

        // Copy trip group data if exists
        if ($originalTrip->tripGroup) {
            $modelTripGroup->attributes = $originalTrip->tripGroup->attributes;
            $modelTripGroup->id = null; // Reset ID for new trip group
            $modelTripGroup->created_on = null;
            $modelTripGroup->modified_on = null;
        }

        $method = 'copy';
        $modelBooking = [];
        $modelRequestCallBack = new RequestCallBack();

        if ($model->load(Yii::$app->request->post()) && $modelTripGroup->load(Yii::$app->request->post())) {
            $validationResult = $this->tripService->validateTrip([
                'modelTripGroup' => $modelTripGroup,
                'model' => $model,
            ]);

            // Nếu xác thực không thành công, hiển thị lỗi ở màn tạo chuyến
            if ($validationResult === false) {
                return $this->render('create', compact('model', 'modelTripGroup', 'modelBooking', 'method'));
            } else {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    // Lưu thông tin bán qua zalo
                    if ($modelTripGroup->zalo_seller_id > 0) {
                        $modelTripGroup->price = str_replace('.', '', $modelTripGroup->price);
                        $modelTripGroup->save();
                    }

                    // Xử lý dữ liệu để lưu thông tin chuyến xe vào DB
                    $model = $this->tripService->storeDataTrip($model, $modelTripGroup, 'create');
                    if (!empty($model->voucher)) {
                        $voucher = $this->voucherService->searchByCodeAndNotUsed($model->voucher);
                        if ($voucher) {
                            if ($voucher->type == VOUCHER_VND_TYPE) {
                                $model->price_customer = (string) max($model->price_customer - $voucher->value, 0);
                            }
                        }
                    }
                    $model->save();
                    $transaction->commit();

                    Yii::$app->session->setFlash('success', 'Copy chuyến xe thành công!');

                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_copy', [
                            'id' => $model->id,
                            'phone' => $model->customer_phone,
                        ]),
                        'action' => 'copy',
                    ]);
                    // Chuyển hướng đến trang xem chuyến đi mới
                    return $this->redirect(['view', 'id' => $model->id]);
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelTripGroup' => $modelTripGroup,
            'modelBooking' => $modelBooking,
            'modelRequestCallBack' => $modelRequestCallBack,
            'method' => $method,
            'module' => 'trip',
        ]);
    }

    /**
     * Finds the Trip model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Trip the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Trip::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * Updates trip group and trip.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @throws NotFoundHttpException if the trip cannot be found
     * @throws Exception
     */
    public function actionTransfer($id)
    {
        $modelBid = (new Bid())->findOne(['trip_id' => $id, 'status' => STATUS_BID_SUCCESS]);
        if ($modelBid instanceof Bid) {
            if ($modelBid->driver_id != 0) {
                Yii::$app->session->setFlash('error', 'Đã có có lái xe nhận lịch!');

                return $this->refresh();
            }
        }

        $modelTripGroup = new TripGroup();
        $model = Trip::findOne($id);
        if ($modelTripGroup->load(Yii::$app->request->post())) {
            if (isset($modelTripGroup->group_zalo_id) && isset($modelTripGroup->zalo_seller_id) && $modelTripGroup->group_zalo_id > 0 && $modelTripGroup->zalo_seller_id > 0) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    $modelTripGroup->price = str_replace('.', '', $modelTripGroup->price);

                    if ($modelTripGroup->id > 0) {
                        $tripGroup = TripGroup::findOne($modelTripGroup->id);
                        $tripGroup->load(Yii::$app->request->post());
                        $tripGroup->save();
                    } else {
                        $modelTripGroup->save();
                    }

                    $transaction->commit();
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_transfer', [
                            'id' => $model->id,
                            'phone' => $model->customer_phone,
                        ]),
                        'action' => 'update',
                    ]);
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                }

                $this->sendMessageZnsService->sendMessageDriverZns([
                    'customer_phone' => $model->customer_phone,
                    'tracking_id' => $model->id,
                    'driver_name' => $modelTripGroup->driver_name,
                    'phone_number' => $modelTripGroup->driver_phone,
                    'license_plates' => $modelTripGroup->license_plates,
                    'vehicle_type' => isset(TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car]) ? TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car] : 'Không rõ',
                    'customer_name' => $model->customer_name,
                ]);

                $status = ($modelTripGroup->group_zalo_id == '') ? STATUS_TRIP_OPEN : STATUS_TRIP_COMPLETE;
                $price = ($modelTripGroup->group_zalo_id > 0 ? $this->tripService->calculatorPrice($model, $modelTripGroup) : $model->price_bid);
                if ($model->status != STATUS_TRIP_DONE && $model->status != STATUS_TRIP_COMPLETE) {
                    $model = $this->tripService->update_driver_debt($model, $modelTripGroup, [], 'create');
                }
                Yii::$app->db->createCommand()
                    ->update('trip', [
                        'trip_group_id' => $modelTripGroup->id,
                        'status' => $status,
                        'collected_money' => $model->collected_money,
                        'driver_debt' => $model->driver_debt,
                        'price_bid' => $price,
                    ], ['id' => $id])
                    ->execute();

                $model->price_bid = $price;
                $bid = Bid::findOne(['status' => 'SUCCESS', 'trip_id' => $id]);
                $this->bidService->createBidTripZalo($model, $bid instanceof Bid ? $bid : new Bid());
            }
        }

        return $this->redirect(['index']);
    }

    /**
     * Action to change driver sub status.
     */
    public function actionChangeStatusDriverSub()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $postData = $request->post();
            $modelTrip = Trip::findOne($postData['trip_id']);
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ($modelTrip !== null) {
                    $modelTrip->driver_sub = $postData['driver_sub'];
                    $modelTrip->save();
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }
    }

    /**
     * Action to send ZNS message for trip to customer.
     * @return mixed
     */
    public function actionSendMessageDriverZns()
    {
        $response = [];
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            $trip = $this->findModel($postData['trip_id']);
            $bid = Bid::findOne(['status' => 'SUCCESS', 'trip_id' => $postData['trip_id']]);

            if ($postData['driver_sub'] == 1) {
                $driverSub = DriverSub::findOne(['trip_id' => $postData['trip_id'], 'driver_id' => $bid->driver_id]);
                $response = $this->sendMessageZnsService->sendMessageDriverZns([
                    'customer_phone' => $trip->customer_phone,
                    'tracking_id' => $trip->id,
                    'driver_name' => $driverSub->name,
                    'phone_number' => $driverSub->phone,
                    'license_plates' => $driverSub->bks,
                    'vehicle_type' => $driverSub->type,
                    'customer_name' => $trip->customer_name,
                ]);
            } else {
                $driver = Driver::findOne($bid->driver_id);
                $car = Car::findOne($driver->car_id);
                $response = $this->sendMessageZnsService->sendMessageDriverZns([
                    'customer_phone' => $trip->customer_phone,
                    'tracking_id' => $trip->id,
                    'driver_name' => $driver->display_name,
                    'phone_number' => $driver->username,
                    'license_plates' => $car->bks,
                    'vehicle_type' => $car->type,
                    'customer_name' => $trip->customer_name,
                ]);
            }
        }

        return json_encode($response);
    }

    /**
     * Get zalo by id zalo seller
     * @param $model
     * @return $customer
     */
    public function actionGetZalo()
    {
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            $zaloSeller = \app\models\GroupZaloSeller::findOne($postData['id']);
            $zaloCatalogueId = json_decode($zaloSeller->group_zalo_catalogue_id, true);
            $zaloCatalogueQuery = \app\models\GroupZaloCatalogue::find()->where(['status' => '1']);
            $zaloQuery = \app\models\GroupZalo::find()->andWhere(['status' => 1]);
            if (isset($zaloCatalogueId) && is_array($zaloCatalogueId) && count($zaloCatalogueId)) {
                $zaloCatalogueQuery->andWhere(['in', 'id', $zaloCatalogueId]);
                $zaloQuery->andWhere(['in', 'group_zalo_catalogue', $zaloCatalogueId]);
            }
            $zaloCatalogue = $zaloCatalogueQuery->orderBy('name')->all();
            $zalo = $zaloQuery->all();
            $options = [];
            foreach ($zaloCatalogue as $category) {
                $groupOptions = \yii\helpers\ArrayHelper::map(
                    array_filter($zalo, function ($item) use ($category) {
                        return $item->group_zalo_catalogue == $category->id;
                    }),
                    'id',
                    function ($item) {
                        return $item->name;
                    }
                );
                if (!empty($groupOptions)) {
                    $options[$category->name] = $groupOptions;
                }
            }

            return json_encode($options);
        }
    }

    public function actionGetPriceBid()
    {
        $params = Yii::$app->request->post();
        $dateTime = new DateTime($params['pickUpTime']);
        $time = $dateTime->format('H:i:s');
        $data = ConfigAutoSale::find()
            ->where(['type_of_car' => $params['type_of_car']])
            ->andWhere(['schedule' => $params['schedule']])
            ->andWhere([
                'or',
                ['and', ['<=', 'from_time', $time], ['>=', 'to_time', $time]],
                ['and', ['<=', 'from_time', $time], ['IS', 'to_time', new \yii\db\Expression('NULL')]],
                ['and', ['IS', 'from_time', new \yii\db\Expression('NULL')], ['>=', 'to_time', $time]],
            ])
            ->one();
        if (isset($data) && $data !== null) {
            return json_encode($data->price);
        } else {
            return json_encode(['error' => 'Không tìm thấy giá phù hợp']);
        }
    }

    public function actionResendMessage()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $tripId = Yii::$app->request->post('trip_id');
        $templateId = Yii::$app->request->post('template_id');
        $type = Yii::$app->request->post('type');

        if (!$tripId || !$templateId) {
            return ['success' => false, 'message' => 'Thiếu dữ liệu'];
        }
        // ===== TEMPLATE 1 ===== //
        if ($type === 'zalo_template_1') {

            $trip = Trip::findOne($tripId);
            if (!$trip) {
                return ['success' => false, 'message' => 'Không tìm thấy chuyến'];
            }
            try {
                $this->sendMessageZnsService->tripSendMessageZNS($trip);
                return ['success' => true];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        // ===== TEMPLATE 2 ===== //
        if ($type === 'zalo_template_2') {

            $trip = Trip::findOne($tripId);
            if (!$trip) {
                return ['success' => false, 'message' => 'Không tìm thấy chuyến'];
            }
            // get bid SUCCESS
            $bid = Bid::find()
                ->where(['trip_id' => $tripId, 'status' => 'SUCCESS'])
                ->one();

            if (!$bid) {
                return ['success' => false, 'message' => 'Chuyến chưa có tài xế nhận chuyến'];
            }

            $driver = Driver::findOne($bid->driver_id);
            $car = Car::findOne($driver->car_id);

            if (!$driver || !$car) {
                return ['success' => false, 'message' => 'Không tìm thấy tài xế hoặc xe'];
            }
            $params = [
                'customer_phone' => $trip->customer_phone,
                'tracking_id' => $trip->id,
                'driver_name' => $driver->display_name,
                'phone_number' => $driver->username,
                'license_plates' => $car->bks,
                'vehicle_type' => isset(TYPE_OF_CAR_LIST[$car->type_of_car]) ? TYPE_OF_CAR_LIST[$car->type_of_car] : 'Không rõ',
                'customer_name' => $trip->customer_name,
                'send_message' => true,
                'reason' => null,
            ];
            try {
                $this->sendMessageZnsService->sendMessageDriverZns($params);
                return ['success' => true];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return ['success' => false, 'message' => 'Template không hợp lệ'];
    }
}