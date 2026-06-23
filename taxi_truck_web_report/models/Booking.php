<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "booking".
 *
 * @property int $id
 * @property int $admin_id
 * @property int $agency_id
 * @property string $created_on
 * @property string $modified_on
 * @property string $customer_name
 * @property string $customer_phone
 * @property int $price_customer
 * @property string $note
 * @property string $pickup_time
 * @property string $status
 * @property string $type_of_car
 * @property string $destination_address
 * @property string $pickup_address
 * @property bool $round_trip
 * @property bool $is_have_bill
 * @property int $type_reject
 * @property Booking[] $bs
 */
class Booking extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'booking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'paid_driver_on', 'pickup_time', 'call_back_id', 'price_customer', 'price_bid', 'customer_property', 'driver_id_created','service'], 'safe'],
            [['customer_name', 'customer_phone', 'pickup_time', 'price_customer', 'type'], 'required'],
            [['pickup_address', 'destination_address', 'customer_name', 'customer_phone', 'note', 'status', 'area', 'utm_source', 'utm_campaign', 'utm_medium', 'remote_ip', 'url', 'voucher', 'stop_point', 'tracking_info'], 'string'],
            [['is_have_bill', 'is_collect_money', 'is_toll_fee'], 'boolean'],
            [['round_trip', 'type_of_car', 'type_reject', 'type', 'agency_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'agency_id' => 'Đại lý',
            'pickup_address' => 'Điểm đi',
            'destination_address' => 'Điểm đến',
            'round_trip' => 'Lịch trình',
            'is_have_bill' => 'Hóa đơn',
            'customer_name' => 'Tên khách hàng',
            'customer_phone' => 'Số điện thoại',
            'pickup_time' => 'Thời gian đi',
            'type_of_car' => 'Loại xe',
            'note' => 'Ghi chú',
            'status' => 'Trạng thái',
            'created_on' => 'Thời gian tạo',
            'modified_on' => 'Thời gian cập nhật',
            'type_reject' => 'Loại từ chối',
            'type' => 'Nguồn nhận lịch',
            'price_customer' => 'Giá báo khách',
            'price_bid' => 'Lái xe nhận',
            'is_collect_money' => 'Thu tiền khách',
            'area' => 'Khu vực',
            'customer_property' => 'Thuộc tính khách hàng',
            'service' => 'Dịch vụ',
            'is_toll_fee'=> 'Phí cầu đường',
        ];
    }

    public function beforeSave($insert)
    {
        if (! parent::beforeSave($insert)) {
            return false;
        }

        if ($this->status == STATUS_BOOKING_REJECT) {
            $requestCallback = RequestCallBack::findOne(['phone' => $this->customer_phone, 'status' => REQUEST_CALL_BACK_WAITING]);
            if ($requestCallback) {
                $command = Yii::$app->db->createCommand()->update('request_call_back', [
                    'status' => REQUEST_CALL_BACK_CANCEL,
                    'note' => $this->note,
                    'type_reject' => $this->type_reject,
                ], ['id' => $requestCallback->id])
                    ->execute();
            }
        }

        if ($this->round_trip == null) {
            $this->round_trip = 0;
        }
        if (!is_array($this->service)) {
            $decoded = json_decode($this->service, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->service = $decoded;
            } else {
                $this->service = [$this->service];
            }
        }
        $this->service = json_encode($this->service, JSON_UNESCAPED_UNICODE);

        return true;
    }

    /**
     * Find Booking Duplicate
     * @param Booking booking
     * @return int
     */
    public function findBookingDuplicate($booking)
    {
        return Booking::find()->where([
            'status' => STATUS_BOOKING_CREATE,
            'pickup_address' => $booking->pickup_address,
            'destination_address' => $booking->destination_address,
            'customer_name' => $booking->customer_name,
            'customer_phone' => $booking->customer_phone,
            'type_of_car' => $booking->type_of_car,
            'pickup_time' => $booking->pickup_time,
        ])->count();
    }

    /**
     * @return ActiveQuery
     */
    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => 'admin_id']);
    }

    public function getAgency()
    {
        return $this->hasOne(Agency::class, ['id' => 'agency_id']);
    }
}
