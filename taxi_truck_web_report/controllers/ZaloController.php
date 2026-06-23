<?php

namespace app\controllers;

use app\models\GroupZalo;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TripController implements the CRUD actions for Trip model.
 */
class ZaloController extends BaseController
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
                'actions' => [],
            ],
        ];
    }

    public function actionIndex()
    {
        try {
            $searchModel = new \app\models\SearchGroupZalo();

            $zalo = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $zalo,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            pre($e->getMessage());
        }
    }

    public function actionCreate()
    {
        $model = new GroupZalo();
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->status = 1;
                $model->save();
                $transaction->commit();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'zalo_cud', [
                        'id' => $model->id,
                        'name' => $model->name,
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

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $zalo = GroupZalo::findOne($id);
        if ($zalo->load(Yii::$app->request->post()) && $zalo->save()) {
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'zalo_catalogue_cud', [
                    'id' => $zalo->id,
                    'name' => $zalo->name,
                    'action' => ACTION_LIST['update'],
                ]),
                'action' => 'update',
            ]);

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'zalo' => $zalo,
        ]);
    }

    public function actionDelete($id)
    {
        $zalo = GroupZalo::findOne($id);
        $zalo->status = 0;
        $zalo->save();
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'zalo_catalogue_cud', [
                'id' => $zalo->id,
                'name' => $zalo->name,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);

        return $this->redirect(['index']);
    }
}
