<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchTrip represents the model behind the search form of `app\models\Trip`.
 */
class SearchTrip extends Trip
{
    /**
     * {@inheritdoc}
     */
    public $pickupTimeRange;
    public $pickupTimeStart;
    public $pickupTimeEnd;
    public $driver_ban;
    public $group_zalo_id;
    public $filter_time_price;
    public $key_search;
    public $userid_created;
    public $room;
    public $filter_type_of_car;

    public function behaviors()
    {
        return [
            [
                'class' => DateRangeBehavior::className(),
                'attribute' => 'pickupTimeRange',
                'dateStartAttribute' => 'pickupTimeStart',
                'dateEndAttribute' => 'pickupTimeEnd',
            ],
        ];
    }
    public function rules()
    {
        return [
            [['id', 'price_bid', 'price_customer', 'source_trip', 'trip_group_id', 'agency_id', 'group_zalo_id', 'customer_property', 'service'], 'integer'],
            ['userid_created', 'each', 'rule' => ['integer']],
            [['created_on', 'modified_on', 'customer_name', 'customer_phone', 'description', 'pickup_time', 'status', 'room', 'round_trip'], 'safe'],
            [['is_have_bill', 'is_collect_money', 'driver_ban'], 'boolean'],
            [['pickupTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
            [['filter_type_of_car', 'filter_time_price', 'key_search'], 'string'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'pickupTimeRange' => 'Thời gian đón khách',
            'source_trip' => 'Nguồn nhận lịch',
            'status' => 'Trạng thái',
            'group_zalo_id' => 'Nhóm bán',
            'is_have_bill' => 'Xuất hóa đơn',
            'is_collect_money' => 'Không thu tiền khách',
            'agency_id' => 'Chọn nguồn đại lý',
            'driver_ban' => 'Lái xe có nhiều xe',
            'room' => 'Chỉ lấy lịch theo room',
            'round_trip' => 'Lịch trình',
            'userid_created' => 'Người tạo đơn',
            'customer_property' => 'Thuộc tính khách hàng',
            'service' => 'Dịch vụ',
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Trip::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->joinWith(['tripReturn', 'admin']);
        $query->joinWith(['tripReturn.driver AS tripReturnDriver'])->andWhere(['OR', ['tripReturnDriver.id' => null], ['tripReturnDriver.id' => new \yii\db\Expression('trip_return.driver_id')]]);
        if (($this->status == STATUS_TRIP_DONE || $this->status == STATUS_TRIP_COMPLETE)) {
            if ($this->driver_ban == STATUS_DRIVER_BAN) {
                $query->innerJoin('bid b', 'trip.id = b.trip_id and b.status = "' . STATUS_BID_SUCCESS . '"')
                    ->innerJoin('driver d', 'b.driver_id = d.id AND d.driver_ban = ' . STATUS_DRIVER_BAN);
            }
            // Lái xe nhận
            $query->joinWith(['bid', 'bid.driver'])->andWhere(['bid.status' => STATUS_BID_SUCCESS]);
            $query->joinWith(['bid.driver.car']);
            $query->leftJoin('driver_sub', 'driver.id = driver_sub.driver_id AND trip.id = driver_sub.trip_id');
            $query->select([
                'trip.*',
                'driver_sub_name' => 'driver_sub.name',
                'driver_sub_phone' => 'driver_sub.phone',
                'driver_sub_bks' => 'driver_sub.bks',
                'driver_sub_type' => 'driver_sub.type',
            ]);
        }
        $query->joinWith(['agency']);
        $query->joinWith(['tripGroup', 'tripGroup.groupZalo', 'tripGroup.groupZaloSeller'])->andWhere(['OR', ['trip_group.id' => null], ['trip_group.id' => new \yii\db\Expression('trip.trip_group_id')]]);
        if ($this->status === 'NOT_YET_SOLD' || empty($this->status)) {
            $query->andWhere(['trip.status' => ['CREATE', 'OPEN', 'EXPIRE']]);
        } elseif ($this->status === 'READY_EXPIRE') {
            $query->andWhere(['trip.status' => 'OPEN']);
        } elseif ($this->status === 'ALL') {
            $query->andWhere(['!=', 'trip.status', 'PENDING']);
        } elseif ($this->status === 'ALL_NOT_CANCEL') {
            $query->andWhere(['NOT IN', 'trip.status', ['PENDING', 'CANCEL']]);
        } elseif ($this->status === 'CREATE') {
            $query->andFilterWhere([
                'OR',
                ['trip.status' => 'CREATE'],
                ['AND', ['trip.status' => 'OPEN'], ['>=', 'sell_start_time', gmdate('Y-m-d H:i:s', time() + 7 * 3600)]],
            ]);
        } elseif ($this->status === 'OPEN') {
            $query->andFilterWhere([
                'AND',
                ['trip.status' => 'OPEN'],
                ['<=', 'sell_start_time', gmdate('Y-m-d H:i:s', time() + 7 * 3600)],
            ]);
        } else {
            $query->andWhere(['trip.status' => $this->status]);
        }

        if ($this->status === 'READY_EXPIRE') {
            $query->andFilterWhere(['between', 'trip.pickup_time', gmdate('Y-m-d H:i:s', time() + 7 * 3600), gmdate('Y-m-d H:i:s', time() + 8 * 3600)]);
        } else {
            $pickupTimeStart = date('Y-m-d 00:00:00', $this->pickupTimeStart);
            $pickupTimeEnd = date('Y-m-d 23:59:59', $this->pickupTimeEnd);
            if (isset($params['created_on']) && $params['created_on'] == 'on') {
                $query->andFilterWhere(['between', 'trip.created_on', $pickupTimeStart, $pickupTimeEnd]);
            } else {
                $query->andFilterWhere(['between', 'pickup_time', $pickupTimeStart, $pickupTimeEnd]);
            }
        }

        $query->orderBy(['pickup_time' => SORT_ASC]);

        if ($this->source_trip > 0) {
            $query->andFilterWhere(['source_trip' => $this->source_trip]);
            if ($this->agency_id != '') {
                $query->andFilterWhere(['trip.agency_id' => $this->agency_id]);
            }
        }
        if ($this->group_zalo_id != '') {
            $query->innerJoin('trip_group tg', 'trip.trip_group_id = tg.id')->andWhere(['tg.group_zalo_id' => $this->group_zalo_id]);
        }

        $ids = array_filter((array)$this->userid_created, 'is_numeric');

        if ($ids) {
            $query->andFilterWhere(['in', 'trip.userid_created', $ids]);
        }

        if ($this->is_have_bill == 1) {
            $query->andFilterWhere(['is_have_bill' => $this->is_have_bill]);
        }
        if ($this->is_collect_money == 1) {
            $query->andFilterWhere(['is_collect_money' => 0]);
        }

        if ($this->room) {
            $query->andFilterWhere(['>', 'trip_group_id', 0]);
        }

        if (! empty($this->round_trip)) {
            $query->andFilterWhere(['round_trip' => $this->round_trip]);
        }

        if (isset($this->customer_property)) {
            $query->andFilterWhere(['customer_property' => $this->customer_property]);
        }

        if (! empty($this->service)) {
            $query->andWhere([
                'or',
                ['service' => $this->service],
                new \yii\db\Expression('JSON_CONTAINS(service, :service)', [':service' => json_encode([$this->service])])
            ]);
        }

        if (! empty($this->filter_time_price) && $this->filter_time_price !== 'null') {
            $query->orderBy($this->filter_time_price);
        }

        if (! empty($this->filter_type_of_car) && $this->filter_type_of_car !== 'null') {
            $typeOfCarList = explode(',', $this->filter_type_of_car);
            $query->andFilterWhere(['in', 'trip.type_of_car', $typeOfCarList]);
        }

        if (! empty($this->key_search)) {
            $filter = [
                'OR',
                ['LIKE', 'pickup_address', '%' . $this->key_search . '%', false],
                ['LIKE', 'destination_address', '%' . $this->key_search . '%', false],
                ['LIKE', 'customer_name', '%' . $this->key_search . '%', false],
                ['LIKE', 'customer_phone', '%' . $this->key_search . '%', false],

            ];

            if (($this->status == STATUS_TRIP_DONE || $this->status == STATUS_TRIP_COMPLETE)) {
                $filter = array_merge($filter, [['LIKE', 'driver.username', '%' . $this->key_search . '%', false]]);
            }

            $query->andFilterWhere($filter);
        }
        $query->groupBy('trip.id');

        return $dataProvider;
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

    public function customerSearchHistoryTrip(Customer $customer)
    {
        $query = Trip::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $query->joinWith(['agency', 'admin']);
        $query->joinWith(['tripGroup', 'tripGroup.groupZalo', 'tripGroup.groupZaloSeller'])->andWhere(['OR', ['trip_group.id' => null], ['trip_group.id' => new \yii\db\Expression('trip.trip_group_id')]]);
        $query->andFilterWhere(['trip.customer_phone' => $customer->phone]);
        $query->groupBy('trip.id');
        $query->orderBy(['pickup_time' => SORT_DESC]);

        return $dataProvider;
    }
}
