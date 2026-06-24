<?php

namespace app\services;

use app\helpers\MyHelper;
use app\models\SystemConfiguration;
use app\repositories\revenue\RevenueRepository;
use yii\db\Query;

/**
 * Class RevenueService
 */
class RevenueService
{
    protected $urlService;
    protected $bookingService;
    protected $revenueRepository;

    public function __construct()
    {
        $this->urlService = new UrlService();
        $this->bookingService = new BookingService();
        $this->revenueRepository = new RevenueRepository();
    }

    /**
     * Initialize the total array with default values.
     *
     * @return array
     */
    public function initializeTotalArray(): array
    {
        return array_fill_keys([
            'totalBookingWaiting',
            'totalBookingCreate',
            'totalTripCreate',
            'totalTripCancel',
            'totalBookingCancel',
            'totalTripBookingComplete',
            'total',
            'total_close',
            'totalCustomerPrice',
            'totalDriverPrice',
            'totalReceive',
        ], 0);
    }

    /**
     * Initialize the total source array with default values.
     *
     * @return array
     */
    public function initializeTotalSourceArray(): array
    {
        return array_fill_keys(array_keys(SOURCE_TRIP_TYPE_LIST), [
            'total' => 0,
            'success' => 0,
        ]);
    }


    /**
     * Initialize the source mail array with default values.
     *
     * @return array
     */
    public function initializeMailSource(): array
    {
        return array_reduce(array_keys(SOURCE_MAIL_LIST), function ($arr, $key) {
            $arr["source_{$key}"] = 0;
            $arr["source_{$key}_success"] = 0;

            return $arr;
        }, []);
    }

    public function mailSourceClassification($dtDate, $totalTrip)
    {
        $bookings = $this->bookingService->getAllBookingBySourceAndCreatedOn($dtDate, SOURCE_TRIP_TYPE_MAIL_1);

        $total = [];
        foreach (SOURCE_MAIL_LIST as $key => $source) {
            $total["source_{$key}"] = 0;
            $total["source_{$key}_success"] = 0;
        }

        foreach ($bookings as $value) {
            $this->updateTotal($total, $value);
        }

        $nonEmployeeTotal = array_sum(array_intersect_key($total, array_flip(array_map(function ($key) {
            return "source_{$key}";
        }, array_keys(SOURCE_MAIL_LIST)))));

        $total['source_' . SOURCE_KEY_EMPLOYEE] = $totalTrip - $nonEmployeeTotal;
        $total['source_' . SOURCE_KEY_EMPLOYEE . '_success'] = $totalTrip - $nonEmployeeTotal;

        return $total;
    }

    /**
     * Calculate totals from the provided data models.
     *
     * @param array $models The array of data models.
     * @return array An associative array with total values.
     */
    public function calculateTotals(array $models): array
    {
        // Initialize variables for totals
        $totals = [
            'totalTrips' => 0,
            'totalCancelTrips' => 0,
            'totalCompleteTrips' => 0,
            'totalCustomerPrice' => 0,
            'totalDriverPrice' => 0,
            'totalMoneyDebtAgency' => 0,
            'totalSpendPrice' => 0,
            'totalProfit' => 0,
            'totalReceivePrice' => 0,
            'sourceTotals' => array_fill_keys(array_keys(SOURCE_TRIP_TYPE_LIST), 0),
            'sourceSuccessTotals' => array_fill_keys(array_keys(SOURCE_TRIP_TYPE_LIST), 0),
        ];

        foreach ($models as $model) {
            // Accumulate totals
            $totals['totalTrips'] += $model['totalTrips'];
            $totals['totalCancelTrips'] += $model['totalCancelTrips'];
            $totals['totalCompleteTrips'] += $model['totalCompleteTrips'];
            $totals['totalCustomerPrice'] += $model['customerPrice'];
            $totals['totalMoneyDebtAgency'] += $model['moneyDebtAgency'];
            $totals['totalDriverPrice'] += $model['driverPrice'];
            $totals['totalSpendPrice'] += $model['spend_price'];
            $totals['totalProfit'] += $model['customerPrice'] - $model['driverPrice'];
            $totals['totalReceivePrice'] += $model['customerPrice'] - $model['driverPrice'] - $model['spend_price'] - $model['moneyDebtAgency'];

            foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) {
                $totals['sourceTotals'][$key] += (int)$model['source_' . $key];
                $totals['sourceSuccessTotals'][$key] += (int)$model['source_' . $key . '_success'];
            }
        }

        return $totals;
    }

    public function convertListAgency($agencyList)
    {
        $list = [];

        foreach ($agencyList as $value) {
            $id = $value['id'];

            if (! isset($list[$id])) {
                $value['data'] = [];
                $list[$id] = $value;
            }

            $list[$id]['data'][] = $value;
        }

        return $list;
    }

    public function getTimeRanges()
    {
        $timeRanges = MyHelper::convertTimeRange(SystemConfiguration::findByKeyword('other_revenue_time'));
        $timeRangesHandle = [];
        if (isset($timeRanges) && is_array($timeRanges) && count($timeRanges)) {
            foreach ($timeRanges as $key => $value) {
                $timeRangesHandle[] = $key;
            }
        }

        return $timeRangesHandle;
    }

    public function createDateRange()
    {
        return date('Y-m-01') . ' - ' . date('Y-m-d');
    }

    public function getDataStatus($dateStart, $dateEnd)
    {
        $query = new Query();
        $result = $query->select([
            "SUM(IF(trip.status = 'CANCEL', 1, 0)) AS tripCancel",
            "SUM(IF(trip.status IN ('DONE', 'COMPLETE') AND (trip.trip_group_id = 0 OR trip.trip_group_id IS NULL), 1, 0)) AS tripSellApp",
            "SUM(IF(trip.status IN ('DONE', 'COMPLETE') AND trip.trip_group_id > 0, 1, 0)) AS tripSellZalo",
        ])
            ->from('trip')
            ->leftJoin('bid', 'trip.id = bid.trip_id AND bid.status = "SUCCESS"')
            ->where(['between', 'Date(trip.pickup_time)', $dateStart, $dateEnd])
            ->andWhere(['IN', 'trip.status', [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE, STATUS_TRIP_CANCEL]])
            ->one();

        return array_values($result);
    }

    public function getRevenueAndExpenditureData($dateStart, $dateEnd)
    {
        $query = new Query();
        $result = $query->select([
            'priceReceive' => '(SUM(IF((trip.status IN (:statusDone, :statusComplete)), trip.price_customer, 0)) - SUM(IF((bid.status = "SUCCESS"), bid.price, 0)))',
            'priceSpend' => '(SELECT SUM(spend_price) FROM summary_report WHERE Date(dt_date) BETWEEN :dateStart AND :dateEnd)',
        ])
            ->from('trip')
            ->innerJoin('bid', 'trip.id = bid.trip_id AND bid.status = "SUCCESS"')
            ->where(['between', 'Date(trip.pickup_time)', $dateStart, $dateEnd])
            ->andWhere(['IN', 'trip.status', [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE]])
            ->addParams([
                ':dateStart' => $dateStart,
                ':dateEnd' => $dateEnd,
                ':statusDone' => STATUS_TRIP_DONE,
                ':statusComplete' => STATUS_TRIP_COMPLETE,
            ])
            ->one();

        return array_values($result);
    }

    public function getDataSource($dateStart, $dateEnd)
    {
        $query = new Query();
        $select = [];
        foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) {
            $select[] = 'SUM(IF(trip.source_trip = ' . $key . ', 1, 0)) AS source_' . $key;
        }
        $result = $query->select($select)
            ->from('trip')
            ->innerJoin('bid', 'trip.id = bid.trip_id AND bid.status = "SUCCESS"')
            ->where(['between', 'Date(trip.pickup_time)', $dateStart, $dateEnd])
            ->andWhere(['IN', 'trip.status', [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE]])
            ->one();

        return array_values($result);
    }

    private function updateTotal(array &$total, array $value): void
    {
        if (! isset($value['url']) || $value['url'] === null) {
            return; // Exit if 'url' is not set or is null
        }

        if ($this->urlService->isFacebook($value['url'])) {
            $this->incrementTotal($total, SOURCE_KEY_FACEBOOK, ! empty($value['booking_id']));
        } elseif ($this->urlService->isTiktok($value['url'])) {
            $this->incrementTotal($total, SOURCE_KEY_TIKTOK, ! empty($value['booking_id']));
        } elseif ($this->urlService->isZalo($value['url'])) {
            $this->incrementTotal($total, SOURCE_KEY_ZALO, ! empty($value['booking_id']));
        } elseif ($this->urlService->isGoogle($value['url'])) {
            $this->incrementTotal($total, SOURCE_KEY_GOOGLE, ! empty($value['booking_id']));
        } elseif ($this->urlService->isOrganic($value['url'])) {
            $this->incrementTotal($total, SOURCE_KEY_ORGANIC, ! empty($value['booking_id']));
        }
    }

    private function incrementTotal(array &$total, string $key, bool $isSuccess): void
    {
        $total['source_' . $key]++;
        if ($isSuccess) {
            $total["source_{$key}_success"]++;
        }
    }

    public function getNewDriverFirstDeposit($params)
    {
        return $this->revenueRepository->getNewDriverFirstDeposit($params);
    }
}
