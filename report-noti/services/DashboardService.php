<?php

namespace app\services;

use app\helpers\MyStringHelper;
use Yii;
use yii\db\Query;

/**
 * Class DashboardService
 *
 * This class handles the trip-related operations.
 */
class DashboardService
{
    public function actionGetStatisticDay()
    {
        $date = Yii::$app->request->post('date');
        $date = explode(' to ', $date);
        $startDate = date('Y-m-d 00:00:00', strtotime($date[0]));
        $endDate = date('Y-m-d 23:59:59', strtotime($date[1]));

        return json_encode($this->statisticMoneyTripByPeriod($startDate, $endDate));
    }

    /**
     * Calculate statistics for money trip by a specific period.
     * @param string|null $startDate The start date of the period.
     * @param string|null $endDate   The end date of the period.
     * @return array An array containing the calculated statistics.
     */
    public function statisticMoneyTripByPeriod($startDate, $endDate)
    {
        $total = $this->totalRevenueByPeriod($startDate, $endDate);
        $collected = $this->totalCollectedByPeriod($startDate, $endDate);
        $debt = $this->totalDebtByPeriod($startDate, $endDate);

        return [
            'total_customer_revenue' => (int)$total['customer'],
            'total_driver_revenue' => (int)$total['bid'],
            'total_profit' => (int)($total['customer'] - $total['bid']),
            'customers_collected' => (int)$collected['customer'],
            'drivers_collected' => (int)$collected['bid'],
            'profits_collected' => (int)$collected['customer'] - $collected['bid'],
            'customers_debt' => (int)$debt['customer'],
            'drivers_debt' => (int)$debt['bid'],
            'profits_debt' => (int)$debt['customer'] - $debt['bid'],
        ];
    }

    /**
     * Calculate statistics for the entire money trip period.
     * @return array An array containing the calculated statistics.
     */
    public function statisticMoneyTripFull()
    {
        return $this->statisticMoneyTripByPeriod(null, null);
    }

    /**
     * Calculate statistics for the money trip of a single day.
     * @return array An array containing the calculated statistics.
     */
    public function statisticMoneyTripDay()
    {
        $startDate = date('Y-m-d 00:00:00');
        $endDate = date('Y-m-d 23:59:59');

        return $this->statisticMoneyTripByPeriod($startDate, $endDate);
    }

    /**
     * Calculate statistics for the money trip of a week (from Monday to Sunday).
     * @return array An array containing the calculated statistics.
     */
    public function statisticMoneyTripWeek()
    {
        $startDate = date('Y-m-d H:i:s', strtotime('this week Monday'));
        $endDate = date('Y-m-d 23:59:59', strtotime('this week Sunday'));

        return $this->statisticMoneyTripByPeriod($startDate, $endDate);
    }

    /**
     * Calculate statistics for the money trip of a month.
     * @return array An array containing the calculated statistics.
     */
    public function statisticMoneyTripMonth()
    {
        $startDate = date('Y-m-01 00:00:00');
        $endDate = date('Y-m-t 23:59:59');

        return $this->statisticMoneyTripByPeriod($startDate, $endDate);
    }

    /**
     * Calculate the total revenue for a specific period.
     * @param string|null $startDate The start date of the period.
     * @param string|null $endDate   The end date of the period.
     * @return array An array containing the total revenue for customers and drivers.
     */
    public function totalRevenueByPeriod($startDate, $endDate)
    {
        $query = (new Query())
            ->select('SUM(price_customer) AS customer, SUM(B.price) AS bid')
            ->from('trip')
            ->where(['IN', 'trip.status', ['DONE', 'COMPLETE']])
            ->andFilterWhere(['>=', 'trip.pickup_time', $startDate])
            ->andFilterWhere(['<=', 'trip.pickup_time', $endDate])
            ->innerJoin('( SELECT `price`, `trip_id`, `status` FROM bid GROUP BY trip_id ) AS B', 'B.trip_id = trip.id AND B.status = "SUCCESS"');
        $result = $query->one();

        return $result;
    }

    /**
     * Calculate the total amount collected for a specific period.
     * @param string|null $startDate The start date of the period.
     * @param string|null $endDate   The end date of the period.
     * @return array An array containing the total amount collected from customers and drivers.
     */
    public function totalCollectedByPeriod($startDate, $endDate)
    {
        $query = (new Query())
            ->select('SUM(price_customer) AS customer, SUM(B.price) AS bid')
            ->from('trip')
            ->where(['collected_money' => 1, 'driver_debt' => 1])
            ->andWhere(['IN', 'trip.status', ['DONE', 'COMPLETE']])
            ->andFilterWhere(['>=', 'trip.pickup_time', $startDate])
            ->andFilterWhere(['<=', 'trip.pickup_time', $endDate])
            ->innerJoin('( SELECT `price`, `trip_id`, `status` FROM bid GROUP BY trip_id ) AS B', 'B.trip_id = trip.id AND B.status = "SUCCESS"');

        $result = $query->one();

        return $result;
    }

    /**
     * Calculate the total debt for a specific period.
     * @param string|null $startDate The start date of the period.
     * @param string|null $endDate   The end date of the period.
     * @return array An array containing the total debt from customers and drivers.
     */
    public function totalDebtByPeriod($startDate, $endDate)
    {
        $query = (new Query())
            ->select('SUM(price_customer) AS customer')
            ->addSelect('SUM(price_bid) AS bid')
            ->from('trip')
            ->innerJoin('( SELECT `price`, `trip_id`, `status` FROM bid GROUP BY trip_id ) AS B', 'B.trip_id = trip.id AND B.status = "SUCCESS"')
            ->where(['collected_money' => 0])
            ->orWhere(['driver_debt' => 0])
            ->andWhere(['IN', 'trip.status', ['DONE', 'COMPLETE']])
            ->andFilterWhere(['>=', 'trip.pickup_time', $startDate])
            ->andFilterWhere(['<=', 'trip.pickup_time', $endDate]);

        $result = $query->one();

        return [
            'customer' => $result['customer'],
            'bid' => $result['bid'],
        ];
    }

    /**
     * Render statistic table rows.
     *
     * @param array $statisticRevenueFull An associative array containing the revenue statistics.
     * @return string The HTML for the statistic table rows.
     */
    public function renderStatisticAll($statisticRevenueFull = [])
    {
        $html = '';
        if (isset($statisticRevenueFull) && is_array($statisticRevenueFull) && count($statisticRevenueFull)) {
            $statisticLabels = [
                'Tổng doanh thu' => [
                    'total_customer_revenue',
                    'total_driver_revenue',
                    'total_profit',
                ],
                'Đã thu' => [
                    'customers_collected',
                    'drivers_collected',
                    'profits_collected',
                ],
                'Nợ' => [
                    'customers_debt',
                    'drivers_debt',
                    'profits_debt',
                ],
            ];

            foreach ($statisticLabels as $label => $fields) {
                $html .= '<tr>';
                $html .= '<td>' . $label . '</td>';
                foreach ($fields as $field) {
                    $html .= '<td class="text-center">' . MyStringHelper::convertIntegerToPrice($statisticRevenueFull = [][$field]) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        return $html;
    }

    public function totalTripByPeriod($startDate, $endDate)
    {
        $query = (new Query())
            ->select(['
        count(trip.id) AS tong,
        SUM(CASE WHEN trip.status IN ("DONE", "COMPLETE") THEN 1 ELSE 0 END) AS thanh_cong,
        SUM(CASE WHEN trip.status IN ("CANCEL",  "OPEN",  "CREATE",  "EXPIRE") THEN 1 ELSE 0 END) AS that_bai,
        SUM(CASE WHEN source_trip.type = 6 THEN 1 ELSE 0 END) AS nguon_mail,
        SUM(CASE WHEN source_trip.type = 7 THEN 1 ELSE 0 END) AS nguon_call,
        SUM(CASE WHEN source_trip.type = 4 THEN 1 ELSE 0 END) AS nguon_zalo,
        SUM(CASE WHEN source_trip.type = 2 THEN 1 ELSE 0 END) AS nguon_fb,
        SUM(CASE WHEN source_trip.type = 5 THEN 1 ELSE 0 END) AS nguon_dai_ly,
        SUM(CASE WHEN source_trip.type = 8 THEN 1 ELSE 0 END) AS nguon_khach_quay_dau,
        SUM(CASE WHEN source_trip.type = 9 THEN 1 ELSE 0 END) AS nguon_khach_quay_dau_cskh
        '])
            ->from('trip')
            ->where(['NOT IN', 'trip.status', ['PENDING', 'EXPIRE']])
            ->andFilterWhere(['>=', 'trip.pickup_time', $startDate])
            ->andFilterWhere(['<=', 'trip.pickup_time', $endDate])
            ->innerJoin('source_trip', 'trip.source = source_trip.id');

        return $query->one();
    }
}
