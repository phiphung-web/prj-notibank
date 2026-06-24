<?php

namespace app\models;

use yii\db\ActiveRecord;

class AreaConfiguration extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%area_configuration}}';
    }

    public function rules()
    {
        return [
            [['type', 'value'], 'required'],
            [['type'], 'string', 'max' => 255],
            [['value'], 'string'],
            [['created_on', 'modified_on'], 'safe'],
        ];
    }
}
