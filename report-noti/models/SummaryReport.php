<?php

namespace app\models;

class SummaryReport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'summary_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'dt_date', 'total_booking', 'total_booking_cancel', 'total_booking_waiting', 'total_booking_create', 'total_booking_confirm', 'total_trip', 'total_trip_cancel', 'total_trip_complete', 'total_trip_done', 'total_trip_pending', 'total_trip_create', 'customer_price', 'driver_price', 'revenue', 'source_trip', 'spend_price'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [];
    }
}
