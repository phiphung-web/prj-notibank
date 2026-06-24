<?php

namespace app\models;

use app\helpers\MyStringHelper;
use yii\db\Expression;

/**
 * This is the model class for table "pay_transaction".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $description
 * @property int $money
 * @property mixed|null $status
 * @property int|mixed|null $is_disabled
 * @property mixed|Expression|null $disabled_on
 * @property mixed|null $driver_id
 */
class PayTransaction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'accepted_at', 'disabled_on', 'money_before', 'money_after', 'user_id', 'flag'], 'safe'],
            [['money', 'driver_id'], 'required'],
            [['driver_id', 'status', 'admin_id_accepted'], 'integer'],
            [['is_disabled'], 'integer', 'max' => PRICE_MAX],
            [['description'], 'string', 'max' => 255],
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
            'money' => 'VND',
            'driver_id' => 'Tài xế',
            'createTimeRange' => 'Thời gian nạp tiền',
        ];
    }


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->created_on == null)
                $this->created_on = new Expression('NOW()');
            $this->modified_on = new Expression('NOW()');
            $this->money = MyStringHelper::convertStringToInteger($this->money);
            return true;
        }
        return false;
    }

}
