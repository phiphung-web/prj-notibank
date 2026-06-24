<?php

namespace app\models\marketing;

use kartik\daterange\DateRangeBehavior;
use yii\data\ActiveDataProvider;

class Booking extends \yii\db\ActiveRecord
{
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking';
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
    public function rules()
    {
        return [[['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/']];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'createTimeRange' => 'Thời gian tạo',
        ];
    }

    public function search($params)
    {
        $query = Booking::find();

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
        if (! empty($createTimeStart) && ! empty($createTimeEnd)) {
            $createTimeStart = date('Y-m-d 00:00:00', $this->createTimeStart);
            $createTimeEnd = date('Y-m-d 23:59:59', $this->createTimeEnd);
            $query->andFilterWhere(['between', 'created_on', $createTimeStart, $createTimeEnd]);
        }

        $query->andFilterWhere(['type' => SOURCE_TRIP_TYPE_MAIL_1]);
        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }
}
