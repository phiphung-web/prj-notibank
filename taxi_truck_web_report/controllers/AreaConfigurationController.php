<?php

namespace app\controllers;

use app\models\AreaConfiguration;
use app\models\AreaRelationship;
use Yii;

class AreaConfigurationController extends BaseController
{
    public function actionIndex()
    {
        $areaConfigurations = AreaConfiguration::find()->all();

        return $this->render('index', compact(['areaConfigurations']));
    }

    public function actionLoadAreaConfiguration($id = null)
    {
        if ($id !== null) {
            $areaConfig = AreaConfiguration::findOne($id);
            if ($areaConfig !== null) {
                return json_encode($areaConfig->attributes);
            }
        }

        return json_encode([]);
    }

    public function actionSaveAreaConfiguration()
    {
        $action_fuc = 'create';
        $postData = Yii::$app->request->post();
        if (isset($postData['AreaConfiguration']['id']) && ! empty($postData['AreaConfiguration']['id'])) {
            $areaConfiguration = AreaConfiguration::findOne($postData['AreaConfiguration']['id']);
            unset($postData['AreaConfiguration']['id']);
            $areaConfiguration->modified_on = date('Y-m-d H:i:s');
            $action_fuc = 'update';
        } else {
            $areaConfiguration = new AreaConfiguration();
            $areaConfiguration->created_on = date('Y-m-d H:i:s');
            $areaConfiguration->modified_on = date('Y-m-d H:i:s');
        }
        if ($areaConfiguration->load($postData) && $areaConfiguration->save()) {
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'area_configuration_action', [
                    'id' => $areaConfiguration->id,
                    'type' => TYPE_AREA_CONFIGURATION[$areaConfiguration->type],
                    'value' => $areaConfiguration->value,
                    'action' => ACTION_LIST[$action_fuc],
            ]),
                'action' => $action_fuc,
            ]);

            return true;
        }

        return false;
    }

    public function actionDeleteAreaConfiguration()
    {
        $getData = Yii::$app->request->get();
        $areaConfiguration = AreaConfiguration::findOne($getData['id']);

        if ($areaConfiguration && isset($getData['type'])) {
            $field = ($getData['type'] == SCHEDULE_AREA_CONFIGURATION) ? 'address' : 'time';
            $count = AreaRelationship::find()->where([$field => $getData['id']])->count();

            if ($count == 0) {
                $areaConfiguration->delete();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'area_configuration_action', [
                        'id' => $areaConfiguration->id,
                        'type' => TYPE_AREA_CONFIGURATION[$areaConfiguration->type],
                        'value' => $areaConfiguration->value,
                        'action' => ACTION_LIST['delete'],
                    ]),
                    'action' => 'delete',
                ]);
                Yii::$app->session->setFlash('success', 'Bản ghi đã được xóa thành công.');
            } else {
                Yii::$app->session->setFlash('error', 'Bản ghi tồn tại dữ liệu thuộc khu vực không thể xóa.');
            }
        }

        return $this->redirect(['index']);
    }
}
