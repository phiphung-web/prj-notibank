<?php

namespace app\models\api;

use app\models\Booking;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * SearchPayTransaction represents the model behind the search form of `app\models\PayTransaction`.
 */
class SearchBookingApi extends Booking
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [];
    }

    public function behaviors()
    {
        return [];
    }

    public function attributeLabels()
    {
        return [];
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
        $query = new Query();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['perpage']) ? $params['perpage'] : 20,
                'page' => isset($params['page']) ? ($params['page'] - 1) : 0,
            ],
        ]);

        $this->load($params);

        if (! $this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->select([
            'booking.*',
            'booking.price_customer as price_booking',
            'trip.price_customer',
            'trip.status as trip_status',
        ])->from('booking')
            ->leftJoin('trip', 'trip.booking_id = booking.id and trip.status IN ("DONE", "COMPLETE", "OPEN", "CANCEL", "EXPIRE")');

        // get booking by agency
        if (Yii::$app->user->can('DAI_LY_ROLE')) {
            $userId = Yii::$app->user->identity->agency_id;
            $query->andWhere(['booking.agency_id' => $userId]);
        }

        $timeConvert = $this->convertString(isset($params['time']) && ! empty($params['time']) ? $params['time'] : '');
        $priceConvert = $this->convertString(isset($params['price']) && ! empty($params['price']) ? $params['price'] : '');

        if (! empty($timeConvert['start']) && ! empty($timeConvert['end'])) {
            $query->andFilterWhere(['BETWEEN', 'created_on', date('Y-m-d 00:00:00', strtotime($timeConvert['start'])), date('Y-m-d 23:59:59', strtotime($timeConvert['end']))]);
        }
        if (! empty($priceConvert['start']) && ! empty($priceConvert['end'])) {
            $query->andFilterWhere(['BETWEEN', 'price_customer', $priceConvert['start'], $priceConvert['end']]);
        }

        if (isset($params['is_have_bill'])) {
            $query->andWhere(['is_have_bill' => $params['is_have_bill']]);
        }
        if (isset($params['keyword']) && ! empty($params['keyword'])) {
            $query->andFilterWhere([
                'or',
                ['like', 'booking.customer_name', $params['keyword']],
                ['like', 'booking.customer_phone', $params['keyword']],
                ['like', 'booking.pickup_address', $params['keyword']],
                ['like', 'booking.destination_address', $params['keyword']],
            ]);
        }
        if (isset($params['status']) && ! empty($params['status'])) {
            $query->andFilterWhere(['booking.status' => $params['status']]);
        }
        $query->orderBy(['booking.id' => SORT_DESC, 'trip.id' => SORT_DESC]);

        return $dataProvider;
    }

    private function convertString($param = '')
    {
        $convert = explode(' - ', $param);

        return [
            'start' => isset($convert[0]) ? trim($convert[0]) : '',
            'end' => isset($convert[1]) ? trim($convert[1]) : '',
        ];
    }
}
