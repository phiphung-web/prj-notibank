<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\models\Customer;
use app\models\customerService\CustomerService;
use app\models\customerService\CustomerServiceSearch;
use app\models\Status;
use app\models\Trip;
use app\services\CustomerServiceService;
use app\services\SystemConfigurationService;
use Yii;
use yii\base\ErrorException;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\web\Response;

class CustomerServiceController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    private $customerServiceSearchModel;

    public $customerServiceService;

    public $systemConfigurationService;

    public function init()
    {
        parent::init();
        $this->customerServiceSearchModel = new CustomerServiceSearch();
        $this->customerServiceService = new CustomerServiceService();
        $this->systemConfigurationService = new SystemConfigurationService();
    }

    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $params['CustomerServiceSearch']['source'] = 0;
        $params['CustomerServiceSearch']['vip'] = 0;
        $this->customerServiceSearchModel->times = [0,1,2];
        $this->customerServiceSearchModel->status = '';
        $dataProvider = $this->customerServiceSearchModel->search($params);
        $feedbacks = MyHelper::getFeedbackConfigbie();
        $adminList = $this->getListAdmin();

        return $this->render('index', [
            'model' => $this->customerServiceSearchModel,
            'modelCustomerService' => new CustomerService(),
            'dataProvider' => $dataProvider,
            'adminList' => $adminList,
            'feedbacks' => $feedbacks,
            'source' => $params['CustomerServiceSearch']['source'],
            'listPermission' => $this->listPermission,
        ]);
    }

    public function actionCustomerRollback()
    {
        $params = Yii::$app->request->queryParams;
        $params['CustomerServiceSearch']['source'] = SOURCE_TRIP_TYPE_CUSTOMER;
        $params['CustomerServiceSearch']['vip'] = 0;
        $dataProvider = $this->customerServiceSearchModel->search($params);
        $adminList = $this->getListAdmin();

        return $this->render('index', [
            'model' => $this->customerServiceSearchModel,
            'modelCustomerService' => new CustomerService(),
            'dataProvider' => $dataProvider,
            'adminList' => $adminList,
            'source' => $params['CustomerServiceSearch']['source'],
            'listPermission' => $this->listPermission,
        ]);
    }

    public function actionCustomerVip()
    {
        $params = Yii::$app->request->queryParams;
        $params['CustomerServiceSearch']['source'] = '';
        $params['CustomerServiceSearch']['vip'] = 1;
        $dataProvider = $this->customerServiceSearchModel->search($params);
        $adminList = $this->getListAdmin();

        return $this->render('index', [
            'model' => $this->customerServiceSearchModel,
            'modelCustomerService' => new CustomerService(),
            'dataProvider' => $dataProvider,
            'adminList' => $adminList,
            'source' => $params['CustomerServiceSearch']['source'],
            'listPermission' => $this->listPermission,
        ]);
    }

    public function actionDeliver()
    {
        $params = Yii::$app->request->post();
        $data = $this->customerServiceService->upsertCustomerServiceBatch($params);

        return $data;
    }

    public function actionDeleteAll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $params = Yii::$app->request->post();
            if (isset($params['id']) && is_array($params['id']) && count($params['id'])) {
                foreach ($params['id'] as $key => $value) {
                    $customerService = CustomerService::findOne(['trip_id' => $value]);
                    if ($customerService == null) {
                        $customerService = new CustomerService();
                        $customerService->userid_created = Yii::$app->user->id;
                        $customerService->created_at = date('Y-m-d H:i:s');
                    }

                    // Chuẩn bị dữ liệu
                    $model = Trip::find()->where(['trip.id' => $value])->joinWith(['bid'])->one();
                    $model_customer = Customer::findOne(['phone' => $model->customer_phone]);
                    $customerService->trip_id = (isset($model->id) ? (int) $model->id : 0);
                    $customerService->customer_id = (isset($model_customer->id) ? (int) $model_customer->id : 0);
                    $customerService->driver_id = (isset($model->bid->driver_id) ? (int) $model->bid->driver_id : 0);
                    $customerService->type = 0;
                    $customerService->cus_feedback_trip = '';
                    $customerService->cus_feedback_driver = '';
                    $customerService->driver_feedback_cus = '';
                    $customerService->status = STATUS_CUSTOMER_SERVICE_NO_CHECK;
                    $customerService->point = 10;
                    $customerService->userid_updated = Yii::$app->user->id;

                    // Cập nhật dữ liệu từ form vào hệ thống
                    $transaction = Yii::$app->db->beginTransaction();

                    try {
                        $customerService->userid_updated = Yii::$app->user->id;
                        $customerService->save();
                        $transaction->commit();
                    } catch (\Exception $e) {
                        $transaction->rollBack();

                        throw $e;
                    } catch (\Throwable $e) {
                        $transaction->rollBack();

                        throw $e;
                    }

                    // Lưu log vào hệ thống
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'customer_service_action', [
                            'id' => $customerService->trip_id,
                        ]),
                        'action' => 'update',
                    ]);
                }
            }

            return [
                'status' => Status::STATUS_SUCCESS,
                'message' => 'Thành công',
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('CustomerServiceController - actionDeleteAll() - ' . $e->getMessage());
        }

        return [
            'status' => Status::STATUS_ERROR,
            'message' => 'Thất bại',
        ];
    }

    public function actionView()
    {
        $model = CustomerService::findOne(['trip_id' => $_GET['trip_id']]);
        $model_customer = Customer::findOne(['phone' => $_GET['customer_phone']]);

        return $this->asJson(isset($model) && $model != null ? array_merge(['customer_id' => (int) $model_customer->id], $model->attributes) : ['customer_id' => (int) $model_customer->id]);
    }

    public function actionUpdate()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $params = Yii::$app->request->post();
            $customerService = CustomerService::findOne(['trip_id' => (isset($params['CustomerService']['trip_id']) ? $params['CustomerService']['trip_id'] : 0)]);
            $customerServiceType = STATUS_CUSTOMER_SERVICE_NO_PROCESS;
            if ($customerService == null) {
                $customerService = new CustomerService();
                $customerService->userid_created = Yii::$app->user->id;
                $customerServiceType = $customerService->type;
            }

            if ($customerService->load($params)) {
                // Cập nhật dữ liệu từ form vào hệ thống
                $customerService = $this->customerServiceService->updateCustomerService($customerService);

                // Cập nhật điểm cho tài xế
                $this->customerServiceService->updateDriverPoints($customerService, $customerServiceType);


                // Lưu log vào hệ thống
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'customer_service_action', [
                        'id' => $customerService->trip_id,
                    ]),
                    'action' => 'update',
                ]);
            }
            $transaction->commit();

            return $this->redirect(Yii::$app->request->referrer);
        } catch (ErrorException $e) {
            $transaction->rollBack();
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('CustomerServiceController - actionUpdate() - ' . $e->getMessage());
        }
    }

    public function getListAdmin()
    {
        $query = new Query();
        $outputArray = [];
        $adminList = $query->select([
            'admin.*',
            'auth_assignment.item_name as role',
        ])
            ->from('admin')
            ->join('JOIN', 'auth_assignment', 'auth_assignment.user_id = admin.id')
            ->where(['admin.status' => 1, 'auth_assignment.item_name' => ['NHAN_VIEN_ROLE', 'ADMIN_ROLE', 'SUPERMOD_ROLE', 'QUAN_LY_ROLE', 'KE_TOAN_ROLE', 'MOD_ROLE']])
            ->createCommand()
            ->queryAll();

        if (isset($adminList) && is_array($adminList) && count($adminList)) {
            foreach ($adminList as $item) {
                $outputArray[$item['id']] = $item['username'];
            }
        }

        return $outputArray;
    }
}
