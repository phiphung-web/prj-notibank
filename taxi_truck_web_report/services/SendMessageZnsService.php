<?php

namespace app\services;

use app\models\MessageZns;
use app\models\SystemConfiguration;
use app\models\Trip;
use Yii;

/**
 * Class SendMessageZnsService
 *
 * This class handles the trip-related operations.
 */
class SendMessageZnsService
{
    public $voucherService;

    public function __construct()
    {
        $this->voucherService = new VoucherService();
    }

    public function sendMessageNotifyDriver($trip, $systemConfiguration)
    {
        $data = [
            'phone' => '84' . substr($trip['username'], 1),
            'template_id' => $systemConfiguration['zalo_template_notify'],
            'template_data' => [
                'time_pickup' => date('H:i d/m/Y', strtotime($trip['pickup_time'])),
                'address_form' => $trip['pickup_address'],
                'address_to' => $trip['destination_address'],
                'phone_number' => $trip['customer_phone'],
                'customer_name' => $trip['customer_name'],
                'time' => '20 phút',

            ],
            'tracking_id' => $trip['id'],
        ];

        $json_data = json_encode($data);
        $curl = curl_init(URL_SEND_ZNS);
        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_data),
                'access_token: ' . $systemConfiguration['zalo_access_token'],
            ],
        ]);

        $response = json_decode(curl_exec($curl), true);
        curl_close($curl);

        $messageZns = MessageZns::find()
            ->where(['template_id' => $systemConfiguration['zalo_template_notify'], 'trip_id' => $trip['id']])->one();
        if (! $messageZns) {
            $messageZns = new MessageZns();
        }
        $messageZns->setAttributes([
            'trip_id' => $trip['id'],
            'template_id' => $systemConfiguration['zalo_template_notify'],
            'phone' => $trip['customer_phone'],
            'code' => isset($response['error']) ? $response['error'] : '',
            'message' => isset($response['message']) ? $response['message'] : '',
            'template_data' => json_encode($data['template_data']),
        ]);
        $messageZns->save();

        return $messageZns->getAttributes();
    }

    public function tripSendMessageZNS(Trip $trip)
    {
        $system = SystemConfiguration::find()->asArray()->all();
        $temp = array_column($system, 'content', 'keyword');
        $template = $temp['zalo_template_1'];
        if ($trip->source_trip == SOURCE_TRIP_TYPE_AGENCY && ! empty($trip->agency_id)) {
            $agency = \app\models\Agency::findOne($trip->agency_id);
            if (isset($agency->send_price) && $agency->send_price === 0) {
                $template = $temp['zalo_template_4'];
            }
        }
        $data = [
            'phone' => '84' . substr($trip->customer_phone, 1),
            'template_id' => $template,
            'template_data' => [
                'customer_name' => $trip->customer_name,
                'receiver_name' => $trip->customer_name,
                'receiver_phone_number' => $trip->customer_phone,
                'amount' => $trip->price_customer ? $trip->price_customer : 0,
                'start_date' => date('H:i d/m/Y', strtotime($trip->pickup_time)),
                'address_form' => $trip->pickup_address,
                'address_to' => $trip->destination_address,
                'promotion_price' => PROMOTION_PERCENT,
                'type_car' => TYPE_OF_CAR_LIST[$trip->type_of_car],
                'time_pickup' => ' 30 phút'
            ],
            'tracking_id' => $trip->id,
        ];
        $voucher = $this->voucherService->searchCodeNotSend();
        if (! empty($temp['zalo_template_1_voucher']) && ($trip->source_trip != SOURCE_TRIP_TYPE_AGENCY || empty($trip->agency_id))) {
            if ($voucher != null) {
                $data = [
                    'phone' => '84' . substr($trip->customer_phone, 1),
                    'template_id' => $temp['zalo_template_1_voucher'],
                    'template_data' => [
                        'customer_name' => $trip->customer_name,
                        'phone_number' => $trip->customer_phone,
                        'price' => $trip->price_customer,
                        'start_date' => date('H:i d/m/Y', strtotime($trip->pickup_time)),
                        'address_form' => $trip->pickup_address,
                        'address_to' => $trip->destination_address,
                        'promotion_price' => PROMOTION_PERCENT,
                        'type_car' => TYPE_OF_CAR_LIST[$trip->type_of_car],
                        'time_pickup' => date('H:i d/m/Y', strtotime($trip['pickup_time'])),
                        'trip_id' => $trip->id,
                        'content_support' => $voucher->code . ' giảm giá ' . (int)($voucher->value / 1000) . 'K' . ' lần sau',
                    ],
                    'tracking_id' => $trip->id,
                ];
            }
        }
        $json_data = json_encode($data);
        $curl = curl_init(URL_SEND_ZNS);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json_data),
            'access_token: ' . $temp['zalo_access_token'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);
        // Cập nhật trạng thái voucher là đã phát hành
        if ($response['error'] == 0 && $voucher != null) {
            $this->voucherService->updateVoucherIsSend($voucher);
        }

        $model = new MessageZns();
        $model->trip_id = $trip->id;
        $model->template_id = $temp['zalo_template_1'];
        $model->phone = $trip->customer_phone;
        $model->code = isset($response['error']) ? $response['error'] : '';
        $model->message = isset($response['message']) ? $response['message'] : '';
        $model->template_data = json_encode($data['template_data']);
        $model->save();

        return $response;
    }

    public function sendMessageDriverZns($params = [])
    {
        $system = SystemConfiguration::find()->asArray()->all();
        $temp = array_column($system, 'content', 'keyword');

        $data = [
            'phone' => '84' . substr($params['customer_phone'], 1),
            'template_id' => $temp['zalo_template_2'],
            'template_data' => [
                'driver_name' => $params['driver_name'],
                'phone_driver' => $params['phone_number'],
                'license_plates' => $params['license_plates'],
                'customer_name' => $params['customer_name'],
                'order_code' => $params['tracking_id'],
            ],
        ];

        $response = [];

        // Check if should send ZNS message
        $shouldSendMessage = !isset($params['send_message']) || $params['send_message'] === true;

        if ($shouldSendMessage) {
            $json_data = json_encode($data);
            $curl = curl_init(URL_SEND_ZNS);
            curl_setopt_array($curl, [
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $json_data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json_data),
                    'access_token: ' . $temp['zalo_access_token'],
                ],
            ]);

            $response = json_decode(curl_exec($curl), true);
            curl_close($curl);
        }

        $model = new MessageZns();
        $model->setAttributes([
            'trip_id' => $params['tracking_id'],
            'template_id' => $temp['zalo_template_2'],
            'phone' => $params['customer_phone'],
            'code' => $shouldSendMessage ? (isset($response['error']) ? $response['error'] : '') : 0,
            'message' => $shouldSendMessage ? (isset($response['message']) ? $response['message'] : '') : 'Không gửi tin nhắn Zalo cho khách hàng',
            'reason' => isset($params['reason']) ? $params['reason'] : null,
            'template_data' => json_encode($data['template_data']),
        ]);
        $model->save();
    }
}