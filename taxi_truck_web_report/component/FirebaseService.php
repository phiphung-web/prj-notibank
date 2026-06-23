<?php

namespace app\component;

use Exception;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Yii;

class FirebaseService
{
    private Messaging $messaging;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(Yii::getAlias('@app/config/firebase-service-account.json'));
        $this->messaging = $factory->createMessaging();
    }

    /**
     * Gửi thông báo FCM tới một thiết bị
     *
     * @param array $tokens
     * @param string $title Tiêu đề thông báo
     * @param string $body Nội dung thông báo
     * @param String|null $image
     * @param array|null $data
     * @return array Kết quả gửi thông báo
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function sendNotification(array $tokens, string $title, string $body, ?string $image, ?array $data): array
    {
        $result = [];
        $notification = Notification::create($title, $body, $image);
        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)->withData($data);

                Yii::info(
                    'FCM CloudMessage payload: ' . json_encode($message, JSON_UNESCAPED_UNICODE),
                    __METHOD__
                );

                Yii::info(
                    'Sending FCM message: ' . json_encode([
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'image' => $image,
                        ],
                        'data' => $data,
                    ], JSON_UNESCAPED_UNICODE),
                    __METHOD__
                );

                $result[] = [
                    'token' => $token,
                    'status' => 'success',
                    'message' => 'Notification sent successfully.',
                    'result' => $this->messaging->send($message),
                ];
            } catch (Exception $e) {
                Yii::warning(
                    'Error sending FCM message: ' . $e->getMessage() . ' | payload=' . json_encode([
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'image' => $image,
                        ],
                        'data' => $data,
                    ], JSON_UNESCAPED_UNICODE),
                    __METHOD__
                );

                $result[] = [
                    'token' => $token,
                    'status' => 'error',
                    'message' => 'Error sending notification: ' . $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    /**
     * Send FCM notifications to a device with an audible alert.
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param String|null $image
     * @param array|null $data
     * @param AndroidConfig|null $android Sound settings for Android
     * @param ApnsConfig|null $apns sound settings for Ios
     * @return array Notification results
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function sendNotificationWithSound(array $tokens, string $title, string $body, ?string $image, ?array $data, ?AndroidConfig $android, ?ApnsConfig $apns): array
    {
        $result = [];
        $notification = Notification::create($title, $body, $image);
        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)->withData($data)->withAndroidConfig($android)->withApnsConfig($apns);

                $result[] = [
                    'token' => $token,
                    'status' => 'success',
                    'message' => 'Notification sent successfully.',
                    'result' => $this->messaging->send($message),
                ];
            } catch (Exception $e) {
                $result[] = [
                    'token' => $token,
                    'status' => 'error',
                    'message' => 'Error sending notification: ' . $e->getMessage(),
                ];
            }
        }

        return $result;
    }
}
