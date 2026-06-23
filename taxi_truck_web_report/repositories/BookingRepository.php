<?php

namespace app\repositories;

use app\models\Booking;
use yii\db\ActiveQuery;

class BookingRepository
{

    public static function baseQuery(): ActiveQuery
    {
        return Booking::find()
            ->select([
                'id',
                'customer_name',
                'customer_phone',
                'pickup_address',
                'destination_address',
                'modified_on',
            ])
            ->where(['status' => STATUS_BOOKING_CREATE]);
    }
}
