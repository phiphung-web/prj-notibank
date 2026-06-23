<?php

namespace app\modules\cronjob\controllers;

use app\models\SummaryReport;
use app\services\RevenueService;
use yii\rest\ActiveController;
use Yii;

class SummaryReportController extends ActiveController
{
    public $modelClass = 'app\models\SummaryReport';
    public $revenueService;

    public function init()
    {
        parent::init();
        $this->revenueService = new RevenueService();
    }

    public function actionGenerate()
    {
        $data = [];
        // Thêm mới toàn bộ
        // $fromDate = $this->getDefaultDate(null);
        $fromDate = $this->getDefaultDate(gmdate('Y-m-d 00:00:00', strtotime('-7 days', time())));
        $toDate = gmdate('Y-m-d 23:59:59', time());
        $statisticBooking = $this->getStatisticBooking($fromDate, $toDate);
        $statisticTrip = $this->getStatisticTrip($fromDate, $toDate);
        $data = $this->filterSummaryReport($statisticBooking, $statisticTrip);
        foreach ($data as $row) {
            $summaryReport = SummaryReport::findOne(['dt_date' => $row['dt_date']]);

            if ($summaryReport === null) {
                // Nếu không tìm thấy bản ghi nào, chèn mới
                $summaryReport = new SummaryReport($row);
            } else {
                // Nếu tìm thấy, cập nhật bản ghi
                $summaryReport->attributes = $row;
            }

            $summaryReport->save();
        }

        pre($data);
    }

    private function filterSummaryReport($statisticBooking, $statisticTrip)
    {
        $mergedArray = [];

        foreach ($statisticTrip as $item) {
            $dtDate = $item['dt_date'];
            $item['source_trip'] = [];

            foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) {
                $item['source_trip'][$key] = [
                    'total' => (isset($item['source_trip_' . $key . '_total']) ? $item['source_trip_' . $key . '_total'] : 0),
                    'success' => (isset($item['source_trip_' . $key . '_success']) ? $item['source_trip_' . $key . '_success'] : 0),
                ];

                if ($key == SOURCE_TRIP_TYPE_MAIL_1) {
                    $item['source_trip'][$key]['employee'] = (isset($item['source_trip_' . $key . '_employee']) ? $item['source_trip_' . $key . '_employee'] : 0);
                    unset($item['source_trip_' . $key . '_employee']);
                }

                if (isset($item['source_trip_' . $key . '_total']))
                    unset($item['source_trip_' . $key . '_total']);
                if (isset($item['source_trip_' . $key . '_success']))
                    unset($item['source_trip_' . $key . '_success']);
            }
            $item['source_trip'] = json_encode($item['source_trip']);
            $mergedArray[$dtDate] = $item;
        }

        foreach ($statisticBooking as $item) {
            $dtDate = $item['dt_date'];
            if (isset($mergedArray[$dtDate]))
                $mergedArray[$dtDate]['source_trip'] = json_decode($mergedArray[$dtDate]['source_trip'], true);
            foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) {
                if (isset($mergedArray[$dtDate]))
                    $mergedArray[$dtDate]['source_trip'][$key]['total'] += (int) $item['source_trip_' . $key . '_total'];
                unset($item['source_trip_' . $key . '_total']);
                if ($key == SOURCE_TRIP_TYPE_MAIL_1 && isset($mergedArray[$dtDate]))
                    $mergedArray[$dtDate]['source_trip'][$key]['data'] = $this->revenueService->mailSourceClassification($dtDate, $mergedArray[$dtDate]['source_trip'][$key]['total']);
            }
            if (isset($mergedArray[$dtDate])) {
                $mergedArray[$dtDate] = array_merge($mergedArray[$dtDate], $item);
            } else {
                $mergedArray[$dtDate] = $item;
            }
            if (isset($mergedArray[$dtDate]['source_trip'])) {
                $mergedArray[$dtDate]['source_trip'] = json_encode($mergedArray[$dtDate]['source_trip']);
            }
        }
        return array_values($mergedArray);
    }

    private function getDefaultDate($date)
    {
        return $date === null || $date === '0000-00-00' ? '2023-01-01' : $date;
    }

    private function getStatisticBooking($fromDate, $toDate)
    {
        $selectTripTypes = '';
        foreach (SOURCE_TRIP_TYPE_LIST as $key => $type) {
            $columnName = 'source_trip_' . $key . '_total';
            $selectTripTypes .= ", SUM(IF(b.type = {$key}, 1, 0)) AS {$columnName}";
        }

        $sql = "
            WITH RECURSIVE date_range AS (
                SELECT DATE(:fromDate) AS dt_date
                UNION ALL
                SELECT DATE_ADD(dt_date, INTERVAL 1 DAY)
                FROM date_range
                WHERE dt_date < :toDate
            )
            SELECT
                d.dt_date,
                COUNT(b.id) AS total_booking,
                SUM(IF(b.status = 'REJECT', 1, 0)) AS total_booking_cancel,
                SUM(IF(b.status = 'WAITING', 1, 0)) AS total_booking_waiting,
                SUM(IF(b.status = 'CREATE', 1, 0)) AS total_booking_create,
                SUM(IF(b.status = 'CONFIRM', 1, 0)) AS total_booking_confirm
                {$selectTripTypes}
            FROM date_range d
            LEFT JOIN booking b
                ON DATE(b.created_on) = d.dt_date
                AND (b.type_reject != 1 OR b.type_reject IS NULL)
            GROUP BY d.dt_date
            ORDER BY d.dt_date ASC
        ";

        return Yii::$app->db->createCommand($sql, [
            ':fromDate' => $fromDate,
            ':toDate' => $toDate,
        ])->queryAll();
    }

    private function getStatisticTrip($fromDate, $toDate)
    {
        // Tạo SELECT các cột
        $selectColumns = "
            d.dt_date,
            SUM(IF(trip.STATUS <> 'PENDING', 1, 0)) AS total_trip,
            SUM(IF(trip.STATUS = 'CANCEL', 1, 0)) AS total_trip_cancel,
            SUM(IF(trip.STATUS = 'COMPLETE', 1, 0)) AS total_trip_complete,
            SUM(IF(trip.STATUS = 'DONE', 1, 0)) AS total_trip_done,
            SUM(IF(trip.STATUS IN ('OPEN', 'CREATE', 'EXPIRE'), 1, 0)) AS total_trip_create,
            SUM(IF(trip.STATUS = 'PENDING', 1, 0)) AS total_trip_pending,
            SUM(IF(trip.STATUS IN ('DONE', 'COMPLETE'), trip.price_customer, 0)) AS customer_price,
            SUM(IF(trip.STATUS IN ('DONE', 'COMPLETE'), B.price, 0)) AS driver_price,
            SUM(IF(trip.STATUS IN ('DONE', 'COMPLETE'), (trip.price_customer - B.price), 0)) AS revenue
        ";

        // Thêm các cột động từ SOURCE_TRIP_TYPE_LIST
        foreach (SOURCE_TRIP_TYPE_LIST as $key => $type) {
            $selectColumns .= ",
                SUM(IF(trip.source_trip = {$key} AND trip.booking_id = 0, 1, 0)) AS source_trip_{$key}_total,
                SUM(IF(trip.source_trip = {$key} AND trip.status IN ('DONE', 'COMPLETE', 'OPEN', 'EXPIRE'), 1, 0)) AS source_trip_{$key}_success";
            if ($key == SOURCE_TRIP_TYPE_MAIL_1) {
                $selectColumns .= ",
                    SUM(IF(trip.source_trip = {$key} AND trip.booking_id = 0 AND trip.status IN ('DONE', 'COMPLETE', 'OPEN'), 1, 0)) AS source_trip_{$key}_employee";
            }
        }

        // Raw SQL kết hợp WITH RECURSIVE để tạo dải ngày
        $sql = "
            WITH RECURSIVE date_range AS (
                SELECT DATE(:fromDate) AS dt_date
                UNION ALL
                SELECT DATE_ADD(dt_date, INTERVAL 1 DAY)
                FROM date_range
                WHERE dt_date < :toDate
            )
            SELECT
                {$selectColumns}
            FROM date_range d
            LEFT JOIN trip ON DATE(trip.created_on) = d.dt_date
                AND trip.status NOT IN ('PENDING')
                AND trip.id NOT IN (
                    SELECT id FROM summary_report WHERE DATE(summary_report.dt_date) = d.dt_date
                )
            LEFT JOIN (
                SELECT trip_id, MAX(id) AS latest_bid_id
                FROM bid
                GROUP BY trip_id
            ) AS latest_bid ON trip.id = latest_bid.trip_id
            LEFT JOIN bid B ON B.id = latest_bid.latest_bid_id
            GROUP BY d.dt_date
            ORDER BY d.dt_date ASC
        ";

        return Yii::$app->db->createCommand($sql, [
            ':fromDate' => $fromDate,
            ':toDate' => $toDate,
        ])->queryAll();
    }
}
