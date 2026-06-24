<?php

namespace app\models;

use yii\db\ActiveRecord;

class VnWard extends ActiveRecord
{
    public static function tableName()
    {
        return 'vn_ward';
    }

    public function rules()
    {
        return [
            [['wardid', 'name', 'districtid'], 'required'],
            [['wardid', 'districtid'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 191],
        ];
    }

    public function getDistrict()
    {
        return $this->hasOne(VnDistrict::class, ['districtid' => 'districtid']);
    }
}
