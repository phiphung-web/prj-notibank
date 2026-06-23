<?php

namespace app\controllers;

use app\helpers\MyStringHelper;
use app\models\Admin;
use app\models\Driver;
use app\models\PayTransaction;
use app\models\SearchPayTransaction;
use app\models\SearchSmsPayTransaction;
use app\models\Status;
use app\services\BankTransactionService;
use app\services\NotificationService;
use app\services\PayService;
use app\services\PayTransactionService;
use app\services\SystemConfigurationService;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * PayController implements the CRUD actions for PayTransaction model.
 */
class PayController extends BaseController
{
    private PayService $payService;
    private BankTransactionService $bankTransactionService;
    private PayTransactionService $payTransactionService;
    private SystemConfigurationService $systemConfiguration;
    private NotificationService $notificationService;
    public function init()
    {
        parent::init();
        $this->payService = new PayService();
        $this->systemConfiguration = new SystemConfigurationService();
        $this->bankTransactionService = new BankTransactionService();
        $this->payTransactionService = new PayTransactionService();
        $this->notificationService = new NotificationService();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
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
     * Lists all PayTransaction models.
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new SearchPayTransaction();
        $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all PayTransaction SMS models.
     * @return string
     */
    public function actionListSms(): string
    {
        $searchModel = new SearchSmsPayTransaction();
        $searchModel->isAll = true;
        $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 1);
        $adminList = $this->bankTransactionService->getAdminList(true);
        $systemConfiguration = $this->systemConfiguration->getAllConfiguration();

        return $this->render('index_sms', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'adminList' => $adminList,
            'systemConfiguration' => $systemConfiguration,
        ]);
    }

    /**
     * Lists all PayTransaction SMS models.
     * @return string
     */
    public function actionListCustomer(): string
    {
        $searchModel = new SearchSmsPayTransaction();
        $searchModel->isAll = true;
        $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $adminList = $this->bankTransactionService->getAdminList(false);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 0);
        $systemConfiguration = $this->systemConfiguration->getAllConfiguration();

        return $this->render('index_customer', [
            'searchModel' => $searchModel,
            'adminList' => $adminList,
            'dataProvider' => $dataProvider,
            'systemConfiguration' => $systemConfiguration,
        ]);
    }

    /**
     * Lists all PayTransaction SMS models.
     * @return string
     */
    public function actionListPayment(): string
    {
        $searchModel = new SearchSmsPayTransaction();
        $searchModel->isAll = true;
        $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $adminList = $this->bankTransactionService->getAdminList(false);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, 0);
        $systemConfiguration = $this->systemConfiguration->getAllConfiguration();

        return $this->render('index_customer', [
            'searchModel' => $searchModel,
            'adminList' => $adminList,
            'dataProvider' => $dataProvider,
            'systemConfiguration' => $systemConfiguration,
        ]);
    }

    /**
     * Accepts a recharge transaction.
     * @throws NotFoundHttpException
     */
    public function actionAcceptRecharge(): string
    {
        // Check if the model is not found and return a 'Not Found' error message
        $id = Yii::$app->request->post('id');
        $model = $this->findModel($id);
        if ($model === null) {
            return $this->payService->jsonResponse(Status::STATUS_NOT_FOUND, 'Không tìm thấy giao dịch!');
        }
        if ($model->status) {
            return $this->payService->jsonResponse(Status::STATUS_NOT_FOUND, 'Giao dịch đã được xác nhận!');
        }

        // Check if the driver is not found and return a 'Not Found' error message
        $driverPhone = Yii::$app->request->post('phone');
        $driver = $this->findDriver($driverPhone);
        if ($driver === null) {
            return $this->payService->jsonResponse(Status::STATUS_NOT_FOUND, 'Driver not found!');
        }

        // Begin a database transaction
        $transaction = Yii::$app->db->beginTransaction();

        try {
            // Call the updateModelAndDriver function from payService to update the model and driver
            $storeModelAndDriver = $this->payService->updateModelAndDriver($model, $driver);
            if ($this->payService->saveModelAndDriver($storeModelAndDriver['model'], $storeModelAndDriver['driver'])) {
                $transaction->commit();

                return $this->payService->jsonResponse(Status::STATUS_OK, 'Successfully recharged the driver!');
            } else {
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        return $this->payService->jsonResponse(Status::STATUS_BAD_REQUEST, 'An error occurred!');
    }

    /**
     * Displays a single PayTransaction model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    //    public function actionView($id)
    //    {
    //        return $this->render('view', [
    //            'model' => $this->findModel($id),
    //        ]);
    //    }

    /**
     * Creates a new PayTransaction model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|Response
     * @throws \Throwable
     * @throws Exception
     */
    public function actionCreate()
    {
        $model = new PayTransaction();

        if ($model->load(Yii::$app->request->post())) {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();

            try {
                $driver = Driver::findOne($model->driver_id);
                $moneyBefore = $driver->money;
                $driver->money = $driver->money + MyStringHelper::convertStringToInteger($model->money);
                $driver->save();

                $model->money_before = $moneyBefore;
                $model->money_after = $driver->money;
                $model->phone = $driver->username;
                $model->save();

                $adminId = Yii::$app->user->id;
                $admin = Admin::findOne($adminId);

                $data = [
                    'type' => NOTIFICATION_PAY_TYPE,
                    'money' => ($model->money) / 10000,
                    'total_money' => $driver->money,
                ];
                $this->notificationService->sendNotificationByUsername($driver, $admin, null, 'Nạp tiền thành công', 'Hệ thống đã nạp ' . MyStringHelper::convertIntegerToPrice($model->money) . 'đ vào tài khoản của lái xe ' . $driver->display_name, '', $data);
                $transaction->commit();
                $this->payTransactionService->sendMessageZns($model, $this->systemConfiguration->getAllConfiguration(), $driver, 'success');
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'pay_create', [
                        'id' => $model->id,
                        'money' => MyStringHelper::convertIntegerToPrice($model->money),
                        'action' => ACTION_LIST['create'],
                    ]),
                    'action' => 'create',
                ]);
            } catch (\Exception | \Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PayTransaction model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    //    public function actionUpdate($id)
    //    {
    //        $model = $this->findModel($id);
    //
    //        if ($model->load(Yii::$app->request->post()) && $model->save()) {
    //            return $this->redirect(['view', 'id' => $model->id]);
    //        }
    //
    //        return $this->render('update', [
    //            'model' => $model,
    //        ]);
    //    }

    /**
     * Deletes an existing PayTransaction model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    //    public function actionDelete($id)
    //    {
    //        $this->findModel($id)->delete();
    //
    //        return $this->redirect(['index']);
    //    }

    /**
     * Finds the PayTransaction model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return PayTransaction the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): PayTransaction
    {
        if (($model = PayTransaction::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function findDriver($phone)
    {
        return Driver::find()->where(['username' => $phone])->one();
    }

    public function actionDeleteAcceptRecharge(): string
    {
        $id = Yii::$app->request->post('id');
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $dataPayTransaction = PayTransaction::findOne($id);
            if (! empty($dataPayTransaction)) {
                $dataPayTransaction->is_disabled = 1;
                $dataPayTransaction->disabled_on = new \yii\db\Expression('NOW()');
                $dataPayTransaction->save();
                $transaction->commit();

                return $this->payService->jsonResponse(Status::STATUS_OK, 'Successfully recharged the driver!');
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        return $this->payService->jsonResponse(Status::STATUS_BAD_REQUEST, 'An error occurred!');
    }
}
