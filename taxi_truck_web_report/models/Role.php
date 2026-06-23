<?php

namespace app\models;

/**
 * This is the model class for table "role".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $name
 *
 * @property DriverRole[] $driverRoles
 * @property Driver[] $drivers
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriverRoles()
    {
        return $this->hasMany(DriverRole::className(), ['role_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDrivers()
    {
        return $this->hasMany(Driver::className(), ['id' => 'driver_id'])->viaTable('driver_role', ['role_id' => 'id']);
    }
}
