<?php

namespace app\modules\api\controllers;

use app\models\Agency;
use app\models\Status;
use app\services\AgencyService;
use Yii;
use yii\base\InvalidConfigException;
use yii\rest\ActiveController;

class AgencyController extends ActiveController
{
    public $modelClass = 'app\models\Agency';
    protected $agencyService;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->agencyService = new AgencyService();
    }

    //
    public function actionRegister()
    {
        $params['Agency'] = Yii::$app->request->post();
        $model = new Agency();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model->load($params);
            $dataTokenAndQrcode = $this->agencyService->createTokenAndQrCode();
            $model->token = $dataTokenAndQrcode['token'];
            $model->qr_code = $dataTokenAndQrcode['qrCode'];
            $model->status = 1;
            $model->percent = 0;
            $model->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        if ($model->validate()) {
            return [
                'status' => Status::STATUS_OK,
                'message' => 'success',
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
    }
}
