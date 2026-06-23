<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\models\Agency;
use app\models\agency\SearchAgency;
use app\services\AgencyService;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TripController implements the CRUD actions for Trip model.
 */
class AgencyController extends BaseController
{
    /**
     * @var AgencyService
     */
    protected $agencyService;

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
                'actions' => [],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        $this->agencyService = new AgencyService();
    }


    public function actionIndex()
    {
        try {
            $searchModel = new SearchAgency();
            $agency = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $agency,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }
    }

    public function actionCreate()
    {
        try {
            $model = new Agency();
            if ($model->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();
                $validationResult = $this->validatePhone(compact('model'));
                if ($validationResult === false) {
                    return $this->render('create', compact('model'));
                }

                try {
                    $dataTokenAndQrcode = $this->agencyService->createTokenAndQrCode();

                    $model->token = $dataTokenAndQrcode['token'];
                    $model->price = str_replace('.', '', $model->price);
                    $model->qr_code = $dataTokenAndQrcode['qrCode'];
                    $model->status = 1;
                    $result = $model->save();

                    $transaction->commit();

                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'agency_cud', [
                            'id' => $model->id,
                            'name' => $model->name,
                            'action' => ACTION_LIST['create'],
                        ]),
                        'action' => 'create',
                    ]);
                    if ($result > 0) {
                        return $this->redirect(['index']);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }

            return $this->render('create', compact('model'));
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        try {
            $agency = Agency::findOne($id);
            if ($agency->load(Yii::$app->request->post())) {
                $transaction = Yii::$app->db->beginTransaction();

                try {
                    if (empty($agency->token)) {
                        $dataTokenAndQrcode = $this->agencyService->createTokenAndQrCode();

                        $agency->token = $dataTokenAndQrcode['token'];
                        $agency->qr_code = $dataTokenAndQrcode['qrCode'];
                    }
                    $agency->price = str_replace('.', '', $agency->price);
                    $result = $agency->save();
                    $transaction->commit();
                    $validationResult = $this->validatePhone(['model' => $agency]);
                    if ($validationResult === false) {
                        return $this->render('update', compact('agency'));
                    }
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'agency_cud', [
                            'id' => $agency->id,
                            'name' => $agency->name,
                            'action' => ACTION_LIST['update'],
                        ]),
                        'action' => 'update',
                    ]);
                    if ($result > 0) {
                        return $this->redirect([
                        'index',
                        'agency' => new ActiveDataProvider([
                            'query' => Agency::find(),
                        ]),
                    ]);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }

            return $this->render('update', [
                'agency' => $agency,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }
    }

    public function actionDelete($id)
    {
        $agency = Agency::findOne($id);
        $id = $agency->id;
        $agency->delete();
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'agency_cud', [
                'id' => $agency->id,
                'name' => $agency->name,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);

        return $this->redirect(['index']);
    }
    /**
     * Validate phone
     *
     * @param array $params
     * @return bool
     */
    protected function validatePhone($params = [])
    {
        $check = true;

        if (isset($params['model']->phone) && ! preg_match('/^(?:\+)?[0-9]+$/', $params['model']->phone)) {
            $params['model']->addError('phone', 'Định dạng số điện thoại không hợp lệ.');
            $check = false;
        }

        return $check;
    }
}
