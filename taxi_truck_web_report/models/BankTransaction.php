<?php

namespace app\models;

use app\helpers\MyStringHelper;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%bank_transaction}}".
 *
 * @property int $id
 * @property int $type_bank Loại ngân hàng
 * @property int $admin_id ID của admin
 * @property string|null $token_tele Token Telegram
 * @property string|null $chat_tele Chat ID Telegram
 * @property int $account_balance Số dư tài khoản
 * @property string $created_on Thời gian tạo
 * @property string $updated_on Thời gian cập nhật
 */
class BankTransaction extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%bank_transaction}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_bank', 'admin_id'], 'required'],
            [['type_bank', 'admin_id', 'account_balance', 'check_driver'], 'integer'],
            [['created_on', 'updated_on', 'account_holder', 'account_number', 'is_display'], 'safe'],
            [['token_tele', 'chat_tele'], 'string', 'max' => 255],
            [['account_balance'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_bank' => 'Loại ngân hàng',
            'admin_id' => 'ID của admin',
            'token_tele' => 'Token Telegram',
            'chat_tele' => 'Chat ID Telegram',
            'account_balance' => 'Số dư tài khoản',
            'created_on' => 'Thời gian tạo',
            'updated_on' => 'Thời gian cập nhật',
            'check_driver' => 'Nạp tiền lái xe',
            'qrcode_path' => 'Ảnh QRCode',
            'account_holder' => 'Chủ tài khoản',
            'account_number' => 'Số tài khoản',
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
        $this->updated_on = new \yii\db\Expression('NOW()');
        $this->account_balance = MyStringHelper::convertStringToInteger($this->account_balance);

        return true;
    }

    /**
     * Thiết lập quan hệ với bảng Admin.
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => 'admin_id']);
    }
}