<?php

namespace app\services;

use app\models\Car;
use app\models\CarRelationship;
use yii\base\Component;
use yii\db\Query;

class CarService extends Component
{
    public function getCarSub($driver)
    {
        $query = new Query();
        $data = $query->select([
            'u.*',
            'car_relationship.id as id_relation',
        ])->from('car_relationship')
            ->innerJoin(['u' => 'car'], 'u.id = car_id')->where(['driver_id' => $driver])->all();

        return $data;
    }

    public function insertCar($carModel)
    {
        $carModel->save();

        return $carModel;
    }

    public function insertCarSub($carSub, $driver)
    {
        try {
            foreach ($carSub as $key => $value) {
                $param['Car'] = $value;
                $car = new Car();
                $car->load($param);
                $car->save();

                if ($car->id > 0) {
                    $carRelationship = new CarRelationship();
                    $carRelationship->car_id = $car->id;
                    $carRelationship->driver_id = $driver->id;
                    $carRelationship->save();
                }
            }
        } catch (\Throwable $th) {
            pre($th);
        }
    }

    public function updateCarSub($carSub, $driver)
    {
        try {
            $idList = [];
            foreach ($carSub as $key => $value) {
                $param['Car'] = $value;
                $car = (isset($value['id']) && ! empty($value['id']) ? Car::findOne($value['id']) : new Car());
                $car->load($param);
                $car->save();

                if ($car->id > 0) {
                    $carRelationship = (isset($value['id_relation']) && ! empty($value['id_relation']) ? CarRelationship::findOne($value['id_relation']) : new CarRelationship());
                    $carRelationship->car_id = $car->id;
                    $carRelationship->driver_id = $driver->id;
                    $carRelationship->save();
                }
                $idList[] = $car->id;
            }
            $carList = CarRelationship::find()->where(['driver_id' => $driver->id])->asArray()->andWhere(['not in', 'car_id', $idList])->asArray()->all();
            if (isset($carList) && is_array($carList) && count($carList)) {
                $carIdList = [];
                foreach ($carList as $value) {
                    $carIdList[] = $value['car_id'];
                }
                CarRelationship::deleteAll(['driver_id' => $driver->id, 'car_id' => $carIdList]);
                Car::deleteAll(['id' => $carIdList]);
            }
        } catch (\Throwable $th) {
            pre($th);
        }
    }
}
