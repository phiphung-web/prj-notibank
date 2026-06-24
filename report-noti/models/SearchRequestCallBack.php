<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\data\ActiveDataProvider;

class SearchRequestCallBack extends RequestCallBack
{
    /**
     * {@inheritdoc}
     */

    public $createdOnTimeRange;
    public $createdOnTimeStart;
    public $createdOnTimeEnd;

    public function behaviors()
    {
        return [
      [
        'class' => DateRangeBehavior::className(),
        'attribute' => 'createdOnTimeRange',
        'dateStartAttribute' => 'createdOnTimeStart',
        'dateEndAttribute' => 'createdOnTimeEnd',
      ],
    ];
    }

    public function rules()
    {
        return [
      [['createdOnTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
    ];
    }

    public function attributeLabels()
    {
        return [
      'createdOnTimeRange' => 'Ngày yêu cầu',
    ];
    }

    public function search($params)
    {
        $this->load($params);
        $this->validate();
        $phone = ! empty($params['SearchRequestCallBack']) ? $params['SearchRequestCallBack']['phone'] : '';
        $status = ! empty($params['SearchRequestCallBack']) ? $params['SearchRequestCallBack']['status'] : REQUEST_CALL_BACK_WAITING;

        $query = $this->find()->orderBy(['created_on' => SORT_DESC]);

        if ($status != REQUEST_CALL_BACK_ALL) {
            $query->where(['status' => $status]);
        }
        if ($phone) {
            $query = $query->andWhere(['like', 'phone', $phone]);
        }

        $createdOnTimeStart = date('Y-m-d 00:00:00', $this->createdOnTimeStart);
        $createdOnTimeEnd = date('Y-m-d 23:59:59', $this->createdOnTimeEnd);
        $query->andFilterWhere(['between', 'created_on', $createdOnTimeStart, $createdOnTimeEnd]);

        return new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 20,
      ],
    ]);
    }
}
