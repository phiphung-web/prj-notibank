<?php

namespace app\models;

use DateTime;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "car".
 *
 * @property int $id
 * @property int $phone
 * @property int status
 * @property int type_reject
 * @property string $note
 * @property DateTime $created_on
 * @property DateTime $modified_on
 */
class RequestCallBack extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'request_call_back';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
      ['phone', 'required'],
      ['phone', 'match', 'pattern' => '/^[0-9]{10,11}$/'],
      ['type_reject', 'default', 'value' => null],
      ['type_reject', 'integer', 'min' => 1, 'tooSmall' => '{attribute} chưa được chọn'],
      [['note', 'utm_source', 'utm_campaign', 'utm_medium', 'remote_ip', 'url', 'tracking_info'], 'string'],
      [['created_on', 'modified_on', 'trip_id', 'source_trip'], 'safe'],
    ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
      'id' => 'ID',
      'phone' => 'Số điện thoại',
      'status' => 'Trạng thái',
      'type_reject' => 'Loại từ chối',
      'note' => 'Lý do hủy',
      'created_on' => 'Thời gian tạo',
      'modified_on' => 'Cập nhật vào lúc',
      'source_trip' => 'Nguồn nhận yêu cầu',
    ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (! parent::beforeSave($insert)) {
            return false;
        }

        if ($this->created_on == null) {
            $this->created_on = new Expression('NOW()');
        }

        $this->modified_on = new Expression('NOW()');

        return true;
    }

    /**
     * get count number phone waiting
     */
    public function countNumberPhoneWaiting()
    {
        return RequestCallBack::find()->where([
      'status' => REQUEST_CALL_BACK_WAITING,
    ])->count();
    }

    /**
     * search
     */
    public function search($params)
    {
        $phone = ! empty($params['RequestCallBack']) ? $params['RequestCallBack']['phone'] : '';
        $status = ! empty($params['RequestCallBack']) ? $params['RequestCallBack']['status'] : REQUEST_CALL_BACK_WAITING;

        $query = $this->find()->where(['status' => $status]);

        if ($phone) {
            $query = $query->where(['phone' => $phone]);
        }

        return new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 20,
      ],
    ]);
    }
}
