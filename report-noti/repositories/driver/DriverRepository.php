<?php

namespace app\repositories\driver;

use app\models\Driver;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use DateTime;

class DriverRepository
{
    public function getDriverByUsername(string $username = ''): ?Driver
    {
        if (empty($username)) {
            return null;
        }

        $driver = Driver::find()
            ->where(['username' => $username])
            ->andWhere(['is_sub_driver' => DRIVER_TYPE_NORMAL])
            ->one();

        return $driver;
    }

    public function getDriverManyTrips($params = []): ActiveDataProvider
    {
        $startDate = (isset($params['createTimeStart']) ? date('Y-m-d 00:00:00', strtotime($params['createTimeStart'])) : date('Y-m-01 00:00:00'));
        $endDate = (isset($params['createTimeEnd']) ? date('Y-m-d 23:59:59', strtotime($params['createTimeEnd'])) : date('Y-m-d 23:59:59'));

        $query = new Query();
        $query
            ->from('trip')
            ->select(['driver.*', 'COUNT(trip.id) AS trip_count', 'SUM(trip.price_customer - bid.price) AS trip_price, bid.driver_id'])
            ->innerJoin('bid', 'bid.trip_id = trip.id AND bid.status = "SUCCESS"')
            ->innerJoin('driver', 'driver.id = bid.driver_id')
            ->andWhere(['between', 'trip.created_on', $startDate, $endDate])
            ->groupBy('driver_id')
            ->orderBy(['trip_count' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

    public function getDriverNoTrips($params = []): ActiveDataProvider
    {
        $startDate = (isset($params['createTimeStart']) ? date('Y-m-d 00:00:00', strtotime($params['createTimeStart'])) : date('Y-m-01 00:00:00'));
        $endDate = (isset($params['createTimeEnd']) ? date('Y-m-d 23:59:59', strtotime($params['createTimeEnd'])) : date('Y-m-d 23:59:59'));

        $subQuery = (new \yii\db\Query())
            ->select('driver_id')
            ->from('trip')
            ->innerJoin('bid', 'bid.trip_id = trip.id AND bid.status = "SUCCESS"')
            ->where(['between', 'trip.created_on', $startDate, $endDate])
            ->groupBy('driver_id');

        $query = (new \yii\db\Query())
            ->from('driver')
            ->select('driver.*')
            ->where(['NOT IN', 'driver.id', $subQuery])
            ->orderBy(['driver.id' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

    public function getDriverNotActive($params = []): ActiveDataProvider
    {
        $startDate = (isset($params['createTimeStart']) ? date('Y-m-d 00:00:00', strtotime($params['createTimeStart'])) : date('Y-m-01 00:00:00'));
        $endDate = (isset($params['createTimeEnd']) ? date('Y-m-d 23:59:59', strtotime($params['createTimeEnd'])) : date('Y-m-d 23:59:59'));

        $subQuery = (new \yii\db\Query())
            ->select('driver_id')
            ->from('trip')
            ->innerJoin('bid', 'bid.trip_id = trip.id AND bid.status = "SUCCESS"')
            ->groupBy('driver_id');

        $query = (new \yii\db\Query())
            ->from('driver')
            ->select('driver.*')
            ->where(['NOT IN', 'driver.id', $subQuery])
            ->where(['between', 'driver.created_on', $startDate, $endDate])
            ->orderBy(['driver.id' => SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

    public function getDriverTransactionHistory($params = []): ActiveDataProvider
    {
        $createTimeStart = $params['createTimeStart'] ?? null;
        $createTimeEnd = $params['createTimeEnd'] ?? null;

        if ($createTimeStart) {
            $createTimeStart = (new DateTime($createTimeStart))->format('Y-m-d 00:00:00');
        }

        if ($createTimeEnd) {
            $createTimeEnd = (new DateTime($createTimeEnd))->format('Y-m-d 23:59:59');
        }

        $driverId = $params['driverId'] ?? null;

        $subQuery1 = (new Query())
            ->select([
                'driver_id' => 'bid.driver_id',
                'price_bid' => 'bid.price',
                'money_before' => 'bid.money_before',
                'money_after' => 'bid.money_after',
                'created_on' => 'bid.created_on',
                new Expression("'bid' AS source"),
                'customer_name' => 'trip.customer_name',
                'customer_phone' => 'trip.customer_phone',
                'pickup_address' => 'trip.pickup_address',
                'destination_address' => 'trip.destination_address',
                'pickup_time' => 'trip.pickup_time',
                'round_trip' => 'trip.round_trip',
                'is_have_bill' => 'trip.is_have_bill',
                'is_collect_money' => 'trip.is_collect_money',
                'description' => 'trip.description',
                'type_of_car' => 'trip.type_of_car',
                'price_customer' => 'trip.price_customer',
                'sell_start_time' => 'trip.sell_start_time',
                'status' => 'trip.status',
                'source_trip' => 'trip.source_trip',
                'driver_id_created' => 'trip.driver_id_created',
            ])
            ->from('bid')
            ->innerJoin('trip', 'trip.id = bid.trip_id')
            ->where(['bid.status' => ['SUCCESS', 'REFUND']]);

        if (!empty($createTimeStart) && !empty($createTimeEnd)) {
            $subQuery1->andWhere(['between', 'bid.created_on', $createTimeStart, $createTimeEnd]);
        }

        $subQuery2 = (new Query())
            ->select([
                'driver_id' => 'pay_transaction.driver_id',
                new Expression('NULL AS price'),
                'money_before' => 'pay_transaction.money_before',
                'money_after' => 'pay_transaction.money_after',
                'created_on' => 'pay_transaction.created_on',
                new Expression("'pay_transaction' AS source"),
                new Expression('NULL AS customer_name'),
                new Expression('NULL AS customer_phone'),
                new Expression('NULL AS pickup_address'),
                new Expression('NULL AS destination_address'),
                new Expression('NULL AS pickup_time'),
                new Expression('NULL AS round_trip'),
                new Expression('NULL AS is_have_bill'),
                new Expression('NULL AS is_collect_money'),
                new Expression('NULL AS description'),
                new Expression('NULL AS type_of_car'),
                new Expression('NULL AS price_customer'),
                new Expression('NULL AS sell_start_time'),
                new Expression('NULL AS status'),
                new Expression('NULL AS source_trip'),
                new Expression('NULL AS driver_id_created'),
            ])
            ->from('pay_transaction');

        if (!empty($createTimeStart) && !empty($createTimeEnd)) {
            $subQuery2->andWhere(['between', 'pay_transaction.created_on', $createTimeStart, $createTimeEnd]);
        }

        $subQuery3 = (new Query())
            ->select([
                'driver_id' => 'trip_return.driver_id',
                new Expression('NULL AS price'),
                'money_before' => 'trip_return.money_before',
                'money_after' => 'trip_return.money_after',
                'created_on' => 'trip_return.created_on',
                new Expression("'trip_return' AS source"),
                'customer_name' => 'trip.customer_name',
                'customer_phone' => 'trip.customer_phone',
                'pickup_address' => 'trip.pickup_address',
                'destination_address' => 'trip.destination_address',
                'pickup_time' => 'trip.pickup_time',
                'round_trip' => 'trip.round_trip',
                'is_have_bill' => 'trip.is_have_bill',
                'is_collect_money' => 'trip.is_collect_money',
                'description' => 'trip.description',
                'type_of_car' => 'trip.type_of_car',
                'price_customer' => 'trip.price_customer',
                'sell_start_time' => 'trip.sell_start_time',
                'status' => 'trip.status',
                'source_trip' => 'trip.source_trip',
                'driver_id_created' => 'trip.driver_id_created',
            ])
            ->from('trip_return')
            ->innerJoin('bid', 'trip_return.bid_id = bid.id AND bid.status = "REFUND"')
            ->innerJoin('trip', 'trip.id = bid.trip_id')
            ->where(['trip_return.refund' => 1]);

        if (!empty($createTimeStart) && !empty($createTimeEnd)) {
            $subQuery3->andWhere(['between', 'trip_return.created_on', $createTimeStart, $createTimeEnd]);
        }

        $unionQuery = $subQuery1->union($subQuery2)->union($subQuery3);

        $query = (new Query())
            ->select([
                'driver.display_name',
                'driver.username',
                'source_data.*',
            ])
            ->from('driver')
            ->leftJoin(['source_data' => $unionQuery], 'source_data.driver_id = driver.id')
            ->orderBy('created_on DESC');

        $query->andWhere(['driver.username' => isset($params['username']) ? $params['username'] : 0]);

        if (!empty($createTimeStart) && !empty($createTimeEnd)) {
            $query->andWhere(['between', 'source_data.created_on', $createTimeStart, $createTimeEnd]);
        }

        return new ActiveDataProvider([
            'query' => isset($params['username']) && !empty($params['username']) ? $query : Driver::find()->where('0=1'),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
    }
}
