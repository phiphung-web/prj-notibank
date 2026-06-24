<?php

namespace app\services;

use app\models\Trip;
use app\models\TripGroup;
use yii\base\Component;

class TripGroupService extends Component
{
    /**
     * Get tripgroup
     * @param Trip $trip
     * @return TripGroup $modeltripGroup
     */
    public function getTripGroup(Trip $model)
    {
        return $model->trip_group_id ? TripGroup::findOne($model->trip_group_id) : new TripGroup();
        ;
    }
}
