<?php

namespace app\services;

use app\models\AreaRelationship;
use app\models\VnDistrict;
use yii\base\Component;
use yii\httpclient\Client;
use Yii;

class SearchService extends Component
{
    public $calculationFormulaService;
    public $priceSettingService;

    public function __construct()
    {
        $this->calculationFormulaService = new CalculationFormulaService();
        $this->priceSettingService = new PriceSettingService();
    }

    public function getRequestParams()
    {
        return [
            'addressEnd' => Yii::$app->request->get('address_end'),
            'schedule' => Yii::$app->request->get('schedule'),
            'addressStart' => Yii::$app->request->get('address_start'),
            'html' => Yii::$app->request->get('html'),
            'token' => Yii::$app->request->get('token'),
            'typeOfCarText' => Yii::$app->request->get('type_of_car'),
            'pickupTime' => date('Y-m-d H:i:s', strtotime(Yii::$app->request->get('pickup_time'))),
            'scheduleData' => json_decode(Yii::$app->request->get('schedule'), true),
        ];
    }

    public function getTableArea($params, $agency = [])
    {
        $tableArea = [];
        $dataArea = [];
        $areaRelationship = AreaRelationship::findOne(['id' => $params['addressStart']]);

        if ($areaRelationship !== null) {
            $areaRelationshipList = $this->getAreaRelationshipList($areaRelationship, $params);

            $dataArea = $areaRelationshipList->asArray()->all();
            if (isset($dataArea) && is_array($dataArea) && count($dataArea)) {
                $tableArea = $this->populateTableArea($dataArea);
            }
        }

        if (isset($dataArea) && is_array($dataArea) && count($dataArea)) {
            $firstElement = reset($dataArea);
            $paramDistance['addressStart'] = str_replace(' ', '+', (isset($firstElement['street'], $firstElement['districtid'])
                ? $firstElement['street'] . '+' . VnDistrict::findOne(['districtid' => $firstElement['districtid']])->name
                : ''));
            $paramDistance['addressEnd'] = str_replace(' ', '+', (isset($firstElement['areaConfigurationByAddress']['value'])
                ? $firstElement['areaConfigurationByAddress']['value']
                : ''));
            $distance = $this->getDistance($paramDistance);
            $tableArea = $this->convertPriceOldVersionToNewVersion($tableArea, $distance, $params, $agency);
        }

        return $tableArea;
    }

    private function getAreaRelationshipList($areaRelationship, $params)
    {
        $query = AreaRelationship::find()
            ->where([
                'area_id' => $areaRelationship->area_id,
                'area_relationship.districtid' => $areaRelationship->districtid,
                'area_relationship.provinceid' => $areaRelationship->provinceid,
                'area_relationship.street' => $areaRelationship->street,
                'area_relationship.address' => $params['addressEnd'],
            ])
            ->joinWith(['areaConfigurationByTime', 'areaConfigurationByAddress', 'area'])
            ->orderBy(['area_relationship.schedule' => SORT_ASC]);

        if (!empty($params['scheduleData'])) {
            $query->andWhere(['IN', 'schedule', $params['scheduleData']]);
        }

        if (!empty($params['typeOfCarText'])) {
            $query->andWhere(['type_of_car' => $params['typeOfCarText']]);
        }

        return $query;
    }

    public function getDistance($params)
    {
        $url = 'https://maps.googleapis.com/maps/api/directions/json';
        $client = new Client();
        $response = $client
            ->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->setData([
                'origin' => $params['addressStart'],
                'destination' => $params['addressEnd'],
                'mode' => 'driving',
                'alternatives' => 'true',
                'sensor' => 'false',
                'key' => GOOGLE_DISTANCE_API_KEY,
            ])
            ->send();

        if ($response->isOk) {
            $response_all = $response->data;

            if (isset($response_all['routes']) && count($response_all['routes']) > 0) {
                $minDistance = null;
                foreach ($response_all['routes'] as $route) {
                    $distanceValue = $route['legs'][0]['distance']['value']; // Meters
                    if ($minDistance === null || $distanceValue < $minDistance) {
                        $minDistance = $distanceValue;
                    }
                }
                return round($minDistance / 1000, 1); // Round to 1 decimal place (km)
            }
        }

        return 0;
    }

    private function populateTableArea($dataArea)
    {
        $tableArea = [];

        foreach ($dataArea as $item) {
            $tableArea[$item['type_of_car']] = [
                'name' => TYPE_OF_CAR_LIST[$item['type_of_car']],
                'id' => $item['type_of_car'],
                'data' => [],
            ];
        }

        foreach ($tableArea as $key => $value) {
            foreach ($dataArea as $item) {
                if ($item['type_of_car'] == $value['id']) {
                    unset($item['area']['price_list']);
                    $tableArea[$key]['data'][] = $item;
                }
            }
        }

        return $tableArea;
    }

    public function logApiAction($params, $tableArea)
    {
        $addressStart = '';
        $addressEnd = '';
        if (isset($tableArea) && is_array($tableArea) && count($tableArea)) {
            foreach ($tableArea as $key => $value) {
                $path = isset($value['data'][0]) ? $value['data'][0] : [];
                if (isset($path) && is_array($path) && count($path)) {
                    $addressStart = isset($path['street'], $path['districtid'])
                        ? $path['street'] . ', ' . VnDistrict::findOne(['districtid' => $path['districtid']])->name
                        : '';
                    $addressEnd = isset($path['areaConfigurationByAddress']['value'])
                        ? $path['areaConfigurationByAddress']['value']
                        : '';
                }

                break;
            }
        }

        Yii::$app->apiLogger->logApiAction([
            'created_on' => date('Y-m-d H:i:s'),
            'agency_id' => 100,  // Id của Én Việt
            'user_name' => 'Khách hàng đại lý Én Việt',
            'message' => Yii::t('app', 'api_find_price_1', [
                'address_start' => $addressStart,
                'address_end' => $addressEnd,
                'schedule' => isset($params['scheduleData'])
                    ? $this->getScheduleText($params['scheduleData'])
                    : '',
                'type_of_car' => isset($params['typeOfCarText'])
                    ? $params['typeOfCarText']
                    : '',
                'pickup_time' => isset($params['pickupTime'])
                    ? $params['pickupTime']
                    : '',
            ]),
            'data' => isset($tableArea) ? $tableArea : [],
            'action' => ACTION_PRICE_LOG,
        ]);
    }

    private function getScheduleText($arr = [])
    {
        $arrText = [
            'All' => 'Hai chiều',
            0 => 'Chiều đi',
            1 => 'Chiều về',
        ];

        if (empty($arr)) {
            return '';
        }

        $result = array_map(function ($key) use ($arrText) {
            return isset($arrText[$key]) ? $arrText[$key] : '';
        }, $arr);

        $result = array_filter($result);

        return implode(', ', $result);
    }

    public function validateSearchPrice($params = [])
    {
        $errors = [];

        if (!isset($params['pickupTime']) || empty($params['pickupTime'])) {
            $errors['pickupTime'] = 'Trường thời gian đi là bắt buộc.';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $params['pickupTime'])) {
            $errors['pickupTime'] = "Thời gian pickup không hợp lệ! Định dạng phải là 'Y-m-d H:i'.";
        }

        if (!isset($params['addressEnd']) || empty($params['addressEnd'])) {
            $errors['addressEnd'] = 'Trường điểm đến là bắt buộc.';
        }

        if (!isset($params['schedule'])) {
            $errors['schedule'] = 'Trường lịch trình là bắt buộc.';
        }

        if (!isset($params['addressStart']) || empty($params['addressStart'])) {
            $errors['addressStart'] = 'Trường điểm đi là bắt buộc.';
        }

        if (!isset($params['token']) || empty($params['token'])) {
            $errors['token'] = 'Trường token là bắt buộc.';
        }

        return $errors;
    }
}
