<?php

namespace app\models;

use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string $created_on
 * @property string $modified_on
 * @property string $display_name
 * @property string $phone
 */
class Customer extends \yii\db\ActiveRecord
{
    public $keyword;
    public $customer_month;
    public $customer_booked;
    public $customer_canceled;
    public $customer_successfully;
    public $vip;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_on', 'modified_on', 'lastest_trip', 'vip'], 'safe'],
            [['display_name', 'phone', 'keyword'], 'string', 'max' => 255],
            [['customer_month', 'customer_booked', 'customer_canceled', 'customer_successfully'], 'integer'],
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
            'modified_on' => 'Ngày sửa',
            'display_name' => 'Tên khách hàng',
            'keyword' => 'Từ khóa',
            'phone' => 'Số điện thoại',
            'customer_month' => 'Số lượt đi trong tháng',
            'customer_booked' => 'Số lượt đặt chuyến',
            'customer_canceled' => 'Số lượt hủy chuyến',
            'customer_successfully' => 'Số lượt hoàn thành',
            'lastest_trip' => 'Chuyến đi gần nhất',
            'vip' => 'Khách VIP',
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
    public function getTrip()
    {
        return $this->hasMany(Trip::class, ['customer_phone' => 'phone']);
    }

    /**
     * Creates a data provider instance with the search query applied.
     *
     * @param array $params The search parameters.
     *
     * @return ActiveDataProvider The data provider instance.
     */
    public function search($params)
    {
        $query = $this->find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);
        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'display_name', '%' . $this->keyword . '%', false],
                ['LIKE', 'phone', '%' . $this->keyword . '%', false],
            ]);
        }
        $query->orderBy(['total_paid' => SORT_DESC]);

        return $dataProvider;
    }

    public function searchVip($params)
    {
        $sixMonthsAgo = new Expression('NOW() - INTERVAL 6 MONTH');
        $query = (new Query())
            ->select([
                'trip.*',
                'customer_phone as phone',
                'customer_name as display_name',
                'COUNT(trip.id) AS total_trip',
                'SUM(CASE WHEN trip.status IN ("DONE", "COMPLETE") THEN 1 ELSE 0 END) AS total_complete',
                'SUM(CASE WHEN trip.status = "CANCEL" THEN 1 ELSE 0 END) AS total_cancel',
                'SUM(trip.price_customer) AS total_paid',
                'MAX(trip.pickup_time) as lastest_trip',
            ])
            ->from('trip')
            ->where(['trip.status' => ['DONE', 'COMPLETE', 'CANCEL']])
            ->andWhere(['>', 'trip.pickup_time', $sixMonthsAgo])
            ->andWhere(['!=', 'trip.source_trip', SOURCE_TRIP_TYPE_AGENCY])
            ->groupBy('trip.customer_phone')
            ->having(['>', 'total_trip', 8])
            ->orderBy(['total_trip' => SORT_DESC]);
        if (isset($_GET['Customer']['keyword']) && ! empty($_GET['Customer']['keyword'])) {
            $query->andFilterWhere([
                'OR',
                ['LIKE', 'customer_phone', '%' . $_GET['Customer']['keyword'] . '%', false],
                ['LIKE', 'customer_name', '%' . $_GET['Customer']['keyword'] . '%', false],
            ]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (! $this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        return $dataProvider;
    }
}
