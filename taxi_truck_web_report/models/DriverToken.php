<?php

namespace app\models;

/**
 * This is the model class for table "driver_token".
 *
 * @property int $id
 * @property int $driver_id
 * @property string $token
 * @property string|null $os
 * @property string|null $uuid
 * @property string|null $type
 * @property string $created_on
 * @property string $updated_on
 * @property string|null $expired_on
 */
class DriverToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%driver_token}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['driver_id', 'token'], 'required'],
            [['driver_id'], 'integer'],
            [['created_on', 'expired_on', 'os', 'uuid', 'type'], 'safe'],
            [['token'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'driver_id' => 'Driver ID',
            'token' => 'Token',
            'os' => 'OS',
            'uuid' => 'UUID',
            'type' => 'Type',
            'created_on' => 'Created On',
            'expired_on' => 'Expired On',
        ];
    }
}
