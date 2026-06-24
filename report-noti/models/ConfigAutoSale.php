<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "config_auto_sale".
 *
 * @property int $id
 * @property int $type_of_car
 * @property int $minute_before
 * @property int $schedule
 * @property int $price
 */
class ConfigAutoSale extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'config_auto_sale';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
      [['type_of_car', 'schedule', 'price'], 'integer'],
      [['type_of_car', 'from_time', 'to_time','schedule', 'price'], 'safe'],
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
      'from_time' => 'Thời gian bắt đầu',
      'to_time' => 'Thời gian kết thúc',
      'schedule' => 'Lịch trình',
      'price' => 'Giá',
    ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $result = parent::validate($attributeNames, $clearErrors);
        if (! $this->hasErrors() && ($this->isNewRecord || $this->isAttributeChanged('to_time'))) {
            if ($this->to_time < $this->from_time) {
                $this->addError('to_time', 'Giá trị to_minute phải lớn hơn hoặc bằng from_time');
                $result = false;
            }
        }

        return $result;
    }
}
