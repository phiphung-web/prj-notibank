<?php

namespace app\repositories\revenue;

use yii\data\ActiveDataProvider;

class RevenueRepository
{
    public function getNewDriverFirstDeposit($params = []): ActiveDataProvider
    {
        $startDate = (isset($params['createTimeStart']) ? date('Y-m-d 00:00:00', strtotime($params['createTimeStart'])) : date('Y-m-01 00:00:00'));
        $endDate = (isset($params['createTimeEnd']) ? date('Y-m-d 23:59:59', strtotime($params['createTimeEnd'])) : date('Y-m-d 23:59:59'));

        $query = (new \yii\db\Query())
            ->from('driver')
            ->select([
                'driver.id',
                'driver.display_name',
                'driver.username',
                'driver.accepted_on',
                'driver.status',
                'admin.username as admin_name',
                'pay_transaction.money',
                'driver.driver_ban',
                'driver.parent_id',
                'car.bks',
                'car.color',
                'car.type',
                'car.album_registration_certificate',
                'car.registration_certificate_front',
                'car.registration_certificate_behind',
                'car.type_of_car',
                'car.car_year',
                'car.note',
                'car.car_type',
            ])
            ->innerJoin('car', 'car.id = driver.car_id')
            ->innerJoin('admin', 'admin.id = driver.admin_id_accepted')
            ->innerJoin('pay_transaction', 'pay_transaction.id = (
            SELECT MIN(pt.id)
            FROM pay_transaction pt
            WHERE pt.driver_id = driver.id
        )')
            ->andWhere(['driver.is_sub_driver' => DRIVER_TYPE_NORMAL])
            ->where(['between', 'accepted_on', $startDate, $endDate])
            ->andWhere(['driver.admin_id_accepted' => isset($params['admin_id_accepted']) ? $params['admin_id_accepted'] : 0])
            ->orderBy(['driver.accepted_on' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }
}
