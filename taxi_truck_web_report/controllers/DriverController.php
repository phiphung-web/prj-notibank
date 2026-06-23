<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\models\Car;
use app\models\Driver;
use app\models\DriverRole;
use app\models\DriverSub;
use app\models\LogRequest;
use app\models\Role;
use app\models\SearchDriver;
use app\models\SearchPayTransaction;
use app\services\CarService;
use app\services\DriverHistoryService;
use app\services\DriverService;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\widgets\DetailView;

/**
 * DriverController implements the CRUD actions for Driver model.
 */
class DriverController extends BaseController
{
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

    protected $carService;
    protected $driverService;
    protected $driverHistoryService;

    public function init()
    {
        parent::init();
        $this->driverService = new DriverService();
        $this->driverHistoryService = new DriverHistoryService();
        $this->carService = new CarService();
    }

    /**
     * Lists all Driver models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new SearchDriver();
        $dataProvider = $model->search(Yii::$app->request->queryParams);
        $reason_reject_array = $this->driverService->getReasonReject();

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'reason_reject_array' => $reason_reject_array,
            'statistic' => $this->driverService->getStatisticDriver(),
        ]);
    }

    public function actionDriverManyTrips()
    {
        $reason_reject_array = $this->driverService->getReasonReject();

        return $this->render('index-driver-many-trips', [
            'reason_reject_array' => $reason_reject_array,
            'dataProvider' => $this->driverService->getDriverManyTrips(Yii::$app->request->queryParams),
        ]);
    }

    public function actionDriverNoTrips()
    {
        $reason_reject_array = $this->driverService->getReasonReject();

        return $this->render('index-driver-no-trips', [
            'reason_reject_array' => $reason_reject_array,
            'dataProvider' => $this->driverService->getDriverNoTrips(Yii::$app->request->queryParams),
        ]);
    }

    public function actionDriverNotActive()
    {
        $reason_reject_array = $this->driverService->getReasonReject();

        return $this->render('index-driver-not-active', [
            'reason_reject_array' => $reason_reject_array,
            'dataProvider' => $this->driverService->getDriverNotActive(Yii::$app->request->queryParams),
        ]);
    }

    /**
     * Renders the index page with the registered drivers.
     *
     * @return mixed
     */
    public function actionRegister()
    {
        $model = new SearchDriver();
        $request = Yii::$app->request->queryParams;
        $dataProvider = $model->searchRegister($request);
        $request['register'] = true;

        return $this->render('register', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Renders the index page with the registered drivers sub.
     *
     * @return mixed
     */
    public function actionRegisterDriverSub()
    {
        $model = new SearchDriver();
        $request = Yii::$app->request->queryParams;
        $dataProvider = $model->searchDriverSub($request);

        return $this->render('driver_sub', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateStatus($id)
    {
        try {
            $request = Yii::$app->request;
            $model = $this->driverService->findModel($id);
            $transaction = Yii::$app->db->beginTransaction();
            $model->modified_on = date('Y-m-d H:i:s');
            $model->reason = $request->post('reason');
            $model->status = 2;
            $model->enabled = false;

            try {
                $model->save();
                $transaction->commit();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'driver_update_status', [
                        'id' => $model->id,
                        'name' => $model->display_name,
                        'phone' => $model->username,
                    ]),
                    'action' => 'update',
                ]);
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            }
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }
    }

    /**
     * Renders the index page with the registered drivers.
     *
     * @return mixed
     */
    public function actionAccept($id)
    {
        $model = $this->driverService->findModel($id);
        $carModel = Car::findOne($model->car_id);
        if (Yii::$app->request->getMethod() == 'POST') {
            $role = DriverRole::find()->where(['driver_id' => $id])->one();
            if (! $role) {
                $model->link('roles', Role::findOne(1));
            }
            $model->register = false;
            $model->status = 1;
            $model->admin_id_accepted = Yii::$app->user->id;
            if ($model->accepted_on == null) {
                $model->accepted_on = date('Y-m-d H:i:s');
            }
            $model->enabled = true;
            $model->driver_ban = STATUS_DRIVER_NORMAL;
            $carModel->type = '';
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->save();
                $carModel->save();
                $transaction->commit();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'driver_cuda', [
                        'id' => $model->id,
                        'name' => $model->display_name,
                        'phone' => $model->username,
                        'action' => ACTION_LIST['accept'],
                    ]),
                    'action' => 'accept',
                ]);

                return $this->redirect(Yii::$app->request->referrer);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', $e->errorInfo[2] ? $e->errorInfo[2] : 'Có lỗi xảy ra.');
            }
        }
    }

    public function actionAcceptDriverSub($id, $status)
    {
        try {
            $logRequest = LogRequest::find()->where(['driver_id' => $id])->orderBy(['created_on' => SORT_DESC])->one();
            $driver = $this->driverService->findModel($id);
            $driver->driver_ban = $status;
            $driver->save();

            $logRequest->accepted_on = date('Y-m-d H:i:s');
            $logRequest->save();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'driver_action_driver_sub', [
                    'id' => $driver->id,
                    'name' => $driver->display_name,
                    'action' => ($status == STATUS_DRIVER_BAN ? 'Chấp nhận' : 'Hủy bỏ'),
                ]),
                'action' => 'accept',
            ]);

            return $this->redirect(Yii::$app->request->referrer);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('DriverController - actionCreate() - ' . $e->getMessage());
        }
    }

    /**
     * Displays a single Driver model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchModel = new SearchPayTransaction();
        $payHistory = $searchModel->searchDriver($id);

        return $this->render('view', [
            'model' => $this->driverService->findModel($id),
            'payHistory' => $payHistory,
        ]);
    }


    public function actionTop()
    {
        $model = new Driver();
        $data = $model->getTop();
        $provider = new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]);

        return $this->render('top', [
            'dataProvider' => $provider,
        ]);
    }

    /**
     * Thêm mới tài xế
     * @return mixed
     */
    public function actionCreate()
    {
        try {
            // Thu thập dữ liệu
            $model = new Driver();
            $carModel = new Car();
            if ($model->load(Yii::$app->request->post()) && $carModel->load(Yii::$app->request->post())) {

                // Validate dữ liệu đầu vào
                $validationResult = $this->driverService->validateDriver(['carModel' => $carModel]);
                if ($validationResult === false) {
                    return $this->render('create', compact('model', 'carModel'));
                } else {
                    // Thêm mới thông tin tài xế
                    $transaction = Yii::$app->db->beginTransaction();

                    try {
                        $car = $this->carService->insertCar($carModel);
                        $driver = $this->driverService->insertDriver($model, $car);
                        $transaction->commit();

                        // Lưu log thêm mới tài xế
                        Yii::$app->userLogger->logUserAction([
                            'created_on' => date('Y-m-d H:i:s'),
                            'user_id' => Yii::$app->user->id,
                            'user_name' => Yii::$app->user->identity->username,
                            'message' => Yii::t('app', 'driver_cuda', [
                                'id' => $model->id,
                                'name' => $model->display_name,
                                'phone' => $model->username,
                                'action' => ACTION_LIST['create'],
                            ]),
                            'action' => 'create',
                        ]);

                        return $this->redirect(['view', 'id' => $model->id]);
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', $e->errorInfo[2] ? $e->errorInfo[2] : 'Có lỗi xảy ra.');
                    }
                }
            }

            return $this->render('create', [
                'model' => $model,
                'carModel' => $carModel,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('DriverController - actionCreate() - ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin lái xe
     *
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        try {
            // Thu thập dữ liệu
            $model = $this->driverService->findModel($id);
            $carModel = Car::findOne($model->car_id);
            $pass_old = $model->password;
            if ($model->load(Yii::$app->request->post()) && $carModel->load(Yii::$app->request->post())) {
                // Validate dữ liệu đầu vào
                $validationResult = $this->driverService->validateDriver(['carModel' => $carModel]);
                if ($validationResult === false) {
                    return $this->render('update', compact('model', 'carModel'));
                } else {
                    $transaction = Yii::$app->db->beginTransaction();

                    // Tổng hợp dữ liệu cập nhật tài xế
                    $model = $this->driverService->storeUpdateDriver($model, $pass_old);

                    // Cập nhật thông tin tài xế
                    try {
                        $model->save();
                        $carModel->save();
                        $transaction->commit();
                        // Lưu log cập nhật tài xế
                        Yii::$app->userLogger->logUserAction([
                            'created_on' => date('Y-m-d H:i:s'),
                            'user_id' => Yii::$app->user->id,
                            'user_name' => Yii::$app->user->identity->username,
                            'message' => Yii::t('app', 'driver_cuda', [
                                'id' => $model->id,
                                'name' => $model->display_name,
                                'phone' => $model->username,
                                'action' => ACTION_LIST['update'],
                            ]),
                            'action' => 'update',
                        ]);

                        return $this->redirect(['view', 'id' => $model->id]);
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', $e->errorInfo[2] ? $e->errorInfo[2] : 'Có lỗi xảy ra.');
                    }
                }
            }

            return $this->render('update', [
                'model' => $model,
                'carModel' => $carModel,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('DriverController - actionUpdate() - ' . $e->getMessage());
        }
    }

    /**
     * Deletes an existing Driver model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model = $this->driverService->findModel($id);
            $role = DriverRole::find()->where(['driver_id' => $id])->one();
            $car = Car::findOne($model->car_id);
            if ($role) {
                $role->delete();
            }
            $model->delete();
            $car->delete();
            $transaction->commit();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'driver_cuda', [
                    'id' => $model->id,
                    'name' => $model->display_name,
                    'phone' => $model->username,
                    'action' => ACTION_LIST['delete'],
                ]),
                'action' => 'delete',
            ]);
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        $referrer = Yii::$app->request->referrer;
        if (strpos($referrer, 'driver/view') !== false) {
            return $this->redirect(['/driver']);
        } else {
            return $this->redirect($referrer);
        }
    }

    public function actionCreateFolder($folderName, $folderChild)
    {
        $basePath = Yii::getAlias('@webroot');
        $folderPath = $basePath . '/upload/images/' . $folderName;
        $folderChildPath = $basePath . '/upload/images/' . $folderName . '/' . $folderChild;
        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        if (! file_exists($folderChildPath)) {
            mkdir($folderChildPath, 0777, true);
        }
        echo json_encode(['success' => true]);
        Yii::$app->end();
    }



    /**
     * Change trip to driver sub if driver has alot of cars
     * @return mixed
     */
    public function actionChangeDriverSub()
    {
        $response = '';
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $postData = $request->post();
            if (isset($postData['id']) && ! empty($postData['id'])) {
                $model = DriverSub::findOne($postData['id']);
            } else {
                $model = new DriverSub();
            }
            $model->name = $postData['name'];
            $model->phone = $postData['phone'];
            $model->bks = $postData['bks'];
            $model->type = $postData['type'];
            $model->trip_id = $postData['trip_id'];
            $model->driver_id = $postData['driver_id'];

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->save();
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
            $reponse = DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'label' => 'Lái xe phụ',
                        'value' => '<button class="btn btn-warning btn-edit-driver-sub" style="float:right">Chỉnh sửa</button>',
                        'captionOptions' => ['class' => 'table-caption'],
                        'format' => 'raw',
                    ],
                    'name',
                    'phone',
                    'bks',
                    'type',
                ],
            ]);
        }

        return json_encode([
            'html' => $reponse,
            'driver_id' => $model->id,
        ]);
    }

    public function actionGetDriverLocation()
    {
        $model = new SearchDriver();
        $params = Yii::$app->request->queryParams;
        $params['SearchDriver']['enabled'] = 1;
        $params['SearchDriver']['sort'] = 'modified_on';
        $dataProvider = $model->search($params);
        $reason_reject_array = $this->driverService->getReasonReject();

        return $this->render('location', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'reason_reject_array' => $reason_reject_array,
            'statistic' => $this->driverService->getStatisticDriver(),
        ]);
    }

    public function actionGetDriverVip()
    {
        $model = new Driver();
        $data = $model->getDriverVip();
        if (isset($data) && is_array($data) && count($data)) {
            $id = '';
            foreach ($data as $key => $value) {
                $id .= $value['id'];
                if ($key + 1 != count($data)) {
                    $id .= ',';
                }
            }
            $command = Yii::$app->db->createCommand("update driver set driver_rank = 'VIP' where id in (" . $id . ')');
            $command->execute();
        }

        return json_encode($data);
    }

    public function actionUpdateDriverStopWorking()
    {
        try {
            $transaction = Yii::$app->db->beginTransaction();
            $query = new Query();
            $notWorkingDrivers = $query->select(['driver.id', 'display_name'])
                ->from('driver')
                ->leftJoin('bid', 'bid.driver_id = driver.id AND bid.modified_on > DATE_SUB(NOW(), INTERVAL 6 MONTH)')
                ->where(['bid.id' => null])
                ->andWhere(['<', 'driver.created_on', new \yii\db\Expression('DATE_SUB(NOW(), INTERVAL 6 MONTH)')])
                ->groupBy('driver.id')
                ->all();
            pre($notWorkingDrivers);
            $notWorkingDriverIds = array_column($notWorkingDrivers, 'id');
            Driver::updateAll(['status' => 2, 'enabled' => 0], ['id' => $notWorkingDriverIds]);
            $transaction->commit();
            $response = [
                'success' => true,
                'message' => 'Đã cập nhật trạng thái tài xế thành công!',
            ];

            return json_encode($response);
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    public function actionHistoryDriver()
    {
        return $this->render('history', [
            'dataProvider' => $this->driverHistoryService->getDriverTransactionHistory(Yii::$app->request->queryParams),
        ]);
    }
}
