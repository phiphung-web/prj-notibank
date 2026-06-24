<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "role".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property string $contact_person
 * @property string $note
 * @property string $token
 * @property string $qr_code
 * @property string $status
 */
class Location extends ActiveRecord
{
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'location';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
      [['latitude', 'longitude'], 'double'],
      [['display_name'], 'string', 'max' => 65535],
      [['latitude', 'longitude', 'display_name'], 'required'],
    ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
      'latitude' => 'Vĩ độ',
      'longitude' => 'Kinh độ',
      'display_name' => 'Địa điểm',
    ];
    }

    public function getListLocation($params)
    {
        $keyword = isset($params['keyword']) ? $params['keyword'] : '';

        return Location::find()->where(['like', 'display_name', $keyword])->all();
    }
}
