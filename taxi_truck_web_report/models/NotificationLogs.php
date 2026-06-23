<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "notification_logs".
 *
 * @property int $id
 * @property int|null $trip_id
 * @property int|null $user_id
 * @property int|null $driver_id
 * @property int|null $type
 * @property string|null $title
 * @property string|null $message
 * @property string|null $message_data
 * @property int|null $status
 * @property int|null $created_on
 * @property int|null $updated_on
 */
class NotificationLogs extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notification_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['trip_id', 'user_id', 'driver_id', 'type', 'status', 'created_on', 'updated_on'], 'integer'],
            [['title', 'message', 'message_data'], 'string'],
            [['type', 'status'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trip_id' => 'Trip ID',
            'user_id' => 'User ID',
            'driver_id' => 'Driver ID',
            'type' => 'Type',
            'title' => 'Title',
            'message' => 'Message',
            'message_data' => 'Message Data',
            'status' => 'Status',
            'created_on' => 'Created On',
            'updated_on' => 'Updated On',
        ];
    }

    /**
     * Gets the user associated with the notification.
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets the driver associated with the notification.
     */
    public function getDriver()
    {
        return $this->hasOne(Driver::class, ['id' => 'driver_id']);
    }

    /**
     * Gets the trip associated with the notification.
     */
    public function getTrip()
    {
        return $this->hasOne(Trip::class, ['id' => 'trip_id']);
    }
}
