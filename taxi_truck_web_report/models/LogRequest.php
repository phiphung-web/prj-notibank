<?php

namespace app\models;

class LogRequest extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['created_on', 'modified_on', 'accepted_on', 'driver_id', 'message', 'status'], 'safe'],
            [['driver_id', 'status'], 'required'],
        ];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [];
    }
}
