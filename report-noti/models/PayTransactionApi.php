<?php

namespace app\models;

/**
 * This is the model class for table "pay_transaction".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $description
 * @property int $money
 */
class PayTransactionApi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_transaction';
    }

    public function rules()
    {
        return [
            [['id_pay_transaction', 'money', 'type_bank'], 'required'],
            [['created_on', 'modified_on', 'accepted_at', 'money_before', 'money_after', 'user_id'], 'safe'],
            [['money'], 'integer', 'max' => PRICE_MAX],
            [['money', 'driver_id', 'type', 'admin_id_accepted', 'status', 'account_balance_after', 'account_balance_before'], 'integer'],
            [['description', 'id_pay_transaction', 'phone', 'content_bank', 'type_bank', 'message'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_on' => 'Created On',
            'modified_on' => 'Modified On',
            'description' => 'Mô tả',
            'phone' => 'Số điện thoại tài xế',
            'money' => 'Số tiền',
            'type_bank' => 'Loại ngân hàng',
            'driver_id' => 'Tài xế',
            'createTimeRange' => 'Thời gian nạp tiền',
            'account_balance_after' => 'Số dư sau khi nạp SMS',
            'account_balance_before' => 'Số dư gốc',
        ];
    }


    public function beforeSave($insert)
    {
        if (! parent::beforeSave($insert)) {
            return false;
        }
        if ($this->created_on == null) {
            $this->created_on = new \yii\db\Expression('NOW()');
        }
        $this->modified_on = new \yii\db\Expression('NOW()');

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriver()
    {
        return $this->hasOne(Driver::className(), ['id' => 'driver_id']);
    }
}
