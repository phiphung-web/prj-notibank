<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\data\ActiveDataProvider;

/**
 * SearchGroupZalo represents the model behind the search form of `app\models\GroupZalo`.
 */
class SearchGroupZalo extends GroupZalo
{
    public $keyword;
    public $pickupTimeRange;
    public $pickupTimeStart;
    public $pickupTimeEnd;

    /**
     * {@inheritdoc}
     */

    public function behaviors()
    {
        return [
            [
                'class' => DateRangeBehavior::class,
                'attribute' => 'pickupTimeRange',
                'dateStartAttribute' => 'pickupTimeStart',
                'dateEndAttribute' => 'pickupTimeEnd',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keyword'], 'string'],
            [['pickupTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'point' => 'Điểm',
            'money' => 'Tiền phế',
            'keyword' => 'Từ khóa',
            'pickupTimeRange' => 'Khoảng thời gian',
        ];
    }

    /**
     * Creates a data provider instance with search query applied.
     *
     * @param array|null $params The search parameters.
     *
     * @return ActiveDataProvider The data provider instance.
     */
    public function search($params = null)
    {
        $query = GroupZalo::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        // If the model's data doesn't pass validation, return an empty data provider
        if (! $this->validate()) {
            $query->where('0=1'); // Exclude records when validation fails

            return $dataProvider;
        }

        $query->select([
            'group_zalo.*',
            'group_zalo_catalogue.name AS group_zalo_catalogue_name',
        ])
            ->leftJoin('group_zalo_catalogue', 'group_zalo.group_zalo_catalogue = group_zalo_catalogue.id')
            ->where([
                'group_zalo.status' => 1,
            ])
            ->groupBy(['group_zalo.id']);
        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'group_zalo_catalogue.name', '%' . $this->keyword . '%', false],
                ['LIKE', 'group_zalo.name', '%' . $this->keyword . '%', false],
            ]);
        }
        $query->orderBy('group_zalo_catalogue_name ASC, group_zalo.name ASC');

        return $dataProvider;
    }

    /**
     * Creates a data provider instance with search query applied for statistical purposes.
     *
     * @param array|null $params The search parameters.
     *
     * @return ActiveDataProvider The data provider instance.
     */
    public function search_statistic($params = null)
    {
        $query = GroupZalo::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }

        $pickupTimeStart = date('Y-m-d 00:00:00', $this->pickupTimeStart);
        $pickupTimeEnd = date('Y-m-d 23:59:59', $this->pickupTimeEnd);

        $query->select([
            'group_zalo.*',
            'group_zalo_catalogue.name AS group_zalo_catalogue_name',
            'SUM(CASE WHEN tg.type = 1
                AND t.status NOT LIKE :cancelStatus
                AND t.pickup_time BETWEEN :startDate AND :endDate
                THEN (t.price_customer - tg.price) ELSE 0 END) AS money',
        ])
            ->leftJoin('group_zalo_catalogue', 'group_zalo.group_zalo_catalogue = group_zalo_catalogue.id')
            ->leftJoin('trip_group tg', 'group_zalo.id = tg.group_zalo_id')
            ->innerJoin('trip t', 'tg.id = t.trip_group_id')
            ->where([
                'group_zalo.status' => 1,
            ])
            ->andWhere(['NOT LIKE', 't.status', STATUS_TRIP_CANCEL])
            ->groupBy(['group_zalo.id'])
            ->params([
                ':cancelStatus' => STATUS_TRIP_CANCEL,
                ':startDate' => $pickupTimeStart,
                ':endDate' => $pickupTimeEnd,
            ]);
        if ($pickupTimeStart != date('Y-m-d 00:00:00') && $pickupTimeEnd != date('Y-m-d 23:59:59')) {
            $query->andWhere(['BETWEEN', 't.pickup_time', $pickupTimeStart, $pickupTimeEnd]);
        }
        if (! empty($this->keyword)) {
            $query->andFilterWhere(['LIKE', 'group_zalo_catalogue.name', $this->keyword])
                ->orFilterWhere(['LIKE', 'group_zalo.name', $this->keyword]);
        }
        $query->orderBy('group_zalo_catalogue_name ASC, group_zalo.name ASC');

        return $dataProvider;
    }
}
