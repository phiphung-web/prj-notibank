<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use yii\data\ActiveDataProvider;
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
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;

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
            [['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
            [['created_on', 'modified_on'], 'safe'],
            [['display_name', 'phone', 'keyword'], 'string', 'max' => 255],
            [['customer_month', 'customer_booked', 'customer_canceled', 'customer_successfully'], 'integer'],
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
            'modified_on' => 'Ngày sửa',
            'display_name' => 'Tên khách hàng',
            'keyword' => 'Từ khóa',
            'phone' => 'Số điện thoại',
            'customer_month' => 'Số lượt đi trong tháng',
            'customer_booked' => 'Số lượt đặt chuyến',
            'customer_canceled' => 'Số lượt hủy chuyến',
            'customer_successfully' => 'Số lượt hoàn thành',
            'createTimeRange' => 'Khoảng thời gian',
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
        $query = new Query();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            $query->where('0=1');

            return $dataProvider;
        }
        $query->select([
            'trip.*',
            'Date(trip.created_on) AS dtDate',
            'count(*) AS total',
            'SUM( CASE WHEN trip.status = :cancelStatus THEN 1 END ) AS customer_canceled',
            'SUM( CASE WHEN trip.status IN (:completeStatus, :doneStatus) THEN 1 END ) AS customer_successfully',
            'SUM( CASE WHEN trip.status IN (:completeStatus, :doneStatus, :cancelStatus) THEN 1 END ) AS customer_trip_booked',
        ])->from('trip');
        $query->andFilterWhere(['<>', 'status', 'PENDING']);
        if (! empty($this->keyword)) {
            $query->andFilterWhere(['like', 'customer_name', $this->keyword])
                ->orFilterWhere(['like', 'customer_phone', $this->keyword]);
        }

        if (! empty($this->createTimeRange)) {
            $query->andFilterWhere(['>=', 'Date(trip.created_on)', date('Y-m-d 00:00:00', $this->createTimeStart)])
                ->andFilterWhere(['<=', 'Date(trip.created_on)', date('Y-m-d 23:59:59', $this->createTimeEnd)]);
        }

        $query->groupBy(['customer_phone'])
            ->orderBy(['total' => SORT_DESC])
            ->params([
                ':completeStatus' => STATUS_TRIP_COMPLETE,
                ':doneStatus' => STATUS_TRIP_DONE,
                ':cancelStatus' => STATUS_TRIP_CANCEL,
                ':pendingStatus' => STATUS_TRIP_PENDING,
                ':expireStatus' => STATUS_TRIP_EXPIRE,
            ])->limit(20);

        pre($query->createCommand()->sql);

        return $dataProvider;
    }

    public function countAllCustomer()
    {
        return Customer::find()
            ->leftJoin('trip', 'customer.phone = trip.customer_phone')
            ->where(['trip.status' => [STATUS_TRIP_COMPLETE, STATUS_TRIP_DONE]])
            ->count();
    }
}
