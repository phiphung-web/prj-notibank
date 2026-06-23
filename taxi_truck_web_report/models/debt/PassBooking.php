<?php

namespace app\models\debt;

use app\models\Trip;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * PassBooking represents the model behind the search form of `app\models\Trip`.
 */
class PassBooking extends Trip
{
    public $payment_status;
    public $keyword;

    public function rules()
    {
        return [
            [['payment_status'], 'integer'],
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'payment_status' => 'Trạng thái thanh toán',
            'keyword' => 'Từ khoá',
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

    public function countPassBooking($agency_debt)
    {
        return Trip::find()
            ->where([
                'trip.agency_debt' => 0,
                'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
            ])
            ->andWhere(['!=', 'agency_id', 0])->groupBy('agency_id')
            ->innerJoin('agency', 'trip.agency_id = agency.id AND agency.status = 1 AND agency.agency_debt = ' . $agency_debt)
            ->count();
    }

    public function searchPassBooking($params)
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
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }
        $query->select(['trip.*', 'driver.username', 'driver.display_name', 'bid.price as b_price_bid', 'payment_method', 'paid_driver_on'])
            ->from('booking')
            ->innerJoin('trip', 'trip.booking_id = booking.id and trip.status IN ("' . STATUS_TRIP_DONE . '", "' . STATUS_TRIP_COMPLETE . '")')
            ->innerJoin('bid', 'trip.id = bid.trip_id and bid.status = "' . STATUS_BID_SUCCESS . '"')
            ->innerJoin('driver', 'booking.driver_id_created = driver.id')
            ->where([
                'booking.type' => SOURCE_TRIP_TYPE_DRIVER,
            ]);
        $query->andWhere(['>', 'trip.pickup_time', '2026-01-01 00:00:00']);

        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'trip.customer_name', '%' . $this->keyword . '%', false],
                ['LIKE', 'trip.customer_phone', '%' . $this->keyword . '%', false],
            ]);
        }
        if (! empty($this->payment_status)) {
            $query->andWhere(['IS NOT', 'booking.paid_driver_on', null]);
        } else {
            $query->andWhere(['IS', 'booking.paid_driver_on', null]);
        }
        $query->groupBy('booking.id');

        return $dataProvider;
    }
}
