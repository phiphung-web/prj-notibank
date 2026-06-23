<?php

namespace app\modules\api\controllers;

use app\helpers\MyHelper;
use app\helpers\MyStringHelper;
use app\helpers\ResponseHelper;
use app\models\Agency;
use app\models\AreaConfiguration;
use app\models\SystemConfiguration;
use app\services\CalculationFormulaService;
use app\services\CallService;
use app\services\PriceSettingService;
use app\services\SearchService;
use Yii;
use yii\base\ErrorException;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;

class SearchController extends Controller
{
    public $modelClass = 'app\models\Trip';
    public $calculationFormulaService;
    public $callService;
    public $searchService;
    public $priceSettingService;

    public function init()
    {
        parent::init();
        $this->callService = new CallService();
        $this->calculationFormulaService = new CalculationFormulaService();
        $this->searchService = new SearchService();
        $this->priceSettingService = new PriceSettingService();
    }

    public function actionAddressStart()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $keyword = Yii::$app->request->get('keyword');
            $addressList = $this->callService->searchAddress(! empty($keyword) ? $keyword : '');
            if (! empty($keyword)) {
                Yii::$app->apiLogger->logApiAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'agency_id' => 100, // Id của Én Việt
                    'user_name' => 'Khách hàng đại lý Én việt',
                    'message' => Yii::t('app', 'address_start', [
                        'keyword' => $keyword,
                    ]),
                    'data' => $addressList,
                    'action' => ACTION_SEARCH_LOG,
                ]);
            }

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $addressList);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionAddressStart() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionAddressEnd()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $addressList = [];
            $areaConfigurations = AreaConfiguration::find()->all();
            foreach ($areaConfigurations as $areaConfiguration) {
                switch ($areaConfiguration->type) {
                    case SCHEDULE_AREA_CONFIGURATION:
                        $addressList[] = [
                            'id' => $areaConfiguration['id'],
                            'street' => $areaConfiguration['value'],
                        ];

                        break;
                    default:
                        break;
                }
            }

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $addressList);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionAddressEnd() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionSchedule()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $scheduleList = [];
            foreach (SCHEDULE_LIST as $key => $value) {
                $scheduleList[] = [
                    'value' => $key,
                    'text' => $value,
                ];
            }
            $scheduleList[] = [
                'value' => 'All',
                'text' => 'Hai chiều',
            ];

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $scheduleList);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionSchedule() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionTypeOfCar()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $typeOfCarList = [];
            foreach (TYPE_OF_CAR_LIST as $key => $value) {
                $typeOfCarList[] = [
                    'value' => $key,
                    'text' => $value,
                ];
            }

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $typeOfCarList);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionRecharge() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionTypeReject()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $this->getReasonReject());
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionTypeReject() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionPrice()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $params = $this->searchService->getRequestParams();

            $validate = $this->validateSearchPrice($params);
            if (isset($validate) && is_array($validate) && count($validate)) {
                return ResponseHelper::renderResponse(400, 'Có lỗi xảy ra!', $validate);
            }

            $agency = [];
            if (isset($params['token']) && $params['token'] != 'xevipnoibai') {
                $agency = $this->getAgencyByToken($params['token']);
            }
            $tableArea = $this->searchService->getTableArea($params, $agency);

            if (! empty($params['scheduleData'])) {
                $tableArea = $this->calculationFormulaService->filterByPickupTime($tableArea, $params['pickupTime']);
            }
            $this->searchService->logApiAction($params, $tableArea);

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $params['html'] ? ['html' => $this->renderHtmlCall($tableArea, $params['scheduleData'])] : $tableArea);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionPrice() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionFindPrice()
    {
        $agency = [];
        $params = Yii::$app->request->get();
        $validate = $this->searchService->validateSearchPrice($params);
        if (isset($validate) && is_array($validate) && count($validate)) {
            return ResponseHelper::renderResponse(400, 'Có lỗi xảy ra!', $validate);
        }
        $time = date('H:i:s', strtotime($params['pickupTime']));
        // Nếu chưa có khoảng cách tiến hành tìm kiếm
        if (empty($params['distance'])) {
            $params['distance'] = $this->searchService->getDistance($params);
        }

        // Xác định đại lý

        if ($params['token'] != 'xevipnoibai') {
            $agency = $this->getAgencyByToken($params['token']);
        }

        // Tìm kiếm giá
        $result = $this->calculationFormulaService->findOne($params['schedule'], '', $time);
        $groupedResult = [];
        if (is_array($result) && count($result)) {
            foreach ($result as $record) {
                $typeOfCar = $record['type_of_car'] ?? 'unknown';
                $groupedResult[$typeOfCar][] = $record;
            }
        }

        $vat = (! empty($params['vat']) && $params['vat'] == 1) ? (int)SystemConfiguration::find()->select('content')->where(['keyword' => 'other_price_vat'])->scalar() : 0;

        // Tính toán tăng giá theo event
        $priceSetting = $this->priceSettingService->getPriceSettingByAgency($agency, $params['pickupTime']);
        $array = array_map(function ($key, $values) use ($params, $vat, $agency) {
            // Tính toán thuế
            $price = round($this->calculationFormulaService->formularGeneral($values, $params['distance']) + (int)$params['surcharge'], -3);
            $vatAmount = round($price / 100 * $vat);

            if (isset($priceSetting) && ! empty($priceSetting)) {
                $finalPrice = $this->priceSettingService->calculateFinalPrice($price, $priceSetting);
            } else {
                $finalPrice = $price;
            }

            return [
                'type_of_car' => $key,
                'price' => $finalPrice,
                'vat' => $vatAmount,
            ];
        }, array_keys($groupedResult), array_values($groupedResult));

        $formattedArray = array_column($array, null, 'type_of_car');

        // Lưu log
        if ($params['token'] != 'xevipnoibai') {
            Yii::$app->apiLogger->logApiAction([
                'created_on' => date('Y-m-d H:i:s'),
                'agency_id' => (isset($agency->id) ? $agency->id : 0),
                'user_name' => (isset($agency->name) ? $agency->name : 'Hệ thống XeVip Nội Bài'),
                'message' => Yii::t('app', 'api_find_price_2', [
                    'address_start' => $params['addressStart'],
                    'address_end' => $params['addressEnd'],
                    'schedule' => ($params['schedule'] ? 'Hai chiều' : 'Một chiều'),
                    'pickup_time' => $params['pickupTime'],
                ]),
                'data' => isset($array) ? $array : [],
                'action' => ACTION_PRICE_LOG,
            ]);
        }

        return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $formattedArray);
    }

    private function renderHtmlCall($tableArea, $scheduleData)
    {
        $html = '';
        if (in_array('All', $scheduleData, true)) {
            $html .= '<table class="table table-bordered"><thead><tr><th class="text-center text-bold col-lg-4">Loại xe</th><th class="text-center text-bold col-lg-4">Giá</th><th class="text-bold col-lg-4">Mô tả</th></tr></thead><tbody id="table-body">';
            foreach ($tableArea as $item) {
                $area = $item['data'][0];
                $carName = $item['name'];
                $description = $area['description'];
                $price = MyStringHelper::convertIntegerToPrice($area['price']);
                $roundtrip_price = MyStringHelper::convertIntegerToPrice($area['roundtrip_price']);
                $html .= '<tr>';
                $html .= '<td class="text-bold text-center text-primary">' . $carName . '</td>';
                $html .= '<td class="text-center"><button class="btn btn-warning mr-10 btn-create-trip">' . $roundtrip_price . '₫</button></td>';
                $html .= '<td>' . $description . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        } else {
            $html .= '<table class="table table-bordered"><thead><tr><th class="text-center text-bold col-lg-4">Loại xe</th><th class="text-bold col-lg-4">Thời gian</th><th class="text-center text-bold col-lg-4">Giá</th></tr></thead><tbody id="table-body">';
            foreach ($tableArea as $item) {
                $carName = $item['name'];
                $areaList = $item['data'];

                foreach ($areaList as $index => $area) {
                    $total = count($areaList);
                    $price = MyStringHelper::convertIntegerToPrice($area['price']);
                    $roundtrip_price = MyStringHelper::convertIntegerToPrice($area['roundtrip_price']);
                    $html .= '<tr>';
                    if ($index === 0) {
                        $html .= '<td class="text-bold text-center text-primary" rowspan="' . $total . '">' . $carName . '</td>';
                    }
                    $html .= '<td class=" text-dark"><span class="text-success text-bold">' . SCHEDULE_LIST[$area['schedule']] . ' - </span><span>' . $area['areaConfigurationByTime']['value'] . '</span></td>';
                    $html .= '<td class="text-center"><button class="btn btn-success btn-create-trip mr-10">' . $price . '₫</button></td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table>';
        }

        return $html;
    }

    private function validateSearchPrice($params = [])
    {
        $errors = [];

        if (! isset($params['addressEnd']) || empty($params['addressEnd'])) {
            $errors['address_end'] = 'Trường address_end là bắt buộc.';
        }

        if (! isset($params['schedule']) || empty($params['schedule'])) {
            $errors['schedule'] = 'Trường schedule là bắt buộc.';
        }

        if (! isset($params['addressStart']) || empty($params['addressStart'])) {
            $errors['address_start'] = 'Trường address_start là bắt buộc.';
        }

        return $errors;
    }

    public function getReasonReject()
    {
        $reason_reject = SystemConfiguration::find()
            ->select('content')
            ->where(['keyword' => 'reason_reject'])
            ->scalar();

        $reason_reject = CHOOSE_REASON . '|' . $reason_reject;
        $reason_reject_array = explode('|', $reason_reject);

        $reason_reject_array[999] = ADD_TYPE_REJECT;
        unset($reason_reject_array[0]);

        return $reason_reject_array;
    }

    public function getAgencyByToken($token = '')
    {
        try {
            return Agency::findOne(['token' => $token]);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('ClientController - actionCatch() - ' . $e->getMessage());
        }
    }
}
