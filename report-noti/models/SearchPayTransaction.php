<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SearchPayTransaction represents the model behind the search form of `app\models\PayTransaction`.
 */
class SearchPayTransaction extends PayTransaction
{
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'money', 'driver_id'], 'integer'],
            [['created_on', 'modified_on', 'description'], 'safe'],
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
        $query = PayTransaction::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_on' => $this->created_on,
            'modified_on' => $this->modified_on,
            'money' => $this->money,
            'driver_id' => $this->driver_id,
        ])->andFilterWhere(['>=', 'created_on', date('Y-m-d 00:00:00', $this->createTimeStart)])
            ->andFilterWhere(['<=', 'created_on', date('Y-m-d 23:59:59', $this->createTimeEnd)])
            ->andFilterWhere(['!=', 'driver_id', 0])
            ->andFilterWhere(['is_disabled' => 0]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }

    public function searchDriver($driverId)
    {
        $query = PayTransaction::find();

        $dataProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => [
            'pageSize' => 20,
          ],
        ]);

        $this->load($driverId);

        if (! $this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
          'driver_id' => $driverId, ]);
        $query->orderBy(['created_on' => SORT_DESC]);

        return $dataProvider;
    }
}
