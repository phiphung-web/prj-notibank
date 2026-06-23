<?php

namespace app\models\debt;

use app\models\Trip;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * TripAgency represents the model behind the search form of `app\models\Trip`.
 */
class TripAgency extends Trip
{
    public $keyword;

    public function rules()
    {
        return [
            [['keyword'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'keyword' => 'Từ khóa',
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

    public function countAgencyDebt($agency_debt)
    {
        return Trip::find()
            ->where([
                'trip.agency_debt' => 0,
                'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
            ])
            ->andWhere(['!=', 'agency_id', 0])->groupBy('agency_id')
            ->andWhere(['>', 'pickup_time', '2026-01-01 00:00:00'])
            ->innerJoin('agency', 'trip.agency_id = agency.id AND agency.status = 1 AND agency.agency_debt = ' . $agency_debt)
            ->count();
    }

    public function searchAgencyDebt($params, $agency_debt)
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
        $query->select([
            'agency.*',
            'SUM(
                IF( 
                    trip.money_debt_agency <> 0, 
                    trip.money_debt_agency, 
                    IF(
                        ((trip.price_customer - bid.price) > (trip.price_customer / 100) * agency.percent) AND agency.percent <> 0, 
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
        ])->from('agency')
            ->innerJoin('trip', 'trip.agency_id = agency.id')
            ->innerJoin('bid', 'trip.id = bid.trip_id and bid.status = "' . STATUS_BID_SUCCESS . '"')
            ->where([
                'trip.agency_debt' => 0,
                'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
                'agency.status' => 1,
                'agency.agency_debt' => $agency_debt,
            ])
            ->andWhere(['>', 'pickup_time', '2026-01-01 00:00:00'])
            ->andWhere(['!=', 'trip.agency_id', 0]);

        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'agency.name', '%' . $this->keyword . '%', false],
                ['LIKE', 'agency.phone', '%' . $this->keyword . '%', false],
            ]);
        }

        $query->groupBy('agency.id');

        return $dataProvider;
    }

    public function searchTripDebt($id, $time = '')
    {
        $query = Trip::find();
        $query->andWhere([
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
            'trip.agency_id' => $id,
        ]);

        if (empty($time)) {
            $query->andWhere([
                'trip.agency_debt' => 0,
            ]);
        }

        $query->leftJoin('driver_sub', 'driver.id = driver_sub.driver_id AND trip.id = driver_sub.trip_id');

        $relatedTables = [
            'tripReturnNoOrder AS tripReturn',
            'tripReturn.driver AS tripReturnDriver',
            'bid',
            'bid.driver',
            'bid.driver.car',
            'agency',
            'tripGroup',
            'tripGroup.groupZalo',
            'tripGroup.groupZaloSeller',
        ];
        if (! empty($time)) {
            $query->andFilterWhere(['>=', 'trip.created_on', date('Y-m-01 00:00:00', strtotime($time))])
                ->andFilterWhere(['<=', 'trip.created_on', date('Y-m-d 23:59:59', strtotime($time . '+1 month -1 day'))]);
        }
        $query->joinWith($relatedTables);
        $query->andWhere(['bid.status' => STATUS_BID_SUCCESS]);
        $query->orderBy(['created_on' => SORT_DESC]);

        return $query->all();
    }
}
