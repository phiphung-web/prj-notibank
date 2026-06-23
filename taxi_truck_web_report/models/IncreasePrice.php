<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "increase_price".
 *
 * @property int $id
 */
class IncreasePrice extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'increase_price';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
      [['type_of_car', 'minute_before', 'price_increase'], 'integer'],
      [['type_of_car', 'minute_before', 'price_increase'], 'safe'],
    ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
      'id' => 'ID',
      'type_of_car' => 'Loại xe',
      'minute_before' => 'Số phút trước thời gian đi',
      'price_increase' => 'Giá tăng',
    ];
    }
}
