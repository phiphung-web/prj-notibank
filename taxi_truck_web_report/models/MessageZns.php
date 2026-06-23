<?php

namespace app\models;

/**
 * This is the model class for table "message_zns".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $note
 * @property string $pickup_time
 * @property string $status
 * @property string $type_of_car
 * @property string $destination_address
 * @property string $pickup_address
 * @property bool $round_trip
 * @property bool $is_have_bill
 *
 * @property MessageZns[] $bs
 */
class MessageZns extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message_zns';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'reason'], 'safe'],
            [['template_data', 'message', 'phone'], 'string'],
            [['trip_id', 'template_id', 'code'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [];
    }

    public function beforeSave($insert)
    {
        if (! parent::beforeSave($insert)) {
            return false;
        }

        return true;
    }
}
