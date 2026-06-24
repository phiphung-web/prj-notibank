<?php

namespace app\models;

/**
 * This is the model class for table "driver".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $display_name
 * @property string $password
 * @property string $username
 * @property int $car_id
 *
 * @property Bid[] $bs
 * @property Car $car
 * @property DriverRole[] $driverRoles
 * @property Role[] $roles
 */

class DriverSub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    public $role;

    public static function tableName()
    {
        return 'driver_sub';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'safe'],
            [['name', 'phone', 'bks', 'type'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên lái xe phụ',
            'phone' => 'Số điện thoại',
            'bks' => 'Biển kiểm soát',
            'type' => 'Loại xe',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(Driver::class, ['id' => 'driver_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrip()
    {
        return $this->hasOne(Trip::class, ['id' => 'trip_id']);
    }
}
