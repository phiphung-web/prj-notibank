<?php

namespace app\models;

use app\component\Util;
use app\models\customerService\CustomerService;
use Yii;

/**
 * This is the model class for table "trip".
 *
 * @property int $id
 * @property int $booking_id
 * @property int $agency_id
 * @property string $created_on
 * @property string $modified_on
 * @property string $customer_name
 * @property string $customer_phone
 * @property string $description
 * @property string $pickup_time
 * @property int $price_bid
 * @property int $price_customer
 * @property int $status
 * @property string $type_of_car
 * @property string $sell_start_time
 * @property int $call_driver
 *
 * @property Bid[] $bs
 */
class Trip extends \yii\db\ActiveRecord
{
    public $source_type;
    public $driver_sub_name;
    public $driver_sub_phone;
    public $driver_sub_bks;
    public $no_auto_price;
    public $driver_sub_type;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trip';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'collected_money_at', 'customer_property', 'pickup_time', 'status', 'sell_start_time', 'agency_id', 'call_back_id', 'send_vip', 'send_gold', 'voucher', 'price_vat', 'money_customer_deposit', 'money_debt_agency', 'service'], 'safe'],
            [['customer_name', 'customer_phone', 'pickup_time', 'price_customer', 'pickup_address', 'destination_address', 'customer_property', 'source_trip', 'round_trip'], 'required'],
            [['display', 'is_have_bill', 'is_collect_money', 'is_auto_price', 'no_auto_price', 'is_toll_fee'], 'boolean'],
            [['status', 'type_of_car'], 'default', 'value' => null],
            [['collected_money'], 'default', 'value' => 0],
            [['driver_debt', 'agency_debt'], 'default', 'value' => 1],
            [['round_trip', 'source_type', 'trip_group_id', 'call_driver', 'source_trip'], 'integer'],
            [['area', 'type_of_car'], 'string', 'max' => 255],
            [['description', 'note_private'], 'string', 'max' => 1000],
            [['customer_name'], 'string', 'max' => 30],
            [['customer_phone'], 'string', 'max' => 15],
            [['price_bid', 'price_customer'], 'string', 'max' => 20],
            [['pickup_address', 'area', 'destination_address'], 'string', 'max' => 80],
            [['price_customer'], 'string', 'max' => 20],
            [['note'], 'string'],
            [['driver_sub_name', 'driver_sub_phone', 'driver_sub_bks', 'driver_sub_type'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_on' => 'Thời gian tạo',
            'modified_on' => 'Modified On',
            'customer_name' => 'Tên khách hàng',
            'customer_phone' => 'SDT khách hàng',
            'description' => 'Mô tả',
            'pickup_time' => 'Thời gian đi',
            'price_bid' => 'Giá bán cho lái xe',
            'money_customer_deposit' => 'Khách cọc',
            'money_debt_agency' => 'Thu hộ đại lý',
            'price_customer' => 'Giá báo khách',
            'status' => 'Trạng thái',
            'pickup_address' => 'Điểm đi',
            'destination_address' => 'Điểm đến',
            'round_trip' => 'Lịch trình',
            'area' => 'Khu vực nhận hàng',
            'display' => 'Hiện',
            'type_of_car' => 'Loại xe',
            'is_have_bill' => 'Lấy hóa đơn',
            'is_collect_money' => 'Thu tiền khách',
            'is_auto_price' => 'Tự động tăng tiền',
            'no_auto_price' => 'Không tự động tăng tiền',
            'flag_driver' => 'Nợ tài xế',
            'collected_money' => 'Chưa thu tiền',
            'sell_start_time' => 'Giờ mở bán',
            'source_trip' => 'Nguồn nhận lịch',
            'agency_id' => 'Đại lý',
            'customer_property' => 'Thuộc tính khách hàng',
            'price_vat' => 'Tiền thuế VAT',
            'service' => 'Dịch vụ',
            'is_toll_fee' => 'Phí cầu đường',
            'note' => 'Ghi chú chuyến',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBs()
    {
        return $this->hasMany(Bid::class, ['trip_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['phone' => 'customer_phone']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->created_on == null) {
            $this->created_on = new \yii\db\Expression('NOW()');
            $this->userid_created = Yii::$app->user->identity->id;
        }
        $this->modified_on = new \yii\db\Expression('NOW()');
        $this->userid_updated = Yii::$app->user->identity->id;
        if (!is_array($this->service)) {
            $decoded = json_decode($this->service, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->service = $decoded;
            } else {
                $this->service = [$this->service];
            }
        }
        $this->service = json_encode($this->service, JSON_UNESCAPED_UNICODE);

        if ($this->status == '') {
            $this->status = 'OPEN';
        }

        $systemConfiguration = SystemConfiguration::getAllConfigurations();

        $schedule = isset($systemConfiguration['auto_schedule_accept']) ? explode(',', $systemConfiguration['auto_schedule_accept']) : [];
        $type_of_car = isset($systemConfiguration['auto_type_of_car_accept']) ? explode(',', $systemConfiguration['auto_type_of_car_accept']) : [];
        // if ($this->no_auto_price || !in_array($this->type_of_car, $type_of_car) || !in_array($this->round_trip, $schedule)) {
        $this->is_auto_price = 0;
        // } else {
        // $this->is_auto_price = 1;
        // }

        // if ($this->status === "OPEN") {
        //     $title = "Thông báo cuốc xe mới";
        //     $con = "Đã có thêm 1 cuốc xe mới trên hệ thống Taxi Tải Tiện Chuyến, vui lòng truy cập app để xem";
        //     $util = new Util();
        //     $util->sendMessage($title, $con);
        // }
        return true;
    }

    public function getCustomerService()
    {
        return $this->hasOne(CustomerService::class, ['trip_id' => 'id']);
    }

    public function getTripReturn()
    {
        return $this->hasOne(TripReturn::class, ['trip_id' => 'id'])->orderBy(['trip_return.created_on' => SORT_DESC]);
    }

    public function getTripGroup()
    {
        return $this->hasOne(TripGroup::class, ['id' => 'trip_group_id']);
    }

    public function getBid()
    {
        return $this->hasOne(Bid::class, ['trip_id' => 'id'])->andWhere(['bid.status' => STATUS_BID_SUCCESS]);
    }

    public function getAgency()
    {
        return $this->hasOne(Agency::class, ['id' => 'agency_id']);
    }

    public function getBidAll()
    {
        return $this->hasMany(Bid::class, ['trip_id' => 'id']);
    }

    public function getTripReturnNoOrder()
    {
        return $this->hasOne(TripReturn::class, ['trip_id' => 'id']);
    }

    public function getTripReturnAll()
    {
        return $this->hasMany(TripReturn::class, ['trip_id' => 'id']);
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => 'userid_created']);
    }

    /**
     * Đếm số bản ghi bị trùng dựa trên các trường chính.
     * So sánh theo: customer_phone, pickup_time, pickup_address, destination_address.
     * Bỏ qua chính bản ghi hiện tại khi đã có id.
     *
     * @param Trip $trip
     * @return int
     */
    public static function findTripDuplicate($trip)
    {
        return Trip::find()->where([
            'pickup_address' => $trip->pickup_address,
            'destination_address' => $trip->destination_address,
            'customer_name' => $trip->customer_name,
            'customer_phone' => $trip->customer_phone,
            'type_of_car' => $trip->type_of_car,
            'pickup_time' => $trip->pickup_time,
            'status' => STATUS_TRIP_OPEN,
        ])->count();
    }

    public static function getCustomerPropertyLabel($phone)
    {
        if (!$phone) return 0;

        $trip = self::find()
            ->select(['customer_property'])
            ->where(['customer_phone' => $phone])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $trip ? (int)$trip->customer_property : 0;
    }
}
