<?php

namespace app\services;

use app\helpers\MyStringHelper;
use app\models\SystemConfiguration;
use app\models\Trip;
use yii\base\Component;
use yii\helpers\Url;
use Yii;
use DateTime;
use DateTimeZone;
class CallService extends Component
{
    public function searchAddress($keyword = '')
    {
        $formattedResults = [];
        $query = new \yii\db\Query();
        $results = $query
            ->select(['area_relationship.id', 'area_relationship.street', 'area.area_name'])
            ->from('area_relationship')
            ->leftJoin('area', 'area_relationship.area_id = area.id')
            ->leftJoin('vn_province', 'area_relationship.provinceid = vn_province.provinceid')
            ->where(['like', 'area_relationship.street', $keyword])
            ->orWhere(['like', 'area.area_name', $keyword])
            ->groupBy('area_relationship.id')
            ->limit('100')
            ->all();

        $check = [];
        foreach ($results as $result) {
            if (!in_array($result['street'] . ' - ' . $result['area_name'], $check)) {
                $formattedResults[] = [
                    'id' => $result['id'],
                    'street' => "{$result['street']} - {$result['area_name']}",
                ];
            }
            $check[] = $result['street'] . ' - ' . $result['area_name'];
        }

        return $formattedResults;
    }

    public function renderHtmlCall($tableArea, $scheduleData, $phone, $idCallBack, $scheduleList)
    {
        $html = '';
        if (in_array('All', $scheduleData)) {
            $html .= '<table class="table table-bordered"><thead><tr><th class="text-center text-bold col-lg-4">Loại xe</th><th class="text-center text-bold col-lg-4">Giá</th><th class="text-bold col-lg-4">Mô tả</th></tr></thead><tbody id="table-body">';
            foreach ($tableArea as $item) {
                $area = $item['data'][0];
                $carName = $item['name'];
                $description = $area['description'];
                $price = MyStringHelper::convertIntegerToPrice($area['price']);
                $roundtrip_price = MyStringHelper::convertIntegerToPrice($area['roundtrip_price']);
                $street = $area['street'] . ', ' . $area['area']['area_name'];
                $url_roundtrip = Yii::$app->user->can('DAI_LY_ROLE') ? $this->generateCreateBookingUrl($phone, $area, $street, $idCallBack) : $this->generateTripUrl($phone, $area, $street, $idCallBack);
                $url_reject = $this->generateRejectTripUrl($phone, $area, $street, $idCallBack);
                $html .= '<tr>';
                $html .= '<td class="text-bold text-center text-primary">' . $carName . '</td>';
                $html .= '<td class="text-center"><a target="_blank" href="' . $url_roundtrip . '" class="btn btn-warning mr-10 create-trip-by-advise">' . $roundtrip_price . '₫</a>';
                $html .= '<a target="_blank" href="' . $url_reject . '" class="btn btn-danger create-trip-by-advise mr-10">Từ chối</a></td>';
                $html .= '<td>' . $description . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<table class="table table-bordered"><thead><tr><th class="text-center text-bold col-lg-4">Loại xe</th><th class="text-bold col-lg-4">Thời gian</th><th class="text-center text-bold col-lg-4">Giá</th></tr></thead><tbody id="table-body">';

            foreach ($tableArea as $item) {
                $carName = $item['name'];
                $areaList = $item['data'];

                foreach ($areaList as $index => $area) {
                    $total = count($areaList);
                    $price = MyStringHelper::convertIntegerToPrice($area['price']);
                    $roundtrip_price = MyStringHelper::convertIntegerToPrice($area['roundtrip_price']);
                    $street = $area['street'] . ', ' . $area['area']['area_name'];
                    $url = Yii::$app->user->can('DAI_LY_ROLE') ? $this->generateCreateBookingUrl($phone, $area, $street, $idCallBack, false) : $this->generateTripUrl($phone, $area, $street, $idCallBack, false, $params['pickup_time'] ?? null);
                    $url_reject = $this->generateRejectTripUrl($phone, $area, $street, $idCallBack, false);
                    $html .= '<tr>';
                    if ($index === 0) {
                        $html .= '<td class="text-bold text-center text-primary" rowspan="' . $total . '">' . $carName . '</td>';
                    }
                    $html .= '<td class=" text-dark"><span class="text-success text-bold">' . $scheduleList[$area['schedule']] . ' - </span><span>' . $area['areaConfigurationByTime']['value'] . '</span></td>';
                    $html .= '<td class="text-center"><a target="_blank" href="' . $url . '" class="btn btn-success create-trip-by-advise mr-10">' . $price . '₫</a>';
                    $html .= '<a target="_blank" href="' . $url_reject . '" class="btn btn-danger create-trip-by-advise btn-reject-call-table mr-10">Từ chối</a></td>';
                    $html .= '</tr>';
                }
            }
        }

        return $html;
    }

    public function generateCreateBookingUrl($phone, $area, $street, $idCallBack, $roundTrip = true)
    {
        $urlParams = [
            '/statistic/create',
            'customer_phone' => $phone,
            'price_customer' => $roundTrip ? $area['roundtrip_price'] : $area['price'],
            'round_trip' => $roundTrip ? 1 : 0,
            'pickup_address' => ($area['schedule'] == 0 ? $street : $area['areaConfigurationByAddress']['value']),
            'area' => $street,
            'destination_address' => ($area['schedule'] == 1 ? $street : $area['areaConfigurationByAddress']['value']),
            'type_of_car' => $area['type_of_car'],
            'idCallBack' => $idCallBack,
        ];

        return Url::to($urlParams);
    }

    public function generateRejectTripUrl($phone, $area, $street, $idCallBack, $roundTrip = true)
    {
        $urlParams = [
            '/statistic/create',
            'status' => 'reject',
            'customer_phone' => $phone,
            'price_customer' => $roundTrip ? $area['roundtrip_price'] : $area['price'],
            'round_trip' => $roundTrip ? 1 : 0,
            'pickup_address' => ($area['schedule'] == 0 ? $street : $area['areaConfigurationByAddress']['value']),
            'area' => $street,
            'destination_address' => ($area['schedule'] == 1 ? $street : $area['areaConfigurationByAddress']['value']),
            'type_of_car' => $area['type_of_car'],
            'idCallBack' => $idCallBack,
        ];

        return Url::to($urlParams);
    }

    public function generateTripUrl($phone, $area, $street, $idCallBack, $roundTrip = true, $pickupTime = null)
    {
        $urlParams = [
            '/trip/create',
            'phone' => $phone,
            'price' => $roundTrip ? $area['roundtrip_price'] : $area['price'],
            'round_trip' => $roundTrip ? 1 : 0,
            'pickup_address' => ($area['schedule'] == 0 ? $street : $area['areaConfigurationByAddress']['value']),
            'area' => $street,
            'destination_address' => ($area['schedule'] == 1 ? $street : $area['areaConfigurationByAddress']['value']),
            'pickup_time' => $pickupTime,
            'type_of_car' => $area['type_of_car'],
            'idCallBack' => $idCallBack,
            'customer_property' => Trip::getCustomerPropertyLabel($phone),
        ];

        return Url::to($urlParams);
    }

    public function renderHtmlCallQuote($resultData, $params)
    {
        $html = '<table class="table table-bordered">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Loại xe</th>';
        $html .= '<th>Lịch trình</th>';
        $html .= '<th class="text-center">Giá (VNĐ)</th>';
        $html .= '<th class="text-center">Phụ phí (VNĐ)</th>';
        $html .= '<th class="text-center">Phí chờ (VNĐ)</th>';
        $html .= '<th class="text-center">Phí qua đêm (VNĐ)</th>';
        $html .= '<th></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $groupedData = $this->groupDataByType($resultData);
        $customerProperty = Trip::getCustomerPropertyLabel($params['phone']);
        foreach ($groupedData as $type => $items) {
            $html .= '<tr>';
            $html .= '<td class="text-bold text-center text-primary" rowspan="' . count($items) . '">' . TYPE_OF_CAR_LIST[$type] . '</td>';

            foreach ($items as $index => $item) {
                $price = $item['price_distance'];
                $surcharge = (isset($item['surcharge']) ? $item['surcharge'] : 0) + $params['surcharge'];
                $priceWait = (isset($item['price_wait']) ? $item['price_wait'] : 0) * $params['hourWait'];
                $overnightFee = $params['overnight'] ? (isset($item['overnight_fee']) ? $item['overnight_fee'] : 0) : 0;
                $totalPrice = $price + $surcharge  + $overnightFee;
                $url = Yii::$app->user->can('DAI_LY_ROLE') ? $this->generateCreateBookingUrlByCallQuote([
                    'phone' => $params['phone'],
                    'price' => $totalPrice,
                    'roundTrip' => $item['schedule'],
                    'pickupAddress' => $params['pickupAddress'],
                    'pickupTime' => $params['pickup_time'],
                    'destinationAddress' => $params['destinationAddress'],
                    'typeOfCar' => $type,
                    'customer_property' => $customerProperty,
                ]) : $this->generateTripUrlByCallQuote([
                    'phone' => $params['phone'],
                    'price' => $totalPrice,
                    'roundTrip' => $item['schedule'],
                    'pickupAddress' => $params['pickupAddress'],
                    'pickupTime' => $params['pickup_time'],
                    'destinationAddress' => $params['destinationAddress'],
                    'typeOfCar' => $type,
                    'customer_property' =>  $customerProperty,
                ]);
                $url_reject = $this->generateRejectTripUrlByCallQuote([
                    'phone' => $params['phone'],
                    'price' => $totalPrice,
                    'roundTrip' => $item['schedule'],
                    'pickupAddress' => $params['pickupAddress'],
                    'pickupTime' => $params['pickup_time'],
                    'destinationAddress' => $params['destinationAddress'],
                    'typeOfCar' => $type,
                ]);
                if ($index !== 0) {
                    $html .= '<tr>';
                }
                $html .= '<td>' . SCHEDULE_LIST_TRIP[$item['schedule']] . '</td>';

                $html .= '<td class="text-center">';
                $html .= MyStringHelper::convertIntegerToPrice($price);
                $html .= '</td>';

                $html .= '<td class="text-center">' . MyStringHelper::convertIntegerToPrice($surcharge) . '</td>';
                $html .= '<td class="text-center">' . MyStringHelper::convertIntegerToPrice($priceWait) . '</td>';
                $html .= '<td class="text-center">' . MyStringHelper::convertIntegerToPrice($overnightFee) . '</td>';
                $html .= '<td class="text-center"><a target="_blank" href="' . $url . '" class="btn btn-success create-trip-by-advise mr-10">' . MyStringHelper::convertIntegerToPrice($totalPrice) . '₫</a>';
                $html .= '<a target="_blank" href="' . $url_reject . '" class="btn btn-danger create-trip-by-advise mr-10">Từ chối</a></td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    private function groupDataByType($data)
    {
        $groupedData = [];

        foreach ($data as $item) {
            $type = $item['type_of_car'];
            if (!isset($groupedData[$type])) {
                $groupedData[$type] = [];
            }
            $groupedData[$type][] = $item;
        }

        return $groupedData;
    }

    public function generateCreateBookingUrlByCallQuote($params = [])
    {
        $urlParams = [
            '/statistic/create',
            'customer_phone' => isset($params['phone']) ? $params['phone'] : '',
            'price_customer' => isset($params['price']) ? $params['price'] : 0,
            'round_trip' => isset($params['roundTrip']) ? $params['roundTrip'] : 0,
            'pickup_address' => isset($params['pickupAddress']) ? $params['pickupAddress'] : '',
            'pickup_time' => isset($params['pickupTime']) ? $params['pickupTime'] : '',
            'area' => isset($params['pickupAddress']) ? $params['pickupAddress'] : '',
            'destination_address' => isset($params['destinationAddress']) ? $params['destinationAddress'] : '',
            'type_of_car' => isset($params['typeOfCar']) ? $params['typeOfCar'] : '',
            'idCallBack' => isset($params['idCallBack']) ? $params['idCallBack'] : 0,
            'voucher' => isset($params['voucher']) ? $params['voucher'] : 0,
        ];

        return Url::to($urlParams);
    }

    public function generateRejectTripUrlByCallQuote($params = [])
    {
        $urlParams = [
            '/statistic/create',
            'status' => 'reject',
            'customer_phone' => isset($params['phone']) ? $params['phone'] : '',
            'price_customer' => isset($params['price']) ? $params['price'] : 0,
            'round_trip' => isset($params['roundTrip']) ? $params['roundTrip'] : 0,
            'pickup_address' => isset($params['pickupAddress']) ? $params['pickupAddress'] : '',
            'pickup_time' => isset($params['pickupTime']) ? $params['pickupTime'] : '',
            'area' => isset($params['pickupAddress']) ? $params['pickupAddress'] : '',
            'destination_address' => isset($params['destinationAddress']) ? $params['destinationAddress'] : '',
            'type_of_car' => isset($params['typeOfCar']) ? $params['typeOfCar'] : '',
            'idCallBack' => isset($params['idCallBack']) ? $params['idCallBack'] : 0,
        ];

        return Url::to($urlParams);
    }

    public function generateTripUrlByCallQuote($params = [])
    {
        $customerProperty = isset($params['customer_property']) ? $params ['customer_property'] : Trip::getCustomerPropertyLabel($params['phone'] ?? '');

        $urlParams = [
            '/trip/create',
            'phone' => isset($params['phone']) ? $params['phone'] : '',
            'price' => isset($params['price']) ? $params['price'] : 0,
            'round_trip' => isset($params['roundTrip']) ? $params['roundTrip'] : 0,
            'pickup_address' => isset($params['pickupAddress']) ? $params['pickupAddress'] : '',
            'area' => isset($params['pickupAddress']) ? $params['pickupAddress'] : '',
            'destination_address' => isset($params['destinationAddress']) ? $params['destinationAddress'] : '',
            'pickup_time' => isset($params['pickupTime']) ? $params['pickupTime'] : '',
            'type_of_car' => isset($params['typeOfCar']) ? $params['typeOfCar'] : '',
            'idCallBack' => isset($params['idCallBack']) ? $params['idCallBack'] : 0,
            'voucher' => isset($params['voucher']) ? $params['voucher'] : 0,
            'customer_property' => $customerProperty,
        ];

        return Url::to($urlParams);
    }

    public function checkSourceWhenReject($hotline)
    {
        $source_web_1 = SystemConfiguration::find()->select('content')->where(['keyword' => 'call_phone_web_1'])->scalar();
        $source_web_2 = SystemConfiguration::find()->select('content')->where(['keyword' => 'call_phone_web_2'])->scalar();

        $source_web_1_array = explode('|', $source_web_1);
        $source_web_2_array = explode('|', $source_web_2);

        $is_hotline_in_web_1 = in_array($hotline, $source_web_1_array);
        $is_hotline_in_web_2 = in_array($hotline, $source_web_2_array);

        if ($is_hotline_in_web_1) {
        } elseif ($is_hotline_in_web_2) {
        } else {
        }
    }
}
