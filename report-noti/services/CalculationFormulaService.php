<?php

namespace app\services;

use app\models\CalculationFormula;
use yii\base\Component;

class CalculationFormulaService extends Component
{
    public function calculateNormal($params)
    {
        $calculationFormula = $this->findOne($params['type_of_car'] ?? 0, $params['time'] ?? '');

        return $this->formularGeneral($calculationFormula, $params['distance']);
    }

    public function formularGeneral($calculationFormula, $distance)
    {
        $totalPrice = 0;
        if (is_array($calculationFormula) && count($calculationFormula)) {
            foreach ($calculationFormula as $key => $value) {
                if ($value['km_start'] <= $distance || $key + 1 == count($calculationFormula)) {
                    $totalPrice = $value['price_by_km'];
                    break;
                }
            }
        }

        return $totalPrice;
    }

    public function findOne($type_of_car, $time)
    {
        $query = CalculationFormula::find()
            ->andWhere([
                'or',
                ['and', ['<=', 'time_start', $time], ['>=', 'time_end', $time]],
                ['and', ['>', 'time_start', new \yii\db\Expression('time_end')], ['or', ['<=', 'time_start', $time], ['>=', 'time_end', $time]]],
            ]);

        if (!empty($type_of_car)) {
            $query->andWhere(['type_of_car' => $type_of_car]);
        }

        return $query->orderBy('type_of_car asc, km_start desc')->asArray()->all();
    }

    public function filterByPickupTime($data, $time)
    {
        $hour = $this->convertToMinutes((new \DateTime($time))->format('H:i'));

        if (empty($data) || !is_array($data)) {
            return [];
        }

        foreach ($data as $key => $value) {
            if (empty($value['data']) || !is_array($value['data'])) {
                continue;
            }

            foreach ($value['data'] as $keyData => $valueData) {
                list($timeStart, $timeEnd) = explode(' đến ', $valueData['areaConfigurationByTime']['value']);
                $timeStart = $this->convertToMinutes($timeStart);
                $timeEnd = $this->convertToMinutes($timeEnd);

                if (!(($timeStart >= $timeEnd && ($hour <= $timeEnd || $hour >= $timeStart)) || ($timeStart <= $timeEnd && ($hour <= $timeEnd && $hour >= $timeStart)))) {
                    unset($data[$key]['data'][$keyData]);
                }
            }
            $data[$key]['data'] = array_values($data[$key]['data']);
        }

        return $data;
    }

    public function convertToMinutes($time)
    {
        list($hours, $minutes) = explode(':', $time);

        return (int) trim($hours) * 60 + trim($minutes);
    }
}
