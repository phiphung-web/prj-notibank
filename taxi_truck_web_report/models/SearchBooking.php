<?php

namespace app\models;

use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * SearchPayTransaction represents the model behind the search form of `app\models\PayTransaction`.
 */
class SearchBooking extends Booking
{
    public $createTimeRange;
    public $createTimeStart;
    public $createTimeEnd;
    public $type_reject;
    public $keyword;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id','type'], 'integer'],
            [['created_on', 'modified_on'], 'safe'],
            [['round_trip', 'is_have_bill'], 'boolean'],
            [['createTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
            [['status', 'type_reject', 'keyword'], 'string'],
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

    public function attributeLabels()
    {
        return [
            'admin_id' => 'Người tạo',
            'agency_id' => 'Đại lý',
            'createTimeRange' => 'Thời gian đi',
            'status' => 'Trạng thái',
            'type_reject' => 'Loại từ chối',
            'keyword' => 'Từ khóa',
            'type' => 'Nguồn',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Booking::find()->joinWith('admin')
            ->joinWith('agency');

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_on' => $this->created_on,
            'modified_on' => $this->modified_on,
        ]);

        //
        if (! Yii::$app->user->can('DAI_LY_ROLE') && ! empty($params['SearchBooking']['agency_id'])) {
            $query->andWhere(['booking.agency_id' => $params['SearchBooking']['agency_id']]);
        }

        // get booking by agency
        if (Yii::$app->user->can('DAI_LY_ROLE')) {
            $userId = Yii::$app->user->identity->agency_id;
            $query->andWhere(['booking.agency_id' => $userId]);
        }

        if ($this->status != STATUS_BOOKING_WAITING && $this->status != STATUS_BOOKING_CREATE && ! empty($this->status)) {
            $query->andFilterWhere(['BETWEEN', 'created_on', date('Y-m-d 00:00:00', $this->createTimeStart), date('Y-m-d 23:59:59', $this->createTimeEnd)]);
        } elseif ($this->status == STATUS_BOOKING_WAITING) {
            $query->andFilterWhere(['BETWEEN', 'pickup_time', date('Y-m-d 00:00:00', $this->createTimeStart), date('Y-m-d 23:59:59', $this->createTimeEnd)]);
        } else {
            $query->andFilterWhere(['>=', 'pickup_time', date('Y-m-d 00:00:00', $this->createTimeStart)]);
        }
        if (empty($this->status)) {
            $query->andFilterWhere(['booking.status' => 'CREATE']);
        } elseif ($this->status === 'ALL' || empty($this->status)) {
            $query->andWhere(['in', 'booking.status', [STATUS_BOOKING_CREATE,STATUS_BOOKING_WAITING,STATUS_BOOKING_CONFIRM,STATUS_BOOKING_REJECT]]); //, STATUS_BOOKING_WAITING
        } else {
            $query->andFilterWhere(['booking.status' => $this->status]);
        }
        if (! empty($this->type_reject) && $this->type_reject != 0) {
            $query->andWhere(['type_reject' => $this->type_reject]);
        }

        if (! empty($this->type) && $this->type != 0) {
            $query->andWhere(['type' => $this->type]);
        }

        if (! empty($this->keyword)) {
            $query->andFilterWhere([
                'or',
                ['like', 'customer_name', $this->keyword],
                ['like', 'customer_phone', $this->keyword],
                ['like', 'round_trip', $this->keyword],
            ]);
        }
        $query->orderBy(['id' => SORT_DESC]);

        return $dataProvider;
    }

    /**
     * Count the booking create today
     * @return int
     */
    public function countBookingCreate()
    {
        $query = Booking::find()
            ->where(['in', 'status', [STATUS_BOOKING_CREATE]])
            ->andFilterWhere(['>=', 'pickup_time', gmdate('Y-m-d 00:00', time() + 7 * 3600)]);

        if (Yii::$app->user->can('DAI_LY_ROLE')) {
            $userId = Yii::$app->user->identity->agency_id;
            $query->andWhere(['booking.agency_id' => $userId]);
        }

        return $query->count();
    }

    /**
     * Count the booking waiting today
     * @return int
     */
    public function countBookingWaiting()
    {
        return Booking::find()->where(['in', 'status', [STATUS_BOOKING_WAITING]])->andFilterWhere(['between', 'pickup_time', gmdate('Y-m-d 00:00', time() + 7 * 3600), gmdate('Y-m-d 23:59:59', time() + (2 * 24 * 3600) + 7 * 3600)])->count();
    }
}
