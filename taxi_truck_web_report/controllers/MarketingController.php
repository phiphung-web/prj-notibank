<?php

namespace app\controllers;

use app\models\marketing\Booking;
use app\models\marketing\Callback;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * MarketingController implements the CRUD actions for Message model.
 */
class MarketingController extends BaseController
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

    public function actionIndex()
    {
        $searchModel = new Booking();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');
        $param = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($param);

        return $this->render('booking/index', compact(['dataProvider', 'searchModel']));
    }

    public function actionCallback()
    {
        $searchModel = new Callback();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');
        $param = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($param);

        return $this->render('callback/index', compact(['dataProvider', 'searchModel']));
    }
}
