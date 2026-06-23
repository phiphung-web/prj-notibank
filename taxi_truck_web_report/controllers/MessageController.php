<?php

namespace app\controllers;

use app\models\Message;
use app\models\MessageSearch;
use app\services\MessageService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

use app\services\NotificationService;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends BaseController
{
    private $notificationService;
    private $messageService;

    public function init()
    {
        parent::init();
        $this->messageService = new MessageService();
        $this->notificationService = new NotificationService();
    }
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

    /**
     * Lists all Message models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MessageSearch();
        $searchModel->createTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        if ($searchModel->load(Yii::$app->request->get())) {
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Displays a single Message model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($success)
    {
        return $this->render('view', ['success' => $success, 'msg' => $success ? 'Gửi thành công' : 'Gửi lỗi. Liên hệ với trùm cuối để kiểm tra!']);
    }

    /**
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if (empty($model->phone)) {
                    // $title = 'Thông báo admin';
                    // $con = 'Đã có thêm thông báo mới từ admin XeVipNoiBai mời các bạn xem trong thông báo.';
                    // $util = new Util();
                    // $util->sendMessage($title, $con);
                    $this->notificationService->sendMessageAllDriver([
                        'title' => $model->title,
                        'content' => $model->content,
                    ]);
                } else{
                    $this->messageService->sendMessageForDriver($model);
                }
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'message_cd', [
                        'id' => $model->id,
                        'title' => $model->title,
                        'action' => 'Gửi',
                    ]),
                    'action' => 'accept',
                ]);

                return $this->redirect(['view', 'success' => true]);
            } else {
                return $this->redirect(['view', 'success' => false]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Message model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    /*public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }*/

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'message_cd', [
                'id' => $model->id,
                'title' => $model->title,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);
        Yii::$app->session->setFlash('success', 'Xóa thành công.');

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
