<?php

namespace app\models;

/**
 * This is the model class for table "voucher".
 *
 * @property int $id
 * @property string $code
 * @property int|null $quantity
 * @property float $value
 * @property int|null $type
 * @property int|null $is_send
 * @property int|null $status
 * @property string|null $expired_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Voucher extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'voucher';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'value'], 'required'],
            [['quantity', 'type', 'status'], 'integer'],
            [['value'], 'number'],
            [['is_send'], 'boolean'],
            [['expired_at', 'created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Mã voucher',
            'quantity' => 'Số lượng',
            'value' => 'Giá trị',
            'type' => 'Loại',
            'is_send' => 'Trạng thái gửi',
            'status' => 'Trạng thái',
            'expired_at' => 'Thời gian hết hạn',
            'created_at' => 'Thời gian tạo',
            'updated_at' => 'Thời gian cập nhật',
        ];
    }
}
