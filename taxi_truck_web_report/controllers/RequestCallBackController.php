<?php

namespace app\controllers;

use app\models\RequestCallBack;
use app\models\SearchRequestCallBack;
use app\models\SystemConfiguration;
use Yii;
use yii\filters\VerbFilter;

class RequestCallBackController extends BaseController
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

    public function actionIndex()
    {
        $model = new SearchRequestCallBack();
        $model->createdOnTimeRange = date('Y-m-d') . ' - ' . date('Y-m-d');
        $dataRequestCallBack = $model->search(Yii::$app->request->queryParams);

        // get reason reject
        $getReasonReject = SystemConfiguration::find()
            ->select('content')
            ->where(['keyword' => 'reason_reject'])
            ->scalar();

        $getReasonReject = CHOOSE_REASON . '|' . $getReasonReject;

        $dataReasonReject = explode('|', $getReasonReject);

        $dataReasonReject[999] = ADD_TYPE_REJECT;

        return $this->render('index', [
      'model' => $model,
      'dataRequestCallBack' => $dataRequestCallBack,
      'dataReasonReject' => $dataReasonReject,
    ]);
    }

    // cancel request call back
    public function actionCancel()
    {
        $params = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $dataRequestCallBack = RequestCallBack::findOne($params['SearchRequestCallBack']['id']);

            if ($dataRequestCallBack) {
                $dataRequestCallBack->status = REQUEST_CALL_BACK_CANCEL;
                $dataRequestCallBack->note = ! empty($params['SearchRequestCallBack']['note']) ? $params['SearchRequestCallBack']['note'] : null;
                $dataRequestCallBack->type_reject = $params['SearchRequestCallBack']['type_reject'];
                $dataRequestCallBack->save();
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
}
