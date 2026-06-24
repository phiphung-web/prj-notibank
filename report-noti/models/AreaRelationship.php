<?php

namespace app\models;

/**
 * This is the model class for table "area_relationship".
 *
 * @property int $id
 * @property int $area_id
 * @property string $districtid
 * @property string $provinceid
 * @property int|null $type_of_car
 * @property string|null $street
 * @property string|null $time
 * @property string|null $schedule
 * @property float|null $price
 * @property float|null $roundtrip_price
 * @property string|null $description
 * @property string|null $created_on
 * @property string|null $updated_on
 */
class AreaRelationship extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'area_relationship';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['area_id', 'districtid', 'provinceid'], 'required'],
            [['area_id', 'type_of_car', 'address'], 'integer'],
            [['street', 'description'], 'string'],
            [['price', 'roundtrip_price'], 'number'],
            [['created_on', 'updated_on'], 'safe'],
            [['districtid', 'provinceid'], 'string', 'max' => 20],
            [['time', 'schedule'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'area_id' => 'Mã khu vực',
            'districtid' => 'Quận/Huyện',
            'provinceid' => 'Tỉnh/Thành phố',
            'type_of_car' => 'Loại xe',
            'street' => 'Tên đường',
            'time' => 'Thời gian',
            'schedule' => 'Lịch trình',
            'price' => 'Giá thu khách',
            'roundtrip_price' => 'Giá khứ hồi',
            'description' => 'Mô tả',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArea()
    {
        return $this->hasOne(Area::class, ['id' => 'area_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAreaConfigurationByAddress()
    {
        return $this->hasOne(AreaConfiguration::class, ['id' => 'address']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAreaConfigurationByTime()
    {
        return $this->hasOne(AreaConfiguration::class, ['id' => 'time']);
    }
}
