<?php

namespace app\models;

/**
 * This is the model class for table "trip_delete".
 *
 * @property int $id
 * @property string $name
 * * @property string $description
 */
class TripDelete extends \yii\db\ActiveRecord
{
    public $driver_sub_name;
    public $driver_sub_phone;
    public $driver_sub_bks;
    public $driver_sub_type;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trip_delete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'id', 'is_rollback'], 'safe'],
            [['data_trip', 'note'], 'string'],
            [['user_id'], 'integer'],
            [['is_rollback'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'note' => 'Ghi chú',
            'user_id' => 'Người xóa',
            'data_trip' => 'Dữ liệu chuyến xe bị xóa',
            'is_rollback' => 'Đã được khôi phục',
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

        return true;
    }

    /**
     * Search for a trip by its ID and retrieve related data.
     *
     * @param int $id The ID of the trip to search for.
     * @return array|null An array containing trip and related data or null if not found.
     */
    public function searchTripById($id)
    {
        $query = Trip::find()->where(['trip.id' => $id]);

        $query->joinWith(['tripReturnAll AS trip_return'])->andWhere(['OR', ['trip_return.trip_id' => null], ['trip_return.trip_id' => new \yii\db\Expression('trip.id')]]);

        $query->joinWith(['bidAll'])->andWhere(['OR', ['bid.trip_id' => null], ['bid.trip_id' => new \yii\db\Expression('trip.id')]]);

        $query->leftJoin('driver_sub', 'bid.driver_id = driver_sub.driver_id AND trip.id = driver_sub.trip_id');
        $query->select([
            'trip.*',
            'driver_sub_name' => 'driver_sub.name',
            'driver_sub_phone' => 'driver_sub.phone',
            'driver_sub_bks' => 'driver_sub.bks',
            'driver_sub_type' => 'driver_sub.type',
        ]);
        $query->joinWith(['tripGroup', 'tripGroup.groupZalo', 'tripGroup.groupZaloSeller'])->andWhere(['OR', ['trip_group.id' => null], ['trip_group.id' => new \yii\db\Expression('trip.trip_group_id')]]);
        $result = $query->asArray()->one();

        return $result;
    }
}
