<?php

namespace app\modules\api\controllers;

use app\helpers\MyHelper;
use app\models\RequestCallBack;
use app\models\Status;
use Yii;
use yii\base\ErrorException;
use yii\rest\ActiveController;

class RequestCallBackController extends ActiveController
{
    public $modelClass = 'app\models\RequestCallBack';

    // create request call back
    public function actionCreateCallBack()
    {
        try {
            $params = Yii::$app->request->post();
            $model = new RequestCallBack();
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $checkHasPhoneStandbyState = RequestCallBack::find()->where([
          'phone' => $params['phone'],
          'status' => REQUEST_CALL_BACK_WAITING,
        ])->one();

                if ($checkHasPhoneStandbyState) {
                    return [
            'status' => Status::STATUS_BAD_REQUEST,
            'message' => 'Số điện thoại đã được đăng ký, xin vui lòng chờ phản hồi từ tổng đài viên!',
            'data' => [],
          ];
                }

                $model->phone = $params['phone'];
                $model->utm_source = (isset($params['utm-source']) ? $params['utm-source'] : '');
                $model->utm_campaign = (isset($params['utm-campaign']) ? $params['utm-campaign'] : '');
                $model->utm_medium = (isset($params['utm-medium']) ? $params['utm-medium'] : '');
                $model->remote_ip = (isset($params['remote-ip']) ? $params['remote-ip'] : '');
                $model->url = (isset($params['url']) ? $params['url'] : '');
                $model->tracking_info = (isset($params['tracking-info']) ? $params['tracking-info'] : '');
                $model->website = (isset($params['website']) ? $params['website'] : '');
                $model->status = REQUEST_CALL_BACK_WAITING;
                $model->source_trip = (isset($params['source']) ? $params['source'] : 6);
                $model->save();
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
            }

            if ($model->validate()) {
                return [
          'status' => Status::STATUS_OK,
          'message' => 'Chúng tôi sẽ liên hệ với bạn trong thời gian sớm nhất',
          'data' => [],
        ];
            } else {
                $errors = $model->errors;

                return [
          'status' => Status::STATUS_INTERNAL_SERVER_ERROR,
          'message' => 'error',
          'data' => $errors,
        ];
            }
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('ClientController - actionCatch() - ' . $e->getMessage());
        }
    }
}
