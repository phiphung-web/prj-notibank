<?php

namespace app\services;

use app\jobs\SendNotificationJob;
use app\models\Admin;
use app\models\Driver;
use app\models\Trip;
use app\repositories\DriverTokenRepository;
use app\repositories\NotificationLogsRepository;
use Yii;

/**
 * Class NotificationService
 *
 * This class handles the trip-related operations.
 */
class NotificationService
{
    protected $driverTokenRepository;

    public function __construct()
    {
        $this->driverTokenRepository = new DriverTokenRepository();
    }

    /**
     * Save FCM token for a driver.
     *
     * @param string $username
     * @param string $token
     * @return array
     */
    public function saveToken(string $username, string $token): array
    {
        if (empty($username) || empty($token)) {
            return [
                'status' => 'error',
                'message' => 'Token or username is missing.',
            ];
        }

        $driver = Driver::findOne(['username' => $username]);

        if (!$driver) {
            return [
                'status' => 'error',
                'message' => 'Driver not found with the provided username.',
            ];
        }

        $driver->token_fcm = $token;

        if ($driver->save(false)) {
            return [
                'status' => 'success',
                'message' => 'FCM token saved successfully.',
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Failed to save FCM token.',
        ];
    }

    /**
     * Gửi thông báo FCM dựa trên username của driver.
     *
     * @param Driver $driver
     * @param Admin|null $admin
     * @param Trip|null $trip
     * @param string $title
     * @param string $message
     * @return array
     */
    public function sendNotificationByUsername(Driver $driver, ?Admin $admin, ?Trip $trip, string $title, string $message, ?string $image = '', ?array $data = []): array
    {
        $driverTokens = $this->driverTokenRepository->findValidTokenByDriverId($driver->id);
        if (empty($driverTokens)) {
            // Lưu log cho trường hợp thất bại
            $this->logNotification($trip->id ?? null, $admin->id ?? null, $driver->id ?? null, $title, $message, null);

            return [
                'status' => 'error',
                'message' => 'Không tìm thấy lái xe hoặc thiếu mã thông báo FCM',
            ];
        }
        $tokens = array_map(function ($driverToken) {
            return $driverToken['token'];
        }, $driverTokens);

        // Gửi thông báo thông qua FirebaseService
        $firebaseService = Yii::$app->firebaseService;
        $firebaseResponse = $firebaseService->sendNotification($tokens, $title, $message, $image, $data);
        $this->logNotification($trip->id ?? null, $admin->id ?? null, $driver->id ?? null, $title, $message, json_encode($firebaseResponse));

        return [
            'status' => 'success',
            'message' => 'Notification sent successfully.',
            'result' => $firebaseResponse,
        ];
    }

    public function sendMessageAllDriver($params = [])
    {
        $allTokens = $this->driverTokenRepository->findAllValidTokens();
        // Check if tokens are empty
        if (empty($allTokens)) {
            return [
                'status' => 'error',
                'message' => 'Không tìm thấy token của tài xế nào trong hệ thống.',
                'total_tokens' => 0
            ];
        }

        // Use default value if not in $params
        $title = $params['title'] ?? 'Thông báo chung';
        $content = $params['content'] ?? 'Bạn có thông báo mới từ hệ thống.';
        $image = $params['image'] ?? '';
        $data = $params['data'] ?? [];

        // Send notifications via FirebaseService
        // Chunk tokens into batches of 50 for better performance and error handling
        $chunkSize = 50;
        $chunks = array_chunk($allTokens, $chunkSize);
        $queuedJobs = 0;

        foreach ($chunks as $tokens) {
            $job = new SendNotificationJob([
                'tokens' => $tokens,
                'title' => $title,
                'content' => $content,
                'image' => $image,
                'data' => $data,
                'maxAttempts' => 3,
                'ttr' => 60,
            ]);

            Yii::$app->queue->push($job);
            $queuedJobs++;
        }
        return [
            'status' => 'queued',
            'message' => "Đã đẩy {$queuedJobs} công việc gửi thông báo vào hàng đợi.",
            'total_tokens' => count($allTokens),
        ];
    }

    protected function logNotification(?int $tripId, ?int $userId, ?int $driverId, string $title, string $message, ?string $messageData, $status = 1): void
    {
        try {
            // Sử dụng repository để lưu log
            $notificationLogsRepo = new NotificationLogsRepository();
            $notificationLogsRepo->create([
                'trip_id' => $tripId,
                'user_id' => $userId,
                'driver_id' => $driverId,
                'type' => 0,  // Tùy chỉnh loại thông báo
                'title' => $title,
                'message' => $message,
                'message_data' => $messageData,
                'status' => $status,
                'created_on' => time(),
                'updated_on' => time(),
            ]);
        } catch (\Throwable $e) {
            pre($e->getMessage());
            Yii::error('Failed to save notification log: ' . $e->getMessage(), __METHOD__);
        }
    }
}
