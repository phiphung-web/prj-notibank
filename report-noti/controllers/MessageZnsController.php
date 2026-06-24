<?php

namespace app\controllers;

use app\models\MessageZns;
use app\models\SystemConfiguration;
use yii\filters\AccessControl;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageZnsController extends BaseController
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
        ];
    }
    /**
     * Lists MessageZns models has tripid = $tripid.
     * @param int $tripid
     * @return json
     */
    public function actionGetMessage($tripid)
    {
        $MessZns = MessageZns::find()->where(['trip_id' => $tripid])->groupBy('id, template_id')->orderBy('template_id DESC, id DESC')->all();
        $data = [];
        if ($MessZns !== null) {
            foreach ($MessZns as $key => $value) {
                $data[] = $value->attributes;
            }
        }

        return json_encode($data);
    }
    /**
     * Get key Zalo Message.
     * @return json
     */
    public function actionGetKeyZalo()
    {
        $system = SystemConfiguration::find()->asArray()->all();
        $data = [];
        foreach ($system as $key => $value) {
            if (strpos($value['keyword'], 'template') !== false && $value['content'] != '') {
                $data[$value['keyword']] = $value['content'];
            }
        }

        return json_encode($data);
    }
}
