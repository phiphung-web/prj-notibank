<?php

namespace app\models;

class CarRelationship extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'car_relationship';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['car_id', 'driver_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [];
    }
}
