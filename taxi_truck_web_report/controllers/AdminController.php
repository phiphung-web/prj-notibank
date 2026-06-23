<?php

namespace app\controllers;

use app\models\Admin;
use app\models\Agency;
use app\models\AuthAssignment;
use app\models\Revenue;
use app\services\BankTransactionService;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AdminController implements the CRUD actions for Admin model.
 */
class AdminController extends BaseController
{
    protected $bankTransactionService;

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

    public function init()
    {
        parent::init();
        $this->bankTransactionService = new BankTransactionService();
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Lists all Admin models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = new Query();

        $query->select([
            'admin.id as id',
            'admin.username',
            'admin.status as status',
            'auth_assignment.item_name as role',
            'agency.name as agency_name',
        ])
            ->from('admin')
            ->join('JOIN', 'auth_assignment', 'auth_assignment.user_id =admin.id')
            ->leftJoin('agency', 'admin.agency_id = agency.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Admin model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Admin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed The response, usually a view rendering or a redirection.
     * @throws \Exception|\Throwable If an error occurs during the creation process.
     */
    public function actionCreate()
    {
        $prams = Yii::$app->request->post('Admin');
        $model = new Admin();
        $model->scenario = 'create'; // Set the scenario to 'create' for validation purposes
        if ($model->load(Yii::$app->request->post())) {
            $validationResult = $this->validateAccount(compact('model'));

            // If validation fails, render the 'create' view with the model and error messages
            if ($validationResult === false) {
                return $this->render('create', compact('model'));
            }

            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->agency_id = ! empty($prams['agency_id']) ? $prams['agency_id'] : null;
                $model->password = md5($model->password);
                $model->save();

                $authAssign = new AuthAssignment();
                $authAssign->user_id = (string) $model->id;
                $authAssign->item_name = $model->role;
                $authAssign->created_at = time();

                // Validate and save the AuthAssignment
                if ($authAssign->validate()) {
                    $authAssign->save();
                } else {
                    throw new \Exception('loi');
                }

                $this->bankTransactionService->updateBankTransaction($model, Yii::$app->request->post());
                $transaction->commit();

                // Log the user action
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'admin_cud', [
                        'id' => $model->id,
                        'username' => $model->username,
                        'action' => ACTION_LIST['create'],
                    ]),
                    'action' => 'create',
                ]);

                return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack(); // Roll back the transaction and re-throw the throwable

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack(); // Roll back the transaction and re-throw the throwable

                throw $e;
            }
        }

        // If no data has been submitted, render the 'create' view with the model
        return $this->render('create', [
            'model' => $model,
            'dataAgency' => $this->getAllAgency(),
        ]);
    }

    /**
     * Updates an existing Admin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $prams = Yii::$app->request->post('Admin');
        $model = $this->findModel($id);
        $model->role = $model->getRole();
        $bankTransaction = [];
        $bankTransactionList = $this->bankTransactionService->getBankTransactionList($model);
        if (isset($bankTransactionList) && is_array($bankTransactionList) && count($bankTransactionList)) {
            foreach ($bankTransactionList as $key => $value) {
                $bankTransaction[$value['type_bank']] = $value;
            }
        }
        $pass_old = $model->password;

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            $validationResult = $this->validateAccount(compact('model'));

            // If validation fails, render the 'create' view with the model and error messages
            if ($validationResult === false) {
                return $this->render('create', compact('model'));
            }

            try {
                $model->agency_id = ! empty($prams['agency_id']) ? $prams['agency_id'] : null;

                if ($model->password === $pass_old) {
                    $model->password = $pass_old;
                } else {
                    $model->password = md5($model->password);
                }
                $model->save();

                $authAssign = AuthAssignment::find()->where(['user_id' => $id])->one();
                $authAssign->item_name = $model->role;

                if ($authAssign->validate()) {
                    $authAssign->save();
                } else {
                    throw new \Exception('loi');
                }

                $transaction->commit();
                $this->bankTransactionService->updateBankTransaction($model, Yii::$app->request->post());

                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'admin_cud', [
                        'id' => $model->id,
                        'username' => $model->username,
                        'action' => ACTION_LIST['update'],
                    ]),
                    'action' => 'update',
                ]);

                return $this->redirect(['index']);
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
            'bankTransaction' => $bankTransaction,
            'dataAgency' => $this->getAllAgency(),
        ]);
    }

    public function actionChangePw($id)
    {
        if (! Yii::$app->user->can('ADMIN_ROLE')) {
            Yii::$app->session->setFlash('error', 'Bạn không có quyền truy cập vào trang này.');

            return $this->redirect(['index']);
        }

        $model = $this->findModel($id);
        $changePasswordForm = new \app\models\ChangePasswordForm();

        if ($changePasswordForm->load(Yii::$app->request->post()) && $changePasswordForm->validate()) {
            try {
                $model->password = md5($changePasswordForm->new_password);
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Mật khẩu đã được thay đổi thành công.');
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'change_pass', [
                            'id' => $model->id,
                            'username' => $model->username,
                        ]),
                        'action' => 'update',
                    ]);

                    return $this->redirect(['index']);
                } else {
                    Yii::$app->session->setFlash('error', 'Không thể đổi mật khẩu.');
                }
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
            }
        }

        return $this->render('changepw', [
            'model' => $model,
            'changePasswordForm' => $changePasswordForm,
        ]);
    }


    /**
     * Deletes an Admin model.
     *
     * @param int $id The ID of the Admin model to be deleted.
     * @return mixed The response to the user after the delete operation.
     */
    public function actionDelete($id)
    {
        $admin = Admin::findOne($id);
        if ($admin) {
            $admin->delete();
        }
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'admin_cud', [
                'id' => $admin->id,
                'username' => $admin->username,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);

        return $this->redirect(['index']);
    }

    /**
     * Finds the Admin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Admin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Admin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (! Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new \app\models\LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->session->set('IsAuthorized', true);
            $permissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->id);
            if (isset($permissions['MOD_PERMISSION']) || isset($permissions['ADMIN_PERMISSION'])) {
                Yii::$app->session->set('IsAdmin', true);
            }
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'login'),
                'action' => 'login',
            ]);

            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }


    public function actionChangePass()
    {
        $model = new \app\models\ChangePassForm();
        $modeluser = \app\models\Admin::find()->where([
            'username' => Yii::$app->user->identity->username,
        ])->one();
        $userId = Yii::$app->user->identity->id;
        $searchModel = new Revenue();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');
        $dataProvider = $searchModel->searchUserStatistic(Yii::$app->request->queryParams, $userId);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                try {
                    $modeluser->password = md5($model->newpass);
                    $modeluser->bonus = (string)$modeluser->bonus;
                    if ($modeluser->save()) {
                        Yii::$app->getSession()->setFlash(
                            'success',
                            'Password changed'
                        );
                        Yii::$app->userLogger->logUserAction([
                            'created_on' => date('Y-m-d H:i:s'),
                            'user_id' => Yii::$app->user->id,
                            'user_name' => Yii::$app->user->identity->username,
                            'message' => Yii::t('app', 'change_pass', [
                                'id' => $modeluser->id,
                                'username' => $modeluser->username,
                            ]),
                            'action' => 'update',
                        ]);

                        return $this->refresh();
                    } else {
                        Yii::$app->getSession()->setFlash(
                            'error',
                            'Password not changed'
                        );

                        return $this->refresh();
                    }
                } catch (Exception $e) {
                    Yii::$app->getSession()->setFlash(
                        'error',
                        "{$e->getMessage()}"
                    );

                    return $this->render('changepass', [
                        'model' => $model,
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]);
                }
            } else {
                return $this->render('changepass', [
                    'model' => $model,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
            }
        } else {
            return $this->render('changepass', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'action' => 'logout',
            'message' => Yii::t('app', 'logout'),
        ]);
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Validate account parameters before creation.
     *
     * @param array $params An array containing parameters for validation.
     *                      Expected keys: 'model' - The Admin model instance to validate.
     * @return bool Returns true if validation passes, false otherwise.
     */
    public function validateAccount($params = []): bool
    {
        $check = true;
        if ($params['model']->scenario === 'create') {
            // Check if a user with the same username already exists
            $existingUser = Admin::findOne(['username' => $params['model']->username]);
            if ($existingUser !== null && $existingUser instanceof Admin) {
                $params['model']->addError('username', 'Tài khoản đã tồn tại.');
                $check = false;
            }
        }

        // Validate phone number format (if provided)
        if (isset($params['model']->phone) && ! empty($params['model']->phone) && ! preg_match('/^(?:\+)?[0-9]+$/', $params['model']->phone)) {
            $params['model']->addError('phone', 'Định dạng số điện thoại không hợp lệ.');
            $check = false;
        }

        return $check;
    }

    public function getAllAgency()
    {
        $agencyList = [];
        $dataAgency = Agency::find()
            ->select(['id', 'name'])
            ->where(['status' => 1])
            ->all();

        if (! empty($dataAgency)) {
            $agencyList = ArrayHelper::map($dataAgency, 'id', 'name');
        }

        return $agencyList;
    }

}
