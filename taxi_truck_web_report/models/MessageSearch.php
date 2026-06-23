<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\base\Model;

use yii\data\ActiveDataProvider;

/**
 * MessageSearch represents the model behind the search form of `app\models\Message`.
 */
class MessageSearch extends Message
{
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;

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
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['title', 'content', 'time'], 'safe'],
            ['phone', 'required'],
            [['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
        $query = Message::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ])->andFilterWhere(['>=', 'time', date('Y-m-d 00:00:00', $this->createTimeStart)])
            ->andFilterWhere(['<=', 'time', date('Y-m-d 23:59:59', $this->createTimeEnd)]);

        if ($this->phone == '0') {
            return $dataProvider;
        }
        $query->andFilterWhere(['phone' => $this->phone]);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return [
            'createTimeRange' => 'Thời gian',
            'phone' => 'Tài xế',
        ];
    }
}
