<?php

namespace app\controllers;

use app\models\GroupZalo;
use app\models\GroupZaloCatalogue;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * TripController implements the CRUD actions for Trip model.
 */
class ZaloCatalogueController extends BaseController
{
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
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
        $dataProvider = new ActiveDataProvider([
            'query' => GroupZaloCatalogue::find()->where(['status' => 1]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new GroupZaloCatalogue();
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->status = 1;
                $model->created_on = date('Y-m-d H:i:s');
                $model->modified_on = $model->created_on;
                $model->save();
                $transaction->commit();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'zalo_catalogue_cud', [
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
        $zaloCatalogue = GroupZaloCatalogue::findOne($id);
        $zaloCatalogue->modified_on = date('Y-m-d H:i:s');
        if ($zaloCatalogue->load(Yii::$app->request->post()) && $zaloCatalogue->save()) {
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'zalo_catalogue_cud', [
                    'id' => $zaloCatalogue->id,
                    'name' => $zaloCatalogue->name,
                    'action' => ACTION_LIST['update'],
                ]),
                'action' => 'update',
            ]);

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'zaloCatalogue' => $zaloCatalogue,
        ]);
    }

    public function actionDelete($id)
    {
        if (GroupZalo::find()->where(['group_zalo_catalogue' => $id])->count() > 0) {
            return $this->redirect(['index', 'message' => 'Có tồn tại Zalo, không thể xóa!']);
        }
        $zaloCatalogue = GroupZaloCatalogue::findOne($id);
        $zaloCatalogue->status = 0;
        $zaloCatalogue->modified_on = date('Y-m-d H:i:s');
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'zalo_catalogue_cud', [
                'id' => $zaloCatalogue->id,
                'name' => $zaloCatalogue->name,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);
        $zaloCatalogue->save();

        return $this->redirect(['index']);
    }
}
