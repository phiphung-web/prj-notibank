<?php

namespace app\models;

/**
 * This is the model class for table "trip_return".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property int $bid_id
 * @property int $money
 * @property bool $refund
 */
class TripReturn extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trip_return';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'money_before', 'money_after'], 'safe'],
            [['bid_id', 'note'], 'required'],
            [['bid_id', 'money', 'trip_id', 'driver_id'], 'integer'],
            [['refund'], 'boolean'],
            [['note'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_on' => 'Created On',
            'modified_on' => 'Modified On',
            'bid_id' => 'Bid ID',
            'money' => 'Số tiền hoàn',
            'refund' => 'Hoàn tiền',
            'note' => 'Lý do trả lịch',
        ];
    }

    public function beforeSave($insert)
    {
        if (! parent::beforeSave($insert)) {
            return false;
        }

        if ($this->created_on == null) {
            $this->created_on = new \yii\db\Expression('NOW()');
        }

        $this->modified_on = new \yii\db\Expression('NOW()');


        return true;
    }
    public function getDriver()
    {
        return $this->hasOne(Driver::class, ['id' => 'driver_id']);
    }
}
