<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchTripDriver represents the model behind the search form of `app\models\Trip`.
 */
class SearchTripDriver extends Trip
{
    /**
     * {@inheritdoc}
     */
    public $driver_ban;
    public $group_zalo_id;
    public $driver_debt;
    public $filter_time;
    public $keyword;

    public function rules()
    {
        return [
            [['id', 'price_bid', 'price_customer', 'source_trip', 'agency_id', 'trip_group_id', 'group_zalo_id'], 'integer'],
            [['created_on', 'modified_on', 'customer_name', 'customer_phone', 'description', 'pickup_time', 'status'], 'safe'],
            [['is_have_bill', 'is_collect_money', 'driver_ban'], 'boolean'],
            [['filter_time', 'driver_debt', 'keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'source_trip' => 'Nguồn nhận lịch',
            'status' => 'Trạng thái',
            'group_zalo_id' => 'Nhóm bán',
            'is_have_bill' => 'Xuất hóa đơn',
            'is_collect_money' => 'Không thu tiền khách',
            'agency_id' => 'Chọn nguồn đại lý',
            'driver_ban' => 'Lái xe có nhiều xe',
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

    /**
     * Creates a data provider instance with a search query applied for retrieving trips related to driver debt.
     *
     * @param array $params The parameters for filtering and sorting.
     *
     * @return ActiveDataProvider The data provider instance.
     */
    public function searchDriverDebt($params, $debt_type)
    {
        $query = Trip::find();
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // Load the search parameters into the search model
        $this->load($params);

        // If the search model is not valid, return an empty data provider
        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }
        $query->andWhere([
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ]);

        if ($debt_type == DEBT_SWITCHBOARD) {
            $query->andWhere([
                'trip.is_collect_money' => 0,
                'trip.driver_debt' => 0,
            ])->orWhere([
                'and',
                ['<', 'trip_group.price', 0],
                ['trip_group.type' => 2],
                ['trip.is_collect_money' => 1],
                ['trip.driver_debt' => 0],
            ]);
        } elseif ($debt_type == DEBT_DRIVER) {
            $query->andWhere([
                'trip.is_collect_money' => 1,
                'trip.driver_debt' => 0,
            ])->andWhere(['>', 'trip_group.price', 0]);
        } elseif ($debt_type == DEBT_CUSTOMERS) {
            $query->andWhere([
                'trip.is_collect_money' => 0,
                'trip.collected_money' => 0,
                'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
            ])->andFilterWhere(['!=', 'trip.source_trip', SOURCE_TRIP_TYPE_AGENCY]);
        }

        $query->innerJoin('bid b', 'trip.id = b.trip_id and b.status = "' . STATUS_BID_SUCCESS . '"');
        if ($this->driver_ban == 1) {
            $query->innerJoin('driver d', 'b.driver_id = d.id AND d.driver_ban = ' . $this->driver_ban);
        }
        $query->leftJoin('driver_sub', 'driver.id = driver_sub.driver_id AND trip.id = driver_sub.trip_id');

        $relatedTables = [
            'tripReturnNoOrder AS tripReturn',
            'tripReturn.driver AS tripReturnDriver',
            'bid.driver',
            'bid.driver.car',
            'agency',
            'tripGroup',
            'tripGroup.groupZalo',
            'tripGroup.groupZaloSeller',
        ];

        $query->joinWith($relatedTables);
        $query->andWhere(['bid.status' => STATUS_BID_SUCCESS]);
        $query->andWhere(['>', 'pickup_time', '2026-01-01 00:00:00']);

        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'pickup_address', '%' . $this->keyword . '%', false],
                ['LIKE', 'destination_address', '%' . $this->keyword . '%', false],
                ['LIKE', 'customer_name', '%' . $this->keyword . '%', false],
                ['LIKE', 'customer_phone', '%' . $this->keyword . '%', false],
            ]);
        }

        if (! empty($this->filter_time) && $this->filter_time !== 'null') {
            $query->orderBy($this->filter_time);
        }

        return $dataProvider;
    }

    public function totalMoney($debt_type)
    {
        $query = Trip::find();
        $query->select([
            'SUM(trip.price_customer) AS total_price_customer',
            'SUM(b.price) AS total_bid_price',
        ]);

        $query->andWhere([
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ]);

        if ($debt_type == DEBT_DRIVER) {
            $query->andWhere([
                'trip.is_collect_money' => 1,
                'trip.driver_debt' => 0,
            ])->andWhere(['>', 'trip_group.price', 0]);
        } elseif ($debt_type == DEBT_CUSTOMERS) {
            $query->andWhere([
                'trip.is_collect_money' => 0,
                'trip.collected_money' => 0,
                'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
            ])->andFilterWhere(['!=', 'trip.source_trip', SOURCE_TRIP_TYPE_AGENCY]);
        }

        $query->innerJoin('bid b', 'trip.id = b.trip_id and b.status = "' . STATUS_BID_SUCCESS . '"');
        if ($this->driver_ban == 1) {
            $query->innerJoin('driver d', 'b.driver_id = d.id AND d.driver_ban = ' . $this->driver_ban);
        }
        $query->leftJoin('driver_sub', 'driver.id = driver_sub.driver_id AND trip.id = driver_sub.trip_id');

        $relatedTables = [
            'bid.driver',
            'tripGroup',
        ];

        $query->joinWith($relatedTables);
        $query->andWhere(['bid.status' => STATUS_BID_SUCCESS]);

        $row = $query->asArray()->one();

        return $row;
    }
    /**
     * Count the driver debt settlement
     * @return int
     */
    public function countTripDriverSettlement()
    {
        return Trip::find()->where([
            'trip.is_collect_money' => 0,
            'trip.driver_debt' => 0,
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ])->andWhere(['>', 'pickup_time', '2026-01-01 00:00:00'])
            ->orWhere([
                'and',
                ['<', 'trip_group.price', 0],
                ['trip_group.type' => 2],
                ['trip.is_collect_money' => 1],
                ['trip.driver_debt' => 0],
            ])->joinWith(['tripGroup'])->count();
    }

    /**
     * Count the driver debt collection
     * @return int
     */
    public function countTripDriverCollection()
    {
        return Trip::find()->where([
            'is_collect_money' => 1,
            'driver_debt' => 0,
            'status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ])->andWhere(['>', 'pickup_time', '2026-01-01 00:00:00'])->andWhere(['>', 'trip_group.price', 0])->joinWith(['tripGroup'])->count();
    }

    public function countTripDebtCustomers()
    {
        return Trip::find()->where([
            'is_collect_money' => 0,
            'collected_money' => 0,
            'status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ])->andWhere(['>', 'pickup_time', '2026-01-01 00:00:00'])->andFilterWhere(['!=', 'trip.source_trip', SOURCE_TRIP_TYPE_AGENCY])->count();
    }

    /**
     * Counting the trip of the driver's car has many cars but the information of the sub driver is not available
     * @return int
     */
    public function countTripHaveDriverSubNoInfo()
    {
        $total = Trip::find()->where(['>=', 'pickup_time', date('Y-m-d 00:00:00')])->andFilterWhere(['<=', 'pickup_time', date('Y-m-d 23:59:59', strtotime('+1 day'))])->join('INNER JOIN', 'bid', 'trip.id = bid.trip_id and bid.status = "SUCCESS"')->join('INNER JOIN', 'driver', 'bid.driver_id = driver.id AND driver.driver_ban = 1 AND driver.enabled = 1')->count();
        $count = Trip::find()->where(['>=', 'pickup_time', date('Y-m-d 00:00:00')])->andFilterWhere(['<=', 'pickup_time', date('Y-m-d 23:59:59', strtotime('+1 day'))])->join('INNER JOIN', 'bid', 'trip.id = bid.trip_id and bid.status = "SUCCESS"')->join('INNER JOIN', 'driver', 'bid.driver_id = driver.id AND driver.driver_ban = 1 AND driver.enabled = 1')->join('INNER JOIN', 'driver_sub', 'trip.id = driver_sub.trip_id AND driver.id = driver_sub.driver_id ')->count();

        return $total - $count;
    }
}
