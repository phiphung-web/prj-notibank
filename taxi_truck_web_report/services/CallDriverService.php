<?php

namespace app\services;

use app\models\Trip;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class CallDriverService
 *
 * This class handles the trip-related operations.
 */
class CallDriverService
{
    protected $systemConfiguration;
    public $sendMessageZnsService;


    public function __construct()
    {
        $this->systemConfiguration = new SystemConfigurationService();
        $this->sendMessageZnsService = new SendMessageZnsService();
    }

    public function getTripNeedCall($minute = 0, $params = [])
    {
        $startTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600);
        $endTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600 + $minute * 60);
        $query = Trip::find();
        $query->where([
            'trip.status' => [STATUS_TRIP_COMPLETE, STATUS_TRIP_DONE],
            'trip.call_driver' => CALL_DRIVER_NOT_CONFIRMED,
        ])
            ->joinWith([
                'tripReturnNoOrder AS tripReturn',
                'tripReturn.driver AS tripReturnDriver',
                'bid',
                'bid.driver.car',
                'agency',
                'tripGroup',
                'tripGroup.groupZalo',
                'tripGroup.groupZaloSeller',
            ])
            ->andWhere(['OR', ['trip_return.trip_id' => null], ['trip_return.trip_id' => new Expression('trip.id')]])
            ->andWhere(['OR', ['tripReturnDriver.id' => null], ['tripReturnDriver.id' => new Expression('trip_return.driver_id')]])
            ->andWhere(['bid.status' => STATUS_BID_SUCCESS])
            ->leftJoin('driver_sub', 'driver.id = driver_sub.driver_id AND trip.id = driver_sub.trip_id')
            ->select([
                'trip.*',
                'driver_sub_name' => 'driver_sub.name',
                'driver_sub_phone' => 'driver_sub.phone',
                'driver_sub_bks' => 'driver_sub.bks',
                'driver_sub_type' => 'driver_sub.type',
            ])
            ->andWhere(['OR', ['trip_group.id' => null], ['trip_group.id' => new Expression('trip.trip_group_id')]])
            ->andWhere(['>=', 'pickup_time', $startTime])
            ->andWhere(['<', 'pickup_time', $endTime])
            ->andWhere(['call_driver' => CALL_DRIVER_NOT_CONFIRMED]);

        // search param
        $keyword = ! empty($params['keyword']) ? $params['keyword'] : '';
        if (! empty($keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'pickup_address', '%' . $keyword . '%', false],
                ['LIKE', 'destination_address', '%' . $keyword . '%', false],
                ['LIKE', 'customer_name', '%' . $keyword . '%', false],
                ['LIKE', 'customer_phone', '%' . $keyword . '%', false],
            ]);
        }

        $order = ! empty($params['order']) ? $params['order'] : 'pickup_time DESC';
        if (! empty($order) && $order !== 'null') {
            $query->orderBy($order);
        }


        $tripList = $query->all();
        if (! empty($tripList)) {
            $tripLate = array_filter($tripList, function ($tripItem) {
                $pickupTime = strtotime($tripItem->pickup_time);
                $time = date('H:i', $pickupTime);

                return $time >= '23:00' || $time <= '06:00';
            });

            $tripNormal = array_filter($tripList, function ($tripItem) {
                $pickupTime = strtotime($tripItem->pickup_time);
                $time = date('H:i', $pickupTime);

                return $time < '23:00' && $time > '06:00';
            });

            return [
                'normal' => $tripNormal,
                'late' => $tripLate,
            ];
        }

        return ['normal' => [], 'late' => []];
    }

    public function getTripNormal($minute = 0)
    {
        $startTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600 - $minute * 60);
        $endTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600);
        $query = new Query();
        $condition = ['send_vip' => 0];
        $trips = $query->select(['trip.*', 'trip.sell_start_time as new_time'])
            ->from('trip')
            ->andWhere(['>=', 'sell_start_time', $startTime])
            ->andWhere(['<', 'sell_start_time', $endTime])
            ->andWhere(['trip.status' => 'OPEN'])
            ->andWhere($condition)
            ->all();

        return $trips;
    }
}
