<?php

namespace app\models;

class VnProvince extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'vn_province';
    }

    public function rules()
    {
        return [
            [['provinceid', 'name', 'order'], 'required'],
            [['provinceid'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 191],
            [['order'], 'integer'],
        ];
    }

    public function getDistricts()
    {
        return $this->hasMany(VnDistrict::class, ['provinceid' => 'provinceid']);
    }
}
