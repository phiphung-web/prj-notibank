<?php

namespace app\services;

use app\helpers\MyStringHelper;
use app\models\Area;
use yii\base\Component;
use yii\base\InvalidConfigException;

class AreaService extends Component
{
    /**
     * Lấy thông tin bản ghi "Area" theo ID
     * @param int $id
     * @return Area|null
     */
    public function getPriceListClone($request)
    {
        $area = [];
        if (isset($request['id-clone']) && ! empty($request['id-clone'])) {
            $area = $this->getById($request['id-clone']);
        }

        return $area;
    }

    /**
     * Lấy thông tin bản ghi "Area" theo ID
     * @param int $id
     * @return Area|null
     */
    public function getById($id)
    {
        return Area::findOne($id);
    }

    /**
     * Lưu thông tin bản ghi "Area" vào cơ sở dữ liệu
     * @param Area $model
     * @return bool
     * @throws \Exception
     */
    public function save(Area $model)
    {
        if (! $model->save()) {
            throw new \Exception('Failed to save the model.');
        }

        return $model;
    }

    /**
     * Xóa bản ghi "Area" theo ID
     * @param int $id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteById($id)
    {
        $model = Area::findOne($id);
        if (! $model) {
            throw new InvalidConfigException('The requested record does not exist.');
        }

        if (! $model->delete()) {
            throw new \Exception('Failed to delete the model.');
        }

        return true;
    }

    public function priceListArea($data = [])
    {
        $sortedData = [];
        if (isset($data) && is_array($data) && count($data)) {
            $recordCount = count($data['type_of_car']);
            for ($i = 0; $i < $recordCount; $i++) {
                $record = [
                    'type_of_car' => $data['type_of_car'][$i],
                    'time' => $data['time'][$i],
                    'schedule' => $data['schedule'][$i],
                    'price' => MyStringHelper::convertStringToInteger($data['price'][$i]),
                    'roundtrip_price' => MyStringHelper::convertStringToInteger($data['roundtrip_price'][$i]),
                    'description' => $data['description'][$i],
                    'address' => $data['address'][$i],
                ];
                $sortedData[] = $record;
            }
        }

        return $sortedData;
    }

    public function storeAreaRelationship($area, $priceList)
    {
        $areaRelationship = [];
        $streetList = explode(',', $area->street);
        if (isset($priceList) && is_array($priceList) && count($priceList)) {
            foreach ($priceList as $key => $value) {
                if (isset($streetList) && is_array($streetList) && count($streetList)) {
                    foreach ($streetList as $keyStreet => $valueStreet) {
                        $merge = array_merge([
                            'street' => $valueStreet,
                            'area_id' => $area->id,
                            'districtid' => $area->districtid,
                            'provinceid' => $area->provinceid,
                        ], $value);
                        $areaRelationship[] = $merge;
                    }
                }
            }
        }

        return $areaRelationship;
    }
}