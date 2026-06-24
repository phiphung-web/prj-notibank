<?php

namespace app\models;

use app\helpers\MyStringHelper;

/**
 * This is the model class for table "role".
 *
 * @property int $id
 * @property string $name
 * * @property string $description
 */
class GroupZalo extends \yii\db\ActiveRecord
{
    public $point;
    public $money;
    public $group_zalo_catalogue_name;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_zalo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['note', 'group_zalo_catalogue_name'], 'string'],
            [['status', 'group_zalo_catalogue'], 'integer'],
            [['name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Nguồn bán',
            'note' => 'Chú thích',
            'group_zalo_catalogue' => 'Nhóm nguồn bán',
            'point' => 'Điểm',
            'money' => 'Tiền cước',
            'pickupTimeRange' => 'Khoảng thời gian',
        ];
    }

    public function getTripGroup()
    {
        return $this->hasOne(TripGroup::class, ['group_zalo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupZaloCatalogue()
    {
        return $this->hasOne(GroupZaloCatalogue::class, ['id' => 'group_zalo_catalogue']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            return true;
        }
        return false;
    }
}
