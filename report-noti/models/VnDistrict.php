<?php

namespace app\models;

use yii\db\ActiveRecord;

class VnDistrict extends ActiveRecord
{
    public static function tableName()
    {
        return 'vn_district';
    }

    public function rules()
    {
        return [
            [['districtid', 'name', 'provinceid'], 'required'],
            [['districtid', 'provinceid'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 191],
        ];
    }

    // Khai báo mối quan hệ với bảng VnWard
    public function getWards()
    {
        return $this->hasMany(VnWard::class, ['districtid' => 'districtid']);
    }

    // Khai báo mối quan hệ với bảng VnProvince
    public function getProvince()
    {
        return $this->hasOne(VnProvince::class, ['provinceid' => 'provinceid']);
    }
}
