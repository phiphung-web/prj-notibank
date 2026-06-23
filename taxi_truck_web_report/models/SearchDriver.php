<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\data\ActiveDataProvider;
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
use yii\db\Query;

class SearchDriver extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $role;

    public $bks;
    public $color;
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;
    public $sort;
    public $zero_balance;
    public $driver_type;

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
            [['created_on', 'modified_on', 'role', 'driver_rank', 'sort', 'filter', 'is_sub_driver', 'status'], 'safe'],
            [['car_id'], 'default', 'value' => null],
            [['car_id', 'money', 'driver_ban'], 'integer'],
            [['display_name', 'password', 'username'], 'string', 'max' => 255],
            [['car_id'], 'exist', 'skipOnError' => true, 'targetClass' => Car::className(), 'targetAttribute' => ['car_id' => 'id']],
            [['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
            [['username', 'enabled', 'driver_rank', 'sort', 'is_sub_driver', 'zero_balance', 'driver_ban', 'driver_type'], 'safe'],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => DateRangeBehavior::className(),
                'attribute' => 'createTimeRange',
                'dateStartAttribute' => 'createTimeStart',
                'dateEndAttribute' => 'createTimeEnd',
            ],
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
            'color' => 'Màu xe',
            'enabled' => 'Khóa',
            'sort' => 'Bộ lọc',
            'zero_balance' => 'Tiền nạp = 0',
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

        $this->modified_on = new \yii\db\Expression('NOW()');

        if (!$this->money) {
            $this->money = 0;
        }

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
        $query = new Query();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }

        $query->select([
            'driver.*',
            'u.bks',
            'u.car_year',
            'u.note',
            '(SELECT COUNT(*) FROM driver d2 WHERE d2.parent_id = driver.id) AS sub_count'
        ])->from('driver')->innerJoin(['u' => 'car'], 'u.id = car_id');

        if (!empty($this->createTimeRange)) {
            $query->andFilterWhere([
                'BETWEEN',
                'Date(driver.created_on)',
                date('Y-m-d 00:00:00', $this->createTimeStart),
                date('Y-m-d 23:59:59', $this->createTimeEnd),
            ]);
        }

        $query->andWhere(['driver.is_sub_driver' => $this->is_sub_driver ?? DRIVER_TYPE_NORMAL]);

        if ($this->username) {
            $query->andFilterWhere(['=', 'driver.username', $this->username]);
        }

        if ($this->zero_balance) {
            $query->andWhere(['money' => 0]);
        }

        if ($this->driver_ban) {
            $query->andFilterWhere(['driver.driver_ban' => $this->driver_ban]);
        }

        if ($this->status !== '' && $this->status !== null) {
            $this->status == '2'
                ? $query->andWhere(['>=', 'driver.status', 2])
                : $query->andWhere(['driver.status' => $this->status]);
        }

        if (isset($this->driver_rank) && $this->driver_rank == NORMAL_RANK_DRIVER) {
            $query->andWhere(['IN', 'driver.driver_rank', [$this->driver_rank, '']]);
        } elseif (isset($this->driver_rank) && $this->driver_rank != '') {
            $query->andFilterWhere(['=', 'driver.driver_rank', $this->driver_rank]);
        }
        if ($this->driver_type === 'main') {
            $query->andWhere(['parent_id' => 0]);
        }

        if ($this->driver_type === 'sub') {
            $query->andWhere(['!=', 'parent_id', 0]);
            $query->andWhere([
                'parent_id' => (new \yii\db\Query())->select('id')->from('driver')
            ]);
        }

        if (isset($params['register']) && $params['register']) {
            $query->andFilterWhere(['=', 'driver.register', $params['register']]);
        }

        if (isset($this->sort) && $this->sort == 'car_year desc') {
            $query->orderBy(['car_year' => SORT_DESC])->groupBy('driver.username');
        } elseif (isset($this->sort) && $this->sort == 'car_year asc') {
            $query->orderBy(['car_year' => SORT_ASC])->groupBy('driver.username');
        } elseif (isset($params['SearchDriver']['sort']) && $params['SearchDriver']['sort']) {
            $query->orderBy([$params['SearchDriver']['sort'] => SORT_DESC])->groupBy('driver.username');
        } else {
            $query->orderBy(['total_complete' => SORT_DESC, 'parent_id' => SORT_ASC])->groupBy('driver.username');
        }

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchRegister($params)
    {
        $query = $this->find()->select(['driver.*', 'u.bks', 'u.color'])->leftJoin(['u' => 'car'], 'u.id = car_id');
        $this->load($params);
        $where = ['status' => 0, 'is_sub_driver' => DRIVER_TYPE_NORMAL];
        if ($this->username) {
            $where['username'] = $this->username;
        }
        $query->where($where);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchDriverSub($params)
    {
        $query = $this->find()->select(['driver.*', 'u.bks', 'u.color'])->leftJoin(['u' => 'car'], 'u.id = car_id');
        $this->load($params);
        $where = ['driver_ban' => STATUS_DRIVER_BAN_WAIT_REVIEW];
        if ($this->username) {
            $where['username'] = $this->username;
        }
        $query->where($where);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        return $dataProvider;
    }
}
