<?php

namespace app\models;

/**
 * This is the model class for table "car".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $bks
 * @property string $color
 * @property string $type
 *
 * @property Driver[] $drivers
 */
class Car extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'note', 'registration_certificate_behind', 'registration_certificate_front', 'album_insurance', 'album_registration_certificate', 'name', 'phone', 'vehicle_plate_image'], 'safe'],
            [['bks', 'license_type', 'car_type'], 'required'],
            [['type_of_car', 'car_year'], 'integer'],
            [['bks', 'color', 'type'], 'string', 'max' => 255],
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
            'bks' => 'Biển kiểm soát',
            'color' => 'Màu',
            'type' => 'Hãng xe',
            'type_of_car' => 'Loại xe',
            'registration_certificate_front' => 'Mặt trước ảnh đăng ký xe ',
            'registration_certificate_behind' => 'Mặt sau ảnh đăng ký xe',
            'album_registration_certificate' => 'Ảnh đăng kiểm',
            'album_insurance' => 'Ảnh bảo hiểm',
            'note' => 'Ghi chú lái xe',
            'car_year' => 'Đời xe',
            'car_type' => 'Loại xe xăng hoặc điện',
            'license_type' => 'Màu biển số',
            'vehicle_plate_image' => 'Ảnh biển số xe'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrivers()
    {
        return $this->hasMany(Driver::className(), ['car_id' => 'id']);
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
