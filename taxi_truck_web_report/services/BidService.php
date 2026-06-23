<?php

namespace app\services;

use app\models\Bid;
use app\models\Trip;
use yii\base\Component;

class BidService extends Component
{
    /**
     * Get the newest bid for a given trip ID.
     *
     * @param int $tripId The ID of the trip for which to retrieve the newest bid.
     * @return Bid|null The newest bid for the specified trip ID, or null if no bid is found.
     */
    public function getBidNewestByTripId($tripId = 0)
    {
        return Bid::find()->where(['trip_id' => $tripId])->orderBy(['created_on' => SORT_DESC])->one();
    }

    public function getBidSuccessByTripId($tripId = 0)
    {
        return Bid::find()->where(['trip_id' => $tripId, 'status' => 'SUCCESS'])->one();
    }

    /**
     * Create a bid for a Zalo trip.
     * @param Trip $model The trip model.
     * @param Bid  $bid   The bid model.
     * @return Bid The created bid.
     */
    public function createBidTripZalo(Trip $model, Bid $bid)
    {
        $bid->status = STATUS_BID_SUCCESS;
        $bid->trip_id = $model->id;
        if ($model->trip_group_id !== null && $model->trip_group_id > 0) {
            $bid->driver_id = 0;
        } else {
            $bid->driver_id = $bid->driver_id != 0 ? $bid->driver_id : 0;
        }
        // Set the bid's price to the bid amount specified in the trip model
        $bid->price = $model->price_bid;
        $bid->save();

        return $bid;
    }
}
