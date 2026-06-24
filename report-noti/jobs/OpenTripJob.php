<?php

namespace app\jobs;

use app\models\Driver;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\Trip;
use app\repositories\DriverTokenRepository;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use yii\helpers\Console;

class OpenTripJob extends BaseObject implements JobInterface
{
    public $trip_id;
    public $driver_ids;

    public function execute($queue)
    {
        $repo = new DriverTokenRepository();
        $firebase = Yii::$app->firebaseService;
        $allTokens = [];
        if (!is_array($this->driver_ids)) {
            $this->driver_ids = (array) $this->driver_ids;
        }

        foreach ($this->driver_ids as $driverId) {
            $driver = Driver::findOne($driverId);
            if (!$driver) {
                continue;
            }
            if ($driver->allow_notification != Driver::STATUS_SEND_NOTIFICATION) {
                continue;
            }
            $tokens = $repo->findValidTokenByDriverId($driverId);
            if (!empty($tokens)) {
                $tokenStrings = array_column($tokens, 'token');
                foreach ($tokenStrings as $token) {
                    if ($token === null || $token === '') {
                        continue;
                    }
                    $allTokens[] = (string) $token;
                }
            }
        }
        $allTokens = array_values(array_unique($allTokens));

        if (empty($allTokens)) {
            return;
        }

        $trip = Trip::findOne($this->trip_id);
        if (!$trip || $trip->display == 0) {
            return;
        }

        $title = '';
        $body = '';
        $from = !empty($trip->pickup_address) ? $trip->pickup_address : (!empty($trip->area) ? $trip->area : '');
        $to = !empty($trip->destination_address) ? $trip->destination_address : '';
        if ($from && $to) {
            $body = " {$from} => {$to} ." . "Nhận chuyến ngay!";
        }
        $price = $trip->price_bid ?? $trip->price_bid ?? null;
        if (!empty($price)) {
            $title = "[Chuyến Mới] Giá: " . number_format((int) $price) . "đ. ";
        } else {
            $title = "Nhận chuyến ngay!";
        }
        $data = [
            'trip_id' => (string) $trip->id
        ];
        $androidConfig = AndroidConfig::fromArray([
            'priority' => 'high',
            'notification' => [
                'channel_id' => 'default_notification_channel',
            ],
        ]);

        $apnsConfig = ApnsConfig::fromArray([
            'payload' => [
                'aps' => [
                    'sound' => 'custom_notification_sound.wav',
                ],
            ],
        ]);

        $result = $firebase->sendNotificationWithSound($allTokens, $title, $body, null, $data, $androidConfig, $apnsConfig);
    }
}
