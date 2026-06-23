<?php

namespace app\models\customerService;

use app\models\Customer;
use kartik\daterange\DateRangeBehavior;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

class CustomerServiceSearch extends CustomerService
{
    public $keyword;
    public $source;
    public $pickupTimeRange;
    public $pickupTimeStart;
    public $pickupTimeEnd;
    public $vip;

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
            [['id', 'trip_id', 'customer_id', 'driver_id', 'type', 'status', 'userid_created', 'userid_updated', 'source'], 'integer'],
            [['cus_feedback_trip', 'cus_feedback_driver', 'created_at', 'keyword', 'vip', 'times'], 'safe'],
            [['pickupTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pickupTimeRange' => 'Thời gian đi',
            'status' => 'Trạng thái',
            'times' => 'Số lần chăm sóc',
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = new Query();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        $this->load($params);
        if (! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->select([
            'trip.*',
            'bid.driver_id',
            'trip.id as trip_id',
            'customer_service.id as customer_service_id',
            'customer_service.cus_feedback_trip',
            'customer_service.cus_feedback_driver',
            'customer_service.point',
            'customer_service.status AS customer_service_status',
            'IF(customer_service.times is null, 0 , customer_service.times) as times',
            'customer_service.userid_created AS customer_service_userid_created',
            'customer_service.userid_updated AS customer_service_userid_updated',
        ])->from(['trip' => 'trip'])
            ->innerJoin('bid', 'bid.trip_id = trip.id AND bid.status = "' . STATUS_BID_SUCCESS . '"')
            ->innerJoin(['subquery' => $this->buildSubQueryCountCustomerPhone()], 'subquery.customer_phone = trip.customer_phone')
            ->leftJoin('customer_service', 'customer_service.trip_id = trip.id');

        $query->andWhere([
            'or',
            ['trip.agency_id' => 0],
            ['trip.agency_id' => null],
        ]);

        if (! empty($this->times)) {
            if (in_array(0, $this->times)) {
                $query->andWhere([
                    'or',
                    ['customer_service.times' => 0],
                    ['customer_service.times' => null],
                    ['in', 'customer_service.times', array_diff($this->times, [0])],
                ]);
            } else {
                $query->andFilterWhere(['in', 'customer_service.times', $this->times]);
            }
        }

        if ($this->vip) {
            $query->andWhere(['>=', 'subquery.total_trip', 8]);
        } else {
            $query->andWhere(['<', 'subquery.total_trip', 8]);
        }

        if (empty($this->pickupTimeRange)) {
            $query->andFilterWhere(['between', 'trip.pickup_time', '2024-01-09 00:00:00', gmdate('Y-m-d 23:59:59', time() + 7 * 3600)]);
        } else {
            $pickupTimeStart = date('Y-m-d 00:00:00', $this->pickupTimeStart);
            $pickupTimeEnd = date('Y-m-d 23:59:59', $this->pickupTimeEnd);
            $query->andFilterWhere(['between', 'trip.pickup_time', $pickupTimeStart, $pickupTimeEnd]);
        }

        $query->andWhere([
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        ]);

        if ($this->source !== '') {
            if ($this->source == 0) {
                $query->andWhere(['not in', 'trip.customer_property', [CUSTOMER_PROPERTY_RETURN, CUSTOMER_PROPERTY_RETURN_CSKH]]);
            } elseif ($this->source == SOURCE_TRIP_TYPE_CUSTOMER) {
                $query->andWhere(['in', 'trip.customer_property', [CUSTOMER_PROPERTY_RETURN, CUSTOMER_PROPERTY_RETURN_CSKH]]);
            }
        }

        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'or',
                ['like', 'trip.pickup_address', $this->keyword],
                ['like', 'trip.destination_address', $this->keyword],
                ['like', 'trip.customer_name', $this->keyword],
                ['like', 'trip.customer_phone', $this->keyword],
            ]);
        }

        if (! empty($this->userid_created)) {
            $query->andFilterWhere(['customer_service.userid_created' => $this->userid_created]);
        }

        if (! empty($this->status) && $this->status > 0) {
            $query->andFilterWhere(['customer_service.status' => $this->status]);
        } elseif ($this->status == 0) {
            $query->andWhere([
                'or',
                ['customer_service.status' => STATUS_CUSTOMER_SERVICE_NO_PROCESS],
                ['customer_service.status' => null],
            ]);
        }
        $query->orderBy(['customer_service.userid_created' => SORT_ASC, 'pickup_time' => SORT_ASC])
            ->groupBy('trip.id');

        return $dataProvider;
    }

    /**
     * Define a one-to-one relationship with the Customer model.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    public function buildSubQueryCountCustomerPhone()
    {
        return (new Query())
            ->select([
                'customer_phone',
                'COUNT(trip.id) AS total_trip',
            ])
            ->from('trip')
            ->where(['trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE]])
            ->andWhere([
                'or',
                ['trip.agency_id' => 0],
                ['trip.agency_id' => null],
            ])
            ->andWhere(['>', 'trip.pickup_time', new Expression('NOW() - INTERVAL 6 MONTH')])
            ->andWhere(['<=', 'trip.pickup_time', new Expression('DATE_ADD(NOW(), INTERVAL 7 HOUR)')])
            ->groupBy('trip.customer_phone')
            ->orderBy(['total_trip' => SORT_DESC]);
    }
}
