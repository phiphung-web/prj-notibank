<?php

namespace app\models\customerService;

use yii\db\Expression;
use yii\db\Query;

/**
 * This is the model class for table "customer_service".
 *
 * @property int $id
 * @property int $trip_id
 * @property int $customer_id
 * @property int $driver_id
 * @property int|null $type
 * @property string|null $cus_feedback_trip
 * @property string|null $cus_feedback_driver
 * @property string|null $driver_feedback_cus
 * @property int|null $status
 * @property int|null $userid_created
 * @property int|null $userid_updated
 * @property string $created_at
 */
class CustomerService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['trip_id'], 'required'],
            [['trip_id', 'customer_id', 'driver_id', 'userid_created', 'userid_updated'], 'integer'],
            [['created_at', 'cus_feedback_trip', 'cus_feedback_driver', 'driver_feedback_cus', 'point', 'type', 'status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trip_id' => 'ID Chuyến đi',
            'customer_id' => 'ID Khách hàng',
            'driver_id' => 'ID lái xe',
            'type' => 'Loại',
            'cus_feedback_trip' => 'Khách hàng phản hồi tổng đài',
            'cus_feedback_driver' => 'Khách hàng phản hồi lái xe',
            'driver_feedback_cus' => 'Lái xe phản hồi khách hàng',
            'status' => 'Trạng thái',
            'userid_created' => 'ID Người tạo',
            'userid_updated' => 'ID Người cập nhật',
            'created_at' => 'Ngày tạo',
            'point' => 'Điểm số',
        ];
    }

    public function countCustomerService()
    {
        $query = new Query();
        $query->select([
            'SUM(IF(trip.customer_property IN (' . CUSTOMER_PROPERTY_RETURN . ', ' . CUSTOMER_PROPERTY_RETURN_CSKH . ') AND subquery.total_trip < 8, 1, 0)) as customer_rollback',
            'SUM(IF(trip.customer_property NOT IN (' . CUSTOMER_PROPERTY_RETURN . ', ' . CUSTOMER_PROPERTY_RETURN_CSKH . ') AND subquery.total_trip < 8, 1, 0)) as customer_new',
            'SUM(IF(subquery.total_trip >= 8, 1, 0)) as customer_vip',
        ])->from(['trip' => $this->buildSubquery()])
            ->innerJoin(['subquery' => $this->buildSubQueryCountCustomerPhone()], 'subquery.customer_phone = trip.customer_phone');

        return $query->one();
    }

    protected function buildSubquery()
    {
        return (new Query())
            ->select([
                'trip.id',
                'trip.customer_phone',
                'trip.status',
                'trip.pickup_time',
                'trip.source_trip',
                'trip.customer_property',
                'COUNT(trip.customer_phone) AS phone_count',
            ])
            ->from('trip')
            ->innerJoin('bid', 'bid.trip_id = trip.id AND bid.status = "' . STATUS_BID_SUCCESS . '"')
            ->leftJoin('customer_service', 'customer_service.trip_id = trip.id')
            ->where(['trip.status' => ['DONE', 'COMPLETE']])
            ->andWhere([
                'or',
                ['trip.agency_id' => 0],
                ['trip.agency_id' => null],
            ])
            ->andWhere(['>=', 'trip.pickup_time', '2024-01-09 00:00:00'])
            ->andWhere(['<=', 'trip.pickup_time', gmdate('Y-m-d 23:59:59', time() + 7 * 3600)])
            ->andWhere([
                'or',
                ['<', 'customer_service.times', CUSTOMER_SERVICE_TIMES_SUCCESS],
                ['customer_service.times' => null],
            ])->andWhere([
                'or',
                ['customer_service.status' => STATUS_CUSTOMER_SERVICE_NO_PROCESS],
                ['customer_service.status' => null],
            ])
            ->groupBy('trip.id');
    }

    public function buildSubQueryCountCustomerPhone()
    {
        return (new Query())
            ->select([
                'customer_phone',
                'COUNT(trip.id) AS total_trip',
            ])
            ->from('trip')
            ->where(['trip.status' => ['DONE', 'COMPLETE']])
            ->andWhere([
                'or',
                ['trip.agency_id' => 0],
                ['trip.agency_id' => null],
            ])
            ->andWhere(['>', 'trip.pickup_time', new Expression('NOW() - INTERVAL 6 MONTH')])
            ->andWhere(['<=', 'trip.pickup_time', new Expression('DATE_ADD(NOW(), INTERVAL 7 HOUR)')])
            ->groupBy('trip.customer_phone')
            ->orderBy(['total_trip' => SORT_DESC]);
    }
}
