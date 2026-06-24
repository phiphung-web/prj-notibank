<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "price_setting".
 *
 * @property int $id
 * @property int $agency_id
 * @property float $percent
 * @property int $price
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int $active
 * @property string $created_at
 * @property string|null $updated_at
 */
class PriceSetting extends ActiveRecord
{
    /**
     * Định nghĩa bảng cơ sở dữ liệu
     */
    public static function tableName()
    {
        return '{{%price_setting}}';
    }

    /**
     * Quy tắc kiểm tra dữ liệu (validation rules)
     */
    public function rules()
    {
        return [
            [['percent'], 'required'],
            [['agency_id', 'active', 'price'], 'integer'],
            [['percent'], 'number'],
            [['start_date', 'end_date'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['created_at', 'updated_at'], 'safe'],
            [['percent'], 'compare', 'compareValue' => 0, 'operator' => '>'],
        ];
    }

    /**
     * Gán nhãn cho các trường
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agency_id' => 'ID Đại lý',
            'percent' => 'Phần trăm tăng',
            'price' => 'Số tiền tăng',
            'start_date' => 'Thời gian bắt đầu',
            'end_date' => 'Thời gian kết thúc',
            'active' => 'Trạng thái kích hoạt',
            'created_at' => 'Ngày tạo',
            'updated_at' => 'Ngày cập nhật',
        ];
    }

    /**
     * Đảm bảo cập nhật timestamp tự động khi tạo/sửa bản ghi
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = date('Y-m-d H:i:s');
        }

        return parent::beforeSave($insert);
    }

    /**
     * Định nghĩa mối quan hệ với bảng `agency` (nếu có)
     */
    public function getAgency()
    {
        return $this->hasOne(Agency::class, ['id' => 'agency_id']);
    }

    /**
     * Kiểm tra nếu một bản ghi đang hoạt động
     */
    public function isActive()
    {
        return $this->active == 1;
    }
}
