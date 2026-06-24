<?php

namespace app\models;

use app\helpers\MyHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;

class Revenue extends Model
{
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;

    public function rules()
    {
        return [
            [['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => DateRangeBehavior::className(),
                'attribute' => 'createTimeRange',
                'dateStartAttribute' => 'createTimeStart',
                'dateEndAttribute' => 'createTimeEnd',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'createTimeRange' => 'Khoảng thời gian',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function searchRev($params)
    {
        $query = new Query();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $selectTripTypes = '';
        foreach (SOURCE_TRIP_TYPE_LIST as $key => $type) {
            $selectTripTypes .= ",
				SUM(IF(A.source_trip = {$key}, 1, 0)) AS source_{$key},
				SUM(IF(A.source_trip = {$key} AND A.status IN ('DONE', 'COMPLETE'), 1, 0)) AS source_{$key}_success";
        }

        $sql = "
			WITH RECURSIVE date_range AS (
				SELECT DATE(:fromDate) AS dtDate
				UNION ALL
				SELECT DATE_ADD(dtDate, INTERVAL 1 DAY)
				FROM date_range
				WHERE dtDate < DATE(:toDate)
			),
			latest_bids AS (
				SELECT * FROM bid WHERE status = 'SUCCESS' GROUP BY trip_id
			)
			SELECT
				d.dtDate,
				SUM(IF((A.status IN ('DONE', 'COMPLETE')), A.price_customer, 0)) AS customerPrice,
				SUM(IF((A.status IN ('DONE', 'COMPLETE')), B.price, 0)) AS driverPrice,
				SUM(IF((A.status IN ('DONE', 'COMPLETE')), 1, 0)) AS totalCompleteTrips,
				SUM(IF((A.status IN ('DONE', 'COMPLETE')), A.money_debt_agency, 0)) AS moneyDebtAgency,
				SUM(IF((A.status IN ('DONE', 'COMPLETE')), A.money_customer_deposit, 0)) AS moneyCustomerDeposit,
				SUM(IF(A.status <> 'PENDING', 1, 0)) AS totalTrips,
				SUM(IF(A.status = 'CANCEL', 1, 0)) AS totalCancelTrips,
				(SELECT spend_price FROM summary_report WHERE summary_report.dt_date = d.dtDate) AS spend_price
				{$selectTripTypes}
			FROM date_range d
			LEFT JOIN trip A ON DATE(A.pickup_time) = d.dtDate
			LEFT JOIN ( SELECT * FROM bid WHERE bid.status = 'SUCCESS' GROUP BY trip_id ) B ON A.id = B.trip_id
			GROUP BY d.dtDate
			ORDER BY d.dtDate DESC
		";
        $results = Yii::$app->db->createCommand($sql, [
            ':fromDate' => date('Y-m-d 00:00:00', $this->createTimeStart),
            ':toDate' => date('Y-m-d 23:59:59', $this->createTimeEnd),
        ])->queryAll();

        return new ArrayDataProvider([
            'allModels' => $results,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'attributes' => ['dtDate'], // nếu muốn sort theo cột nào
            ],
        ]);
    }

    public function searchDetailByTime($params = null)
    {
        $timeRanges = MyHelper::convertTimeRange(SystemConfiguration::findByKeyword('other_revenue_time'));
        $date = $params['date'];

        $totalRevenues = [
            'new' => array_fill_keys(array_map(fn($range) => date('H:i', strtotime($range['start'])) . '-' . date('H:i', strtotime($range['end'])), $timeRanges), 0),
            'old' => array_fill_keys(array_map(fn($range) => date('H:i', strtotime($range['start'])) . '-' . date('H:i', strtotime($range['end'])), $timeRanges), 0),
            'agency' => array_fill_keys(array_map(fn($range) => date('H:i', strtotime($range['start'])) . '-' . date('H:i', strtotime($range['end'])), $timeRanges), 0),
        ];
        $query = new Query();


        // Lấy dữ liệu doanh thu của các chuyến xe trong ngày từ cơ sở dữ liệu
        $date_start = $date . ' 00:00:00';
        $date_end = $date . ' 23:59:59';

        $trips = $query->select([
            't.price_customer',
            't.pickup_time',
            't.customer_phone',
            't.source_trip',
            'b.price',
            '(t.price_customer - b.price) as profit',
            '(SELECT COUNT(*) FROM trip AS t2 INNER JOIN bid  AS b2 ON t2.id = b2.trip_id AND b2.status = "SUCCESS" WHERE t2.customer_phone = t.customer_phone AND t2.pickup_time < t.pickup_time AND t2.status IN ("DONE", "COMPLETE")) AS previous_trip_count',
        ])
            ->from(['t' => '(SELECT trip.id, trip.price_customer, trip.pickup_time, trip.customer_phone, trip.source_trip FROM trip INNER JOIN bid ON trip.id = bid.trip_id AND bid.status = "SUCCESS" WHERE trip.status IN ("DONE", "COMPLETE") AND trip.pickup_time BETWEEN :date_start AND :date_end)'])
            ->innerJoin('bid b', 't.id = b.trip_id AND b.status = :status_bid_success')
            ->where(['between', 't.pickup_time', $date_start, $date_end])
            ->addParams([
                ':date_start' => $date_start,
                ':date_end' => $date_end,
                ':status_bid_success' => STATUS_BID_SUCCESS,
            ])
            ->groupBy('t.id')
            ->all();
        $total = 0;
        // Chia dữ liệu doanh thu theo khung giờ
        foreach ($trips as $trip) {
            $tripStartTime = date('H:i:s', strtotime($trip['pickup_time']));
            foreach ($timeRanges as $key => $range) {
                if ($tripStartTime >= $range['start'] && $tripStartTime <= $range['end']) {
                    $total += $trip['profit'];
                    $keyToUpdate = ($trip['source_trip'] == SOURCE_TRIP_TYPE_AGENCY ? 'agency' : ($trip['previous_trip_count'] > 0 ? 'old' : 'new'));
                    $totalRevenues[$keyToUpdate][$key] += $trip['profit'];

                    break;
                }
            }
        }

        $totalRevenues['new'] = array_values($totalRevenues['new']);
        $totalRevenues['agency'] = array_values($totalRevenues['agency']);
        $totalRevenues['old'] = array_values($totalRevenues['old']);
        $totalRevenues['total'] = $total;

        return $totalRevenues;
    }

    public function searchPay($params)
    {
        $query = new Query();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->select([
            'Date(created_on) as dtDate',
            'sum(if(type_bank = 1, money, 0)) as sum',
            'sum(if(type_bank = 1, 1, 0)) as count',
            'sum(if(type_bank = 2, money, 0)) as sum_customer',
            'sum(if(type_bank = 2, 1, 0)) as count_customer',
        ])
            ->from('pay_transaction')
            ->andFilterWhere(['>=', 'Date(created_on)', date('Y-m-d 00:00:00', $this->createTimeStart)])
            ->andFilterWhere(['<=', 'Date(created_on)', date('Y-m-d 23:59:59', $this->createTimeEnd)])
            ->andFilterWhere(['is_disabled' => 0])
            ->groupBy(['dtDate'])
            ->orderBy(['dtDate' => SORT_DESC]);

        return $dataProvider;
    }

    public function searchUserStatistic($params = null, $userId = null)
    {
        $query = new Query();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $fromDate = date('Y-m-d 00:00:00', $this->createTimeStart);
        $toDate = date('Y-m-d 23:59:59', $this->createTimeEnd);

        $query->select([
            'a.username',
            'countTrip' => 'COUNT(t.id)',
            'totalTrip' => "(SELECT COUNT(*) FROM trip t_all WHERE t_all.status <> 'PENDING' AND t_all.userid_created = a.id AND DATE(t_all.pickup_time) BETWEEN '{$fromDate}' AND '{$toDate}')",
            'revenue' => 'SUM(t.price_customer - b.price)',
            'money_bonus' => 'COUNT(t.id) * a.bonus',
        ])
            ->from('admin a')
            ->leftJoin('trip t', [
                'AND',
                'a.id = t.userid_created',
                ['BETWEEN', 'DATE(t.pickup_time)', $fromDate, $toDate],
                ['IN', 't.status', [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE]],
            ])
            ->innerJoin('bid b', [
                'AND',
                't.id = b.trip_id',
                "b.status LIKE '" . STATUS_BID_SUCCESS . "'"
            ])
            ->groupBy(['a.username'])
            ->orderBy(['countTrip' => SORT_DESC]);
        if ($userId !== null) {
            $query->andWhere(['a.id' => $userId]);
        }

        return $dataProvider;
    }

    public function searchRevBooking($params = null)
    {
        $query = new Query();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // $query->from('booking')
        //   ->andFilterWhere(['>=', 'Date(created_on)', date('Y-m-d 00:00:00', $this->createTimeStart)])
        //   ->andFilterWhere(['<=', 'Date(created_on)', date('Y-m-d 23:59:59', $this->createTimeEnd)])
        //   ->orderBy(['Date(created_on)' => SORT_DESC]);

        // $dataProviderModels = $dataProvider->getModels();

        // return $this->getDataRevenueBooking($dataProviderModels);
        $query->from('summary_report')
            ->andFilterWhere(['>=', 'Date(dt_date)', date('Y-m-d', $this->createTimeStart)])
            ->andFilterWhere(['<=', 'Date(dt_date)', date('Y-m-d', $this->createTimeEnd)])
            ->orderBy(['Date(dt_date)' => SORT_DESC]);

        return $query->all();
    }

    public function searchRevBookingAgency($params = null, $admin)
    {
        $query = new Query();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->select([
            'Date(created_on) AS dtDate',
            'COUNT(id) as total',
            "SUM( IF((status = 'CREATE'), 1, 0)) AS totalCreate",
            "SUM( IF((status = 'CONFIRM'), 1, 0)) AS totalConfirm",
            "SUM( IF((status = 'WAITING'), 1, 0)) AS totalWaiting",
            "SUM( IF((status = 'REJECT'), 1, 0)) AS totalReject",
        ])
            ->from('booking')
            ->andFilterWhere(['>=', 'Date(created_on)', date('Y-m-d 00:00:00', $this->createTimeStart)])
            ->andFilterWhere(['<=', 'Date(created_on)', date('Y-m-d 23:59:59', $this->createTimeEnd)])
            ->andFilterWhere(['admin_id' => $admin->id])
            ->groupBy(['dtDate'])
            ->orderBy(['dtDate' => SORT_DESC]);

        return $dataProvider;
    }

    public function reasonReject()
    {
        $reason_reject = SystemConfiguration::findByKeyword('reason_reject');
        $reason_reject_array = explode('|', $reason_reject);
        $reason_reject_array = array_combine(range(1, count($reason_reject_array)), $reason_reject_array);

        $reason_reject_array[999] = ADD_TYPE_REJECT;

        return $reason_reject_array;
    }

    public function getDataRevenueBooking($dataProviderModels)
    {
        $dataGroup = $dataGroupTripType = $result = [];

        if (! empty($dataProviderModels)) {
            foreach ($dataProviderModels as $item) {
                $created_on = date('Y-m-d', strtotime($item['created_on']));
                $type = $item['type'];
                $status = $item['status'];

                if (array_key_exists($type, SOURCE_TRIP_TYPE_LIST)) {
                    $dataGroup[$created_on][$type][$status][] = $item;
                }
            }

            foreach ($dataGroup as $keyDate => $itemGroupDate) {
                $totalDate = 0;

                foreach (SOURCE_TRIP_TYPE_LIST as $keyTripType => $itemTripType) {
                    $totalStatus = 0;

                    // count trip status
                    foreach (TRIP_STATUS_LIST_FULL as $keyTripStatus => $itemTripStatus) {
                        // reset data group
                        $dataGroupTripType[$keyTripType][$keyTripStatus] = [];

                        $dataGroupTripType[$keyTripType][$keyTripStatus]['total'] = isset($itemGroupDate[$keyTripType][$keyTripStatus]) ? count($itemGroupDate[$keyTripType][$keyTripStatus]) : 0;
                        $totalStatus += $dataGroupTripType[$keyTripType][$keyTripStatus]['total'];

                        if ($keyTripStatus == TRIP_STATUS_REJECT && ! empty($itemGroupDate[$keyTripType][$keyTripStatus])) {
                            $array = array_column($itemGroupDate[$keyTripType][$keyTripStatus], 'type_reject');
                            if (isset($array) && is_array($array) && count($array)) {
                                foreach ($array as $key => $value) {
                                    $array[$key] = (int)$value;
                                }
                            }
                            $typeRejects = array_count_values($array);

                            $dataGroupTripType[$keyTripType][$keyTripStatus]['type_rejects'] = $typeRejects;
                        }
                    }

                    $dataGroupTripType[$keyTripType]['totalStatus'] = $totalStatus;
                    $totalDate += $totalStatus;
                }

                $result[$keyDate] = $dataGroupTripType;
                $result[$keyDate]['totalDate'] = $totalDate;
            }
        }

        return $result;
    }

    public function searchRevSchedule($params = null)
    {
        $query = new Query();
        $abc = new Query();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            return $dataProvider;
        }
        $query = (new \yii\db\Query())
            ->select([
                'SUM(IF(round_trip = 0, 1, 0)) AS total_0',
                'SUM(IF(round_trip = 1, 1, 0)) AS total_1',
                'SUM(IF(round_trip = 2, 1, 0)) AS total_2',
                'SUM(IF(round_trip = 3, 1, 0)) AS total_3',
                'SUM(IF(round_trip = 4, 1, 0)) AS total_4',
            ])
            ->from('trip')
            ->where(['BETWEEN', 'DATE(trip.created_on)', date('Y-m-d 00:00:00', $this->createTimeStart), date('Y-m-d 23:59:59', $this->createTimeEnd)])
            ->andWhere(['IN', 'trip.status', ['DONE', 'COMPLETE', 'OPEN', 'CANCEL']]);

        return $query->one();
    }

    public function searchRevGroupZalo($params = null)
    {
        $query = new Query();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            return $dataProvider;
        }
        $query->from('trip')
            ->select(['trip.id'])
            ->innerJoin('trip_group', [
                'AND',
                'trip.trip_group_id = trip_group.id',
                ['BETWEEN', 'DATE(trip_group.created_on)', date('Y-m-d 00:00:00', $this->createTimeStart), date('Y-m-d 23:59:59', $this->createTimeEnd)],
            ])
            ->andWhere(['IN', 'trip.status', ['DONE', 'COMPLETE']])
            ->groupBy(['trip.id']);

        return $query->count();
    }

    public function searchRevCustomerRollback($params = null)
    {
        $query = new Query();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            return $dataProvider;
        }
        $query->from('trip')
            ->andWhere(['IN', 'trip.customer_property', [CUSTOMER_PROPERTY_RETURN, CUSTOMER_PROPERTY_RETURN_CSKH]])
            ->andWhere(['BETWEEN', 'DATE(trip.created_on)', date('Y-m-d 00:00:00', $this->createTimeStart), date('Y-m-d 23:59:59', $this->createTimeEnd)])
            ->groupBy(['trip.id']);

        return $query->count();
    }
}
