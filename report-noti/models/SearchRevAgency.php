<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class SearchRevAgency extends Model
{
    public $id;
    public function rules()
    {
        return [
      [['id'], 'integer'],
    ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
      'id' => 'Đại lý',
    ];
    }

    public function search($params)
    {
        $query = new Query();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 20,
      ],
    ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $query->select([
      'agency.*',
      'DATE_FORMAT(trip.created_on, "Tháng %m/%Y") AS dtMonth',
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
      ) as trip_debt',
      'SUM(trip.price_customer) AS total_price',
      'COUNT(trip.id) AS count_trip',
    ])->from('agency')
            ->innerJoin('trip', 'trip.agency_id = agency.id')
            ->innerJoin('bid', 'trip.id = bid.trip_id and bid.status = "' . STATUS_BID_SUCCESS . '"')
            ->where([
        'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
        'agency.status' => 1,
      ])
            ->andWhere(['!=', 'trip.agency_id', 0]);

        if (isset($params['time_start']) && isset($params['time_end'])) {
            $query->andFilterWhere(['>=', 'trip.pickup_time', date('Y-m-d 00:00:00', strtotime(substr($params['time_start'], 0, 4) . '-' . substr($params['time_start'], 4, 2)))])
                ->andFilterWhere(['<=', 'trip.pickup_time', date('Y-m-d 23:59:59', strtotime(substr($params['time_end'], 0, 4) . '-' . substr($params['time_end'], 4, 2)))]);
        } else {
            $query->andFilterWhere(['>=', 'trip.pickup_time', date('Y-01-01 00:00:00')])
                ->andFilterWhere(['<=', 'trip.pickup_time', date('Y-m-d 23:59:59')]);
        }

        if (isset($this->id) && $this->id > 0) {
            $query->andFilterWhere(['agency.id' => $this->id]);
        }

        $query->groupBy(['agency.id', 'dtMonth'])
            ->orderBy(['agency.id' => SORT_DESC, 'dtMonth' => SORT_DESC]);

        return $dataProvider;
    }
}
