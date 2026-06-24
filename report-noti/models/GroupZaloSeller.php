<?php

namespace app\models;

/**
 * This is the model class for table "role".
 *
 * @property int $id
 * @property string $name
 * * @property string $description
 */
class GroupZaloSeller extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_zalo_seller';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'id' , 'group_zalo_catalogue_id'],'safe'],
            [['name' ], 'string', 'max' => 255],
            [['name' ], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Tên người bán',
            'group_zalo_catalogue_id' => 'Nhóm nguồn bán',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroupZalo()
    {
        return $this->hasMany(GroupZalo::class, ['group_zalo_catalogue' => 'id']);
    }
}
