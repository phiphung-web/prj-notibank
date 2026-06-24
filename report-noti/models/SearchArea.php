<?php

namespace app\models;

use yii\data\ActiveDataProvider;

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
class SearchArea extends \yii\db\ActiveRecord
{
    public $keyword;

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
            [['street', 'area_name', 'description', 'keyword'], 'string'],
            [['created_on', 'updated_on'], 'safe'],
            [['provinceid', 'districtid'], 'string', 'max' => 20],
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
            'keyword' => 'Từ khóa',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProvince()
    {
        return $this->hasOne(VnProvince::class, ['provinceid' => 'provinceid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict()
    {
        return $this->hasOne(VnDistrict::class, ['districtid' => 'districtid']);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Area::find()->joinWith(['vnProvince', 'vnDistrict']);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }
        if ($this->provinceid && ! empty($this->provinceid)) {
            $query->where(['area.provinceid' => $this->provinceid]);
        }
        if ($this->districtid && ! empty($this->districtid)) {
            $query->where(['area.districtid' => $this->districtid]);
        }

        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'area_name', '%' . $this->keyword . '%', false],
                ['LIKE', 'description', '%' . $this->keyword . '%', false],
                ['LIKE', 'street', '%' . $this->keyword . '%', false],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }
}
