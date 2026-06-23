<?php

namespace app\models\agency;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * SearchAgency represents the model behind the search form of `app\models\Agency`.
 */
class SearchAgency extends ActiveRecord
{
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['keyword'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'keyword' => 'Từ khóa',
        ];
    }

    public function search($params = null)
    {
        $query = new Query();

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
        $query->select([
            'agency.*',
            '(SELECT COUNT(trip.id) FROM trip INNER JOIN bid on trip.id = bid.trip_id AND bid.status = "SUCCESS" WHERE trip.agency_id = agency.id AND trip.status IN("DONE", "COMPLETE")) as total_success',
            '(SELECT COUNT(trip.id) FROM trip WHERE trip.agency_id = agency.id ) as total_trip',
        ])
            ->from('agency');
        if (! empty($this->keyword)) {
            $query->andFilterWhere(['LIKE', 'name', $this->keyword])
                ->orFilterWhere(['LIKE', 'address', $this->keyword])
                ->orFilterWhere(['LIKE', 'phone', $this->keyword])
                ->orFilterWhere(['LIKE', 'email', $this->keyword])
                ->orFilterWhere(['LIKE', 'contact_person', $this->keyword]);
        }
        $query->orderBy('id ASC, name ASC');

        return $dataProvider;
    }
}
