<?php

namespace app\services;

use app\models\Booking;
use app\models\Customer;
use app\models\RequestCallBack;
use Yii;
use app\repositories\BookingRepository;
use yii\db\ActiveQuery as YiiActiveQuery;

class BookingService
{
     public const DEFAULT_PAGINATION_LIMIT = 50; // cho số vào const

    public function findOne($id)
    {
        return Booking::findOne($id);
    }

    /**
     * Retrieve all bookings by source and creation date.
     *
     * @param string $date The date to filter bookings.
     * @param string $source The source type of the bookings.
     * @return array The array of bookings matching the criteria.
     */
    public function getAllBookingBySourceAndCreatedOn($date, $source)
    {
        return Booking::find()
            ->select(['booking.id', 'booking.url', 'trip.booking_id'])
            ->leftJoin('trip', 'trip.booking_id = booking.id AND trip.status not in ("CANCEL", "PENDING")')
            ->where(['booking.type' => $source])
            ->andFilterWhere(['like', 'booking.created_on', $date])
            ->groupBy('booking.id')
            ->orderBy(['booking.id' => SORT_DESC])
            ->asArray()
            ->all();
    }

    public function findByCustomerPhone($customerPhone)
    {
        return Booking::find()
            ->where([
                'customer_phone' => $customerPhone,
            ])
            ->andWhere([
                'or',
                ['status' => STATUS_BOOKING_CREATE],
                ['status' => STATUS_BOOKING_WAITING],
            ])
            ->one();
    }

    public function updatePaidDriverOn($bookingId)
    {
        return Yii::$app
            ->db
            ->createCommand()
            ->update('booking', ['paid_driver_on' => date('Y-m-d H:i:s')], ['id' => $bookingId])
            ->execute();
    }

    public function storeBooking(Booking $model, $method = '')
    {
        if (!empty(Yii::$app->user->identity->agency_id)) {
            $model->agency_id = Yii::$app->user->identity->agency_id;
            $model->type = SOURCE_TRIP_TYPE_AGENCY;
        }
        $model->call_back_id = (isset($_GET['idCallBack']) && !empty($_GET['idCallBack'])) ? $_GET['idCallBack'] : 0;
        $model->price_customer = str_replace('.', '', $model->price_customer);
        if (!isset($model->status) || empty($model->status)) {
            $model->status = 'CREATE';
        }
        if ($method == 'create') {
            $model->admin_id = Yii::$app->user->id;
            $model->created_on = date('Y-m-d H:i:s');
        } else {
            $model->modified_on = date('Y-m-d H:i:s');
        }

        return $model;
    }

    public function updateCancelRequestCallBack()
    {
        $modelRequestCallBack = new RequestCallBack();
        $dataRequestCallBack = $modelRequestCallBack->find()->where([
            'id' => $_GET['idCallBack'],
            'status' => REQUEST_CALL_BACK_WAITING,
        ])->one();
        if ($dataRequestCallBack) {
            $dataRequestCallBack->status = REQUEST_CALL_BACK_CONFIRM;
            $dataRequestCallBack->save();
        }
    }

    /**
     * Tạo customer từ booking nếu chưa tồn tại
     * @param Booking $booking
     * @return Customer|null
     */
    public function createCustomerFromBooking($booking)
    {
        if (!empty($booking->customer_phone) && !empty($booking->customer_name)) {
            $customer = Customer::find()->where(['LIKE', 'phone', $booking->customer_phone])->one();
            if (!$customer instanceof Customer) {
                $customer = new Customer();
                $customer->phone = trim($booking->customer_phone);
            }
            $customer->display_name = $booking->customer_name;
            $customer->save();

            return $customer;
        }
        return null;
    }

    /**
     * Lấy danh sách booking mới kể từ $since (UNIX timestamp).
     */
    public function getNewBookings(): array
    {
        $rows = \app\repositories\BookingRepository::baseQuery()
            ->where(['status' => STATUS_BOOKING_CREATE])
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all();

        return [
            'serverTime' => time(),
            'data' => array_map(static function (array $r): array {
                return [
                    'id'            => (int)$r['id'],
                    'customer_name' => $r['customer_name'],
                    'phone'         => $r['customer_phone'],
                    'pickup'        => $r['pickup_address'],
                    'dropoff'       => $r['destination_address'],
                    'modified_unix' => $r['modified_on'] ? strtotime($r['modified_on']) : null,
                ];
            }, $rows),
        ];
    }



}
