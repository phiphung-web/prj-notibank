<?php

namespace app\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class LogController extends BaseController
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

    /**
     * Lists all PayTransaction models.
     * @return mixed
     */
    public function actionIndex()
    {
        $logDir = Yii::getAlias('@app/log/user_logs');
        $dataLog = [];

        $params = Yii::$app->request->queryParams;
        $timezone = new \DateTimeZone('Asia/Ho_Chi_Minh');
        $currentDateTime = new \DateTime('now', $timezone);
        $created_on = ! empty($params['created_on']) ? $params['created_on'] : $currentDateTime->format('Y-m-d');
        $fileName = $created_on . '.json';
        $logFilePath = $logDir . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($logFilePath)) {
            $file = file_get_contents($logFilePath);
            $dataLog = json_decode($file, true);
        }

        $actions = ACTION_LIST;
        $usernames = ArrayHelper::map(\app\models\Admin::find()->all(), 'username', 'username');
        $searchData = compact('created_on', 'usernames', 'actions');
        // Load all foder log
        // if (file_exists($logDir)) {
        //     foreach (glob($logDir . '/*.*') as $filename) {
        //         $existingLogData = file_get_contents($filename);
        //         $existingLogs = json_decode($existingLogData, true);
        //         $dataLog = array_merge($dataLog, $existingLogs);
        //     }
        // }

        if (! empty($params['user_name']) && $params['user_name'] != null) {
            $dataLog = array_filter($dataLog, function ($dataLog_item) use ($params) {
                return ArrayHelper::getValue($dataLog_item, 'user_name') === $params['user_name'];
            });
        }

        if (! empty($params['action']) && $params['action'] != null) {
            $dataLog = array_filter($dataLog, function ($dataLog_item) use ($params) {
                return ArrayHelper::getValue($dataLog_item, 'action') === $params['action'];
            });
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $dataLog,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['user_id', 'created_on', 'user_name', 'action'],
            ],
        ]);

        return $this->render('index', compact('searchData', 'dataProvider'));
    }

    public function actionAgency()
    {
        $logDir = Yii::getAlias('@app/log/api_logs');
        $dataLog = [];
        $actions = ACTION_LIST;
        $params = Yii::$app->request->queryParams;
        $timezone = new \DateTimeZone('Asia/Ho_Chi_Minh');
        $currentDateTime = new \DateTime('now', $timezone);
        $created_on = ! empty($params['created_on']) ? $params['created_on'] : $currentDateTime->format('Y-m-d');
        $fileName = $created_on . '.json';
        $logFilePath = $logDir . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($logFilePath)) {
            $file = file_get_contents($logFilePath);
            $dataLog = json_decode($file, true);
        }

        $searchData = compact('created_on', 'actions');
        if (! empty($params['agency_id']) && $params['agency_id'] != null) {
            $dataLog = array_filter($dataLog, function ($dataLog_item) use ($params) {
                return ArrayHelper::getValue($dataLog_item, 'agency_id') == $params['agency_id'];
            });
        }

        if (! empty($params['action']) && $params['action'] != null) {
            $dataLog = array_filter($dataLog, function ($dataLog_item) use ($params) {
                return ArrayHelper::getValue($dataLog_item, 'action') === $params['action'];
            });
        }

        $dataProvider = new ArrayDataProvider([
            'allModels' => $dataLog,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => ['user_id', 'created_on', 'agency_id', 'action'],
            ],
        ]);

        return $this->render('agency', compact('searchData', 'dataProvider'));
    }
}
