<?php

namespace app\models;

/**
 * This is the model class for table "bid".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property int $driver_id
 * @property int $trip_id
 *
 * @property Driver $driver
 * @property Trip $trip
 */
class Bid extends \yii\db\ActiveRecord
{
    public $referrer;
    public $send_zalo_message;
    public $zalo_disable_reason;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bid';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'referrer', 'money_before', 'price', 'money_after', 'send_zalo_message', 'zalo_disable_reason','pickup_images', 'dropoff_images'], 'safe'],
            [['driver_id', 'trip_id'], 'default', 'value' => null],
            [['driver_id', 'trip_id', 'price_customer'], 'integer'],
            [['status', 'description'], 'string', 'max' => 255],
            // [['driver_id'], 'exist', 'skipOnError' => true, 'targetClass' => Driver::className(), 'targetAttribute' => ['driver_id' => 'id']],
            [['trip_id'], 'exist', 'skipOnError' => true, 'targetClass' => Trip::className(), 'targetAttribute' => ['trip_id' => 'id']],
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
            'driver_id' => 'Lái xe',
            'trip_id' => 'Trip ID',
            'description' => 'Ghi chú',
            'price' => 'Giá',
            'price_customer' => 'Giá báo khách',
            'pickup_images' => 'Ảnh nhận chuyến',
            'dropoff_images' => 'Ảnh giao hoan thành chuyến',

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(Driver::className(), ['id' => 'driver_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrip()
    {
        return $this->hasOne(Trip::className(), ['id' => 'trip_id']);
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
}
