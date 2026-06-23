<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\models\Trip;
use app\services\CallDriverService;
use ErrorException;
use Yii;
use yii\web\Response;

class CallDriverController extends BaseController
{
    protected $callDriverService;

    public function init()
    {
        parent::init();
        $this->callDriverService = new CallDriverService();
    }

    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $tripList = $this->callDriverService->getTripNeedCall(TIME_CALL_DRIVER, $params);

        return $this->render('index', [
            'tripList' => $tripList,
        ]);
    }

    public function actionReload()
    {
        try {
            $params = Yii::$app->request->queryParams;
            $tripList = $this->callDriverService->getTripNeedCall(TIME_CALL_DRIVER, $params);
            Yii::$app->response->format = Response::FORMAT_JSON;

            return [
                'normal' => $this->renderPartial('components/table', ['class' => 'table-trip-normal', 'tripList' => $tripList['normal']]),
                'late' => $this->renderPartial('components/table', ['class' => 'table-trip-late', 'tripList' => $tripList['late']]),
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('CallDriverController - actionReload() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionUpdateCallDriver()
    {
        $id = Yii::$app->request->post('id');
        $model = Trip::findOne($id);

        if ($model != null) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                Yii::$app->db->createCommand()
                    ->update('trip', ['call_driver' => CALL_DRIVER_CONFIRMED], ['id' => $id])
                    ->execute();

                $transaction->commit();

                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'trip_update_debt_driver', [
                        'id' => $model->id,
                        'debt_driver' => 'Đã gọi xác nhận nhận chuyến của',
                    ]),
                    'action' => 'update',
                ]);

                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
            }
        }

        return false;
    }
}
