<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\helpers\MyStringHelper;
use app\helpers\ResponseHelper;
use app\models\Agency;
use app\models\debt\PassBooking;
use app\models\debt\TripAgency;
use app\models\SearchTripDriver;
use app\models\Trip;
use app\services\BookingService;
use app\services\DebtService;
use app\services\DriverService;
use app\services\PayService;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * TripDriverController
 */
class TripDriverController extends BaseController
{
    protected $debtService;
    protected $tripService;
    protected $driverService;
    protected $payService;
    protected $bookingService;

    public function __construct($id, $module, $config = [])
    {
        $this->tripService = Yii::$app->tripService;
        parent::__construct($id, $module, $config);
    }

    public function init()
    {
        parent::init();
        $this->debtService = new DebtService();
        $this->driverService = new DriverService();
        $this->payService = new PayService();
        $this->bookingService = new BookingService();
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
     * .
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('/dash-board/index');
    }

    public function actionPassTrip()
    {
        try {
            $searchModel = new PassBooking();
            $params = Yii::$app->request->queryParams;
            $dataProvider = $searchModel->searchPassBooking($params);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionPassTrip() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        return $this->render('pass-trip/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdatePassTrip()
    {
        try {
            $params = Yii::$app->request->post();
            $booking = $this->bookingService->findOne($params['id']);
            $driver = $this->driverService->findModel($booking->driver_id_created);
            if ($booking != null || (! $params['payment_method'] && $driver != null)) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    // Cập nhật thời gian đã trả tiền cho lái xe
                    $this->bookingService->updatePaidDriverOn($booking->id);

                    // Nếu phương thức thanh toán là nạp bid, cập nhật số tiền cho tài xế
                    if (! $params['payment_method']) {
                        $this->driverService->updateDriverMoney($driver->id, $driver->money, (int)$params['price']);

                        // Nếu số tiền nạp bid > 0 thì tạo transaction
                        if ((int)$params['price'] > 0) {
                            $this->payService->createTransaction([
                            'price' => (int)$params['price'],
                            'driver_id' => $driver->id,
                        ]);
                        }
                    }
                    $transaction->commit();

                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_update_pass_booking', [
                            'id' => $booking->id,
                            'driver' => $driver->display_name,
                            'price' => MyStringHelper::convertIntegerToPrice((int)$params['price']),
                            'total_price' => MyStringHelper::convertIntegerToPrice($driver->money + (int)$params['price']),
                        ]),
                        'action' => 'update',
                    ]);

                    return ResponseHelper::renderResponse(200, 'Thanh toán bán lịch thành công!', []);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }

            return ResponseHelper::renderResponse(400, 'Có lỗi xảy ra!', []);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionUpdatePassTrip() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    /**
     * Displays a list of trips for driver debt collection.
     * Filters trips where driver debt collection is not yet resolved.
     *
     * @return string The rendered view.
     */
    public function actionDriverDebtCollection()
    {
        try {
            $searchModel = new SearchTripDriver();
            $params = Yii::$app->request->queryParams;
            $searchModel->filter_time = 'pickup_time DESC';
            $searchModel->driver_debt = 'driver_debt_collection';
            $dataProvider = $searchModel->searchDriverDebt($params, DEBT_DRIVER);
            $money = $searchModel->totalMoney(DEBT_DRIVER);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionDriverDebtCollection() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        return $this->render('driver-debt-collection', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'debtType' => DEBT_DRIVER,
            'money' => $money,
        ]);
    }

    /**
     * Displays a list of trips for driver debt settlement.
     * Filters trips where driver debt settlement is not yet resolved.
     *
     * @return string The rendered view.
     */
    public function actionDriverDebtSettlement()
    {
        try {
            $searchModel = new SearchTripDriver();
            $params = Yii::$app->request->queryParams;
            $searchModel->filter_time = 'pickup_time DESC';
            $searchModel->driver_debt = 'driver_debt_settlement';
            $dataProvider = $searchModel->searchDriverDebt($params, DEBT_SWITCHBOARD);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionDriverDebtSettlement() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        return $this->render('driver-debt-settlement', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'debtType' => DEBT_SWITCHBOARD,
        ]);
    }

    public function actionDebtCustomers()
    {
        try {
            $searchModel = new SearchTripDriver();
            $params = Yii::$app->request->queryParams;
            $searchModel->filter_time = 'pickup_time DESC';
            $searchModel->driver_debt = 'driver_debt_settlement';
            $dataProvider = $searchModel->searchDriverDebt($params, DEBT_CUSTOMERS);
            $money = $searchModel->totalMoney(DEBT_CUSTOMERS);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionDebtCustomers() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        return $this->render('debt-customers', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'debtType' => DEBT_CUSTOMERS,
            'money' => $money,
        ]);
    }

    public function actionAdminDebtAgency()
    {
        try {
            $searchModel = new TripAgency();
            $params = Yii::$app->request->queryParams;
            $dataProvider = $searchModel->searchAgencyDebt($params, ADMIN_DEBT_AGENCY);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionAdminDebtAgency() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        return $this->render('admin-debt-agency/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAgencyDebtAdmin()
    {
        try {
            $searchModel = new TripAgency();
            $params = Yii::$app->request->queryParams;
            $dataProvider = $searchModel->searchAgencyDebt($params, AGENCY_DEBT_ADMIN);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionAgencyDebtAdmin() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }

        return $this->render('agency-debt-admin/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSearchData()
    {
        try {
            $searchModel = new SearchTripDriver();
            $params = Yii::$app->request->queryParams;
            $debt_type = $params['SearchBooking']['debt_type'];

            $dataProvider = $searchModel->searchDriverDebt($params, $debt_type);
            if (Yii::$app->request->isAjax) {
                $tripModel = new Trip();
                $tripList = $dataProvider->getModels();
                $htmlNew = '';
                if (isset($params['this_table'])) {
                    $htmlNew = $this->renderPartial($params['this_table'], compact(['searchModel', 'tripList', 'tripModel', 'dataProvider']));
                }

                return $htmlNew;
            } else {
                return $this->redirect(Yii::$app->request->referrer);
            }
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionSearchData() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionUpdateTripAgencyDebt()
    {
        try {
            $id = Yii::$app->request->post('id');
            $agency = Agency::findOne($id);
            $model = $this->tripService->getTripDebtByAgencyId($id);
            if ($model != null) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    foreach ($model as $trip) {
                        Yii::$app->db->createCommand()
                            ->update('trip', ['trip.agency_debt' => 1], ['id' => $trip->id])
                            ->execute();
                    }
                    $transaction->commit();

                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_update_debt_agency', [
                            'id' => $model->id,
                            'debt_agency' => 'Đã trả nợ',
                            'agency' => $agency->name,
                            'price' => MyStringHelper::convertIntegerToPrice($agency->price * count($model)),
                        ]),
                        'action' => 'update',
                    ]);

                    return true;
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }

            return false;
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionUpdateTripAgencyDebt() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionUpdateDriverDebtCollection()
    {
        try {
            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if ($model != null) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    Yii::$app->db->createCommand()
                        ->update('trip', ['driver_debt' => 1], ['id' => $id])
                        ->execute();

                    $transaction->commit();

                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_update_debt_driver', [
                            'id' => $model->id,
                            'debt_driver' => 'Đã đòi nợ',
                        ]),
                        'action' => 'update',
                    ]);

                    return true;
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }

            return false;
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionUpdateDriverDebtCollection() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionUpdateDriverDebtSettlement()
    {
        try {
            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if ($model != null) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    Yii::$app->db->createCommand()
                        ->update('trip', ['driver_debt' => 1], ['id' => $id])
                        ->execute();

                    $transaction->commit();

                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'trip_update_debt_driver', [
                            'id' => $model->id,
                            'debt_driver' => 'Đã trả',
                        ]),
                        'action' => 'update',
                    ]);

                    return true;
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }

            return false;
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionUpdateDriverDebtSettlement() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionUpdateDebtCustomers()
    {
        try {
            $id = Yii::$app->request->post('id');
            $model = $this->findModel($id);

            if ($model != null) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    Yii::$app->db->createCommand()
                        ->update('trip', ['collected_money' => 1], ['id' => $id])
                        ->execute();

                    $transaction->commit();

                    return true;
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }

            return false;
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionUpdateDebtCustomers() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionGetDetailTripAgency()
    {
        try {
            $searchModel = new TripAgency();
            $id = Yii::$app->request->post('id');

            return $this->renderPartial('components/table-agency-detail', ['tripList' => $searchModel->searchTripDebt($id), 'agency_id' => $id]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionGetDetailTripAgency() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionAcceptDebtTrip()
    {
        try {
            $param = Yii::$app->request->post();
            $this->debtService->updateTrip($param['trip_id']);

            return json_encode($this->debtService->queryAgencyData($param['agency_id']));
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('TripDriverController - actionAcceptDebtTrip() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }
}
