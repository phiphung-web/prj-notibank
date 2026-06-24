<?php

namespace app\modules\cronjob\controllers;

use app\services\DriverService;
use yii\db\Query;
use yii\rest\ActiveController;

class DriverController extends ActiveController
{
    public $modelClass = 'app\models\Driver';
    public $driverService;

    public function init()
    {
        parent::init();
        $this->driverService = new DriverService();
    }

    public function actionUpdatePoint()
    {
        $connection = \Yii::$app->db;

        // Lấy điểm trung bình từ bảng customer_service cho mỗi tài xế
        $subQuery = (new Query())
            ->select(['AVG(IF(customer_service.point IS NULL, 10, customer_service.point))'])
            ->from('customer_service')
            ->where('customer_service.driver_id = driver.id');

        // Cập nhật bảng driver với điểm trung bình mới
        $command = $connection->createCommand()
            ->update('driver', ['point' => $subQuery])
            ->execute();

        pre($command);
    }
}
