<?php

namespace app\models;

/**
 * This is the model class for table "area".
 *
 * @property int $id
 * @property string $provinceid
 * @property string $districtid
 * @property string|null $street
 * @property string|null $area_name
 * @property string|null $description
 * @property string $created_on
 * @property string $updated_on
 */
class Area extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'area';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['provinceid', 'districtid', 'area_name'], 'required'],
            [['street', 'area_name', 'description', 'price_list'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['provinceid', 'districtid'], 'string', 'max' => 20],
            [['provinceid'], 'compare', 'compareValue' => 0, 'operator' => '!=', 'message' => 'Xin vui lòng chọn tỉnh/thành phố.'],
            [['districtid'], 'compare', 'compareValue' => 0, 'operator' => '!=', 'message' => 'Xin vui lòng chọn quận/huyện.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'provinceid' => 'Tỉnh/Thành phố',
            'districtid' => 'Quận/Huyện',
            'street' => 'Tên đường',
            'area_name' => 'Tên khu vực',
            'description' => 'Mô tả ngắn',
            'created_on' => 'Created On',
            'updated_on' => 'Updated On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVnProvince()
    {
        return $this->hasOne(VnProvince::class, ['provinceid' => 'provinceid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVnDistrict()
    {
        return $this->hasOne(VnDistrict::class, ['districtid' => 'districtid']);
    }
}
