<?php

namespace app\controllers;

use app\models\GroupZaloSeller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ZaloSellerController implements the CRUD actions for GroupZaloSeller model.
 */
class ZaloSellerController extends BaseController
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
        $zaloSeller = new ActiveDataProvider([
            'query' => GroupZaloSeller::find(),
        ]);

        return $this->render('index', [
            'zaloSeller' => $zaloSeller,
        ]);
    }

    public function actionCreate()
    {
        $model = new GroupZaloSeller();
        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $model->group_zalo_catalogue_id = json_encode($model->group_zalo_catalogue_id);
                $model->save();
                $transaction->commit();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'zalo_seller_cud', [
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
        $zaloSeller = GroupZaloSeller::findOne($id);
        if ($zaloSeller->load(Yii::$app->request->post())) {
            $zaloSeller->group_zalo_catalogue_id = json_encode($zaloSeller->group_zalo_catalogue_id);
            $zaloSeller->save();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'zalo_seller_cud', [
                    'id' => $zaloSeller->id,
                    'name' => $zaloSeller->name,
                    'action' => ACTION_LIST['update'],
                ]),
                'action' => 'update',
            ]);

            return $this->redirect(['index']);
        }
        $zaloSeller->group_zalo_catalogue_id = json_decode($zaloSeller->group_zalo_catalogue_id, true);

        return $this->render('create', [
            'model' => $zaloSeller,
        ]);
    }

    public function actionDelete($id)
    {
        $zaloSeller = GroupZaloSeller::findOne($id);
        $zaloSeller->delete();
        Yii::$app->userLogger->logUserAction([
            'created_on' => date('Y-m-d H:i:s'),
            'user_id' => Yii::$app->user->id,
            'user_name' => Yii::$app->user->identity->username,
            'message' => Yii::t('app', 'zalo_seller_cud', [
                'id' => $zaloSeller->id,
                'name' => $zaloSeller->name,
                'action' => ACTION_LIST['delete'],
            ]),
            'action' => 'delete',
        ]);

        return $this->redirect(['index']);
    }
}
