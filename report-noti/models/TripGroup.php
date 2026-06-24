<?php

namespace app\models;

/**
 * This is the model class for table "role".
 *
 * @property int $id
 * @property string $name
 * * @property string $description
 */
class TripGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trip_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'id'], 'safe'],
            [['driver_name', 'driver_phone'], 'string', 'max' => 255],
            [['price', 'license_plates'], 'string', 'max' => 20],
            [['group_zalo_id', 'type', 'id', 'zalo_seller_id', 'type_of_car'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'group_zalo_id' => 'Nhóm bán',
            'driver_name' => 'Tên lái xe',
            'driver_phone' => 'Số điện thoại lái xe',
            'price' => 'Tiền lái xe nhận',
            'license_plates' => 'Biển số xe',
            'type_of_car' => 'Loại xe',
            'zalo_seller_id' => 'Người bán',
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

    public function getTrip()
    {
        return $this->hasOne(Trip::class, ['trip_group_id' => 'id']);
    }
    public function getGroupZalo()
    {
        return $this->hasOne(GroupZalo::class, ['id' => 'group_zalo_id']);
    }

    public function getGroupZaloSeller()
    {
        return $this->hasOne(GroupZaloSeller::class, ['id' => 'zalo_seller_id']);
    }
}
