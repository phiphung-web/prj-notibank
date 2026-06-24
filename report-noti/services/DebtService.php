<?php

namespace app\services;

use app\helpers\MyStringHelper;
use Yii;
use yii\base\Component;
use yii\db\Query;

class DebtService extends Component
{
    public function updateTrip($tripId)
    {
        Yii::$app->db->createCommand()
            ->update('trip', ['agency_debt' => 1], ['id' => $tripId])
            ->execute();
    }

    public function queryAgencyData($agencyId)
    {
        $query = new Query();
        $result = $query->select([
            'agency.*',
            'SUM(
                IF(
                    trip.money_debt_agency <> 0,
                    trip.money_debt_agency,
                    IF(
                        (trip.price_customer - bid.price) > (trip.price_customer / 100) * agency.percent,
                        (trip.price_customer / 100) * agency.percent ,
                        IF(
                            (trip.price_customer - bid.price) > agency.price,
                            agency.price,
                            0
                        )
                    )
                )
            ) AS `total_price_rose` ',
            'SUM(trip.price_customer) AS `total_price`',
        ])
            ->from('agency')
            ->innerJoin('trip', 'trip.agency_id = agency.id')
            ->innerJoin('bid', 'trip.id = bid.trip_id and bid.status = "' . STATUS_BID_SUCCESS . '"')
            ->where([
                'trip.agency_debt' => 0,
                'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
                'agency.id' => $agencyId,
            ])
            ->groupBy('agency.id')
            ->createCommand()
            ->queryOne();

        $result['total_price_rose'] = isset($result['total_price_rose']) ? MyStringHelper::convertIntegerToPrice($result['total_price_rose']) : 0;
        $result['total_price'] = isset($result['total_price']) ? MyStringHelper::convertIntegerToPrice($result['total_price']) : 0;

        return $result;
    }
}
