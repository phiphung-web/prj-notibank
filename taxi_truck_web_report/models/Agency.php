<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
class Agency extends ActiveRecord
{
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'agency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['utm_source', 'utm_campaign', 'utm_medium', 'remote_ip', 'url', 'tracking_info', 'presenter', 'keyword', 'percent'], 'safe'],
            [['name', 'address', 'email', 'contact_person'], 'string', 'max' => 255],
            [['phone', 'note', 'price'], 'string'],
            ['phone', 'match', 'pattern' => '/^[0-9]{10,11}$/'],
            [['status', 'send_price', 'agency_debt'], 'integer'],
            [['name', 'address', 'phone'], 'required'],
            ['email', 'email'],
            ['phone', 'validateUniquePhone'],
            ['email', 'validateUniqueEmail'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Đại lý cha',
            'name' => 'Đại lý',
            'address' => 'Địa chỉ',
            'phone' => 'Số điện thoại',
            'email' => 'Email',
            'contact_person' => 'Người liên hệ',
            'note' => 'Ghi chú',
            'qr_code' => 'QrCode',
            'status' => 'Trạng thái',
            'keyword' => 'Từ khóa',
            'price' => 'Tiền hoa hồng',
            'send_price' => 'Gửi giá tới khách hàng',
            'agency_debt' => 'Đại lý nợ tổng đài',
        ];
    }

    public function getAgencyList()
    {
        $agencyList = [];
        $dataAgency = Agency::find()
            ->select(['id', 'name'])
            ->all();

        if (! empty($dataAgency)) {
            $agencyList = ArrayHelper::map($dataAgency, 'id', 'name');
        }

        return $agencyList;
    }

    public function validateUniquePhone($attribute, $params)
    {
        $query = Agency::find()
            ->where(['phone' => $this->phone]);
        if (isset($this->id) && ! empty($this->id)) {
            $query->andWhere(['<>', 'id', $this->id]);
        }
        if ($query->count() > 0) {
            $this->addError($attribute, 'Số điện thoại đã tồn tại trong hệ thống.');
        }
    }

    public function validateUniqueEmail($attribute, $params)
    {
        $query = Agency::find()
            ->where(['email' => $this->email]);
        if (isset($this->id) && ! empty($this->id)) {
            $query->andWhere(['<>', 'id', $this->id]);
        }

        if ($query->count() > 0) {
            $this->addError($attribute, 'Email đã tồn tại trong hệ thống.');
        }
    }
}
