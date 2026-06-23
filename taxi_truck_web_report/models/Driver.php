<?php

namespace app\models;

use app\helpers\MyStringHelper;
use app\services\SystemConfigurationService;
use Yii;

/**
 * This is the model class for table "driver".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $display_name
 * @property string $password
 * @property string $username
 * @property int $car_id
 *
 * @property Bid[] $bs
 * @property Car $car
 * @property DriverRole[] $driverRoles
 * @property Role[] $roles
 */

use yii\data\ActiveDataProvider;

/**
 * @property mixed|null $token_fcm
 * @property mixed|null $id
 * @property int $money
 * @property mixed|null $display_name
 */
class Driver extends \yii\db\ActiveRecord
{
    const STATUS_NEWBIE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_LOCK = 2;
    const STATUS_NO_SEND_NOTIFICATION = 0;
    const STATUS_SEND_NOTIFICATION = 1;

    public $role;
    public $bks;

    public static function tableName()
    {
        return 'driver';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'accepted_on', 'admin_id_accepted', 'role', 'driver_rank', 'folder_image', 'avatar', 'status', 'certificate_type', 'activity_area', 'referral_code', 'point', 'reason', 'english', 'parent_id', 'is_sub_driver', 'driver_license_front', 'driver_license_behind', 'allow_notification'], 'safe'],
            [['username', 'password', 'display_name', 'money', 'email', 'identity_front_image', 'identity_back_image'], 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            [['car_id'], 'default', 'value' => null],
            [['car_id', 'money', 'driver_ban'], 'integer'],
            [['enabled', 'register'], 'boolean'],
            [['display_name', 'password', 'username', 'email'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_on' => 'Ngày tạo',
            'modified_on' => 'Lần sửa cuối',
            'display_name' => 'Họ và Tên',
            'password' => 'Mật khẩu',
            'username' => 'Số điện thoại',
            'car_id' => 'Car ID',
            'money' => 'Tiền nạp',
            'driver_ban' => 'Tài xế có nhiều xe',
            'bks' => 'BKS',
            'enabled' => 'Hoạt động',
            'driver_rank' => 'Hạng',
            'role' => 'Nhóm tài khoản',
            'certificate_type' => 'Loại bằng lái ô tô',
            'activity_area' => 'Khu vực hoạt động (Hà Nội)',
            'referral_code' => 'Mã giới thiệu',
            'point' => 'Điểm',
            'reason' => 'Lý do khóa',
            'english' => 'Trình độ tiếng Anh',
            'avatar' => 'Ảnh đại diện',
            'status' => 'Trạng thái',
            'parent_id' => 'Tài xế chính',
            'is_sub_driver' => 'Tài khoản ảo',
            'driver_license_front' => 'Ảnh bằng lái xe phía trước',
            'driver_license_behind' => 'Ảnh bằng lái xe phía sau',
            'allow_notification' => 'Cho phép nhận thông báo',
            'identity_front_image' => 'Ảnh CCCD/CMND phía trước',
            'identity_back_image' => 'Ảnh CCCD/CMND phía sau',
        ];
    }

    public function toString()
    {
        return $this->display_name . ' (' . $this->username . ')';
    }

    public function getTop()
    {
        $query = "select driver_id, sum(price) as sum, count(id) as count from bid where status = 'SUCCESS'  group by  driver_id";
        $command = Yii::$app->db->createCommand($query);
        $resultSet = $command->query();

        return $resultSet->readAll();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBs()
    {
        return $this->hasMany(Bid::className(), ['driver_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'car_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDriverRoles()
    {
        return $this->hasMany(DriverRole::className(), ['driver_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Role::className(), ['id' => 'role_id'])->viaTable('driver_role', ['driver_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($this->created_on == null) {
            $this->created_on = new \yii\db\Expression('NOW()');
        }

        if ($this->parent_id == null) {
            $this->parent_id = 0;
        }

        if ($this->status == 0 || $this->status == 1) {
            $this->enabled = 1;
        } else {
            $this->enabled = 0;
        }

        $this->modified_on = new \yii\db\Expression('NOW()');

        if (!$this->money) {
            $this->money = 0;
        }

        $this->point = MyStringHelper::convertStringToInteger($this->point);
        return true;
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
        $query = $this->find()->select(['driver.*', 'u.bks as bks'])->leftJoin(['u' => 'car'], 'u.id = car_id');
        $this->load($params);
        if ($this->driver_ban) {
            $query->where('driver_ban =' . $this->driver_ban);
        }
        if ($this->username) {
            $query->where('username =' . $this->username);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

    public function getTripReturn()
    {
        return $this->hasOne(TripReturn::class, ['driver_id' => 'id']);
    }

    public function getDriverSub()
    {
        return $this->hasOne(DriverSub::class, ['driver_id' => 'id']);
    }

    public function getDriverVip()
    {
        $system = SystemConfiguration::find()->asArray()->all();
        $systemConfigurationService = new SystemConfigurationService();
        $pointVip = $systemConfigurationService->getConfigByKeyword('point_vip');
        $temp = array_column($system, 'content', 'keyword');

        $data = Driver::find()
            ->select([
                'driver.id',
                'driver.username',
                'driver.display_name',
                'car.bks',
                'car.car_year',
                'COUNT(DISTINCT trip.id) AS trip_count',
            ])
            ->innerJoin('car', 'car.id = driver.car_id')
            ->leftJoin('bid', 'bid.driver_id = driver.id')
            ->leftJoin('trip', 'bid.trip_id = trip.id')
            ->innerJoin('customer_service', 'customer_service.driver_id = driver.id')
            ->where(['>=', 'car.car_year', $temp['driver_year']])
            ->andWhere(['>=', 'driver.point', $pointVip])
            ->groupBy('driver.id')
            ->having(['>=', 'trip_count', $temp['driver_trip_count']])
            ->asArray()
            ->all();

        return $data;
    }
}