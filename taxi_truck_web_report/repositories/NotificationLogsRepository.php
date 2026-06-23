<?php

namespace app\repositories;

use app\models\NotificationLogs;
use Yii;
use yii\db\Exception;

class NotificationLogsRepository extends BaseRepository
{
    /**
     * Constructor binds the NotificationLogs model to the repository.
     */
    public function __construct()
    {
        parent::__construct(new NotificationLogs());
    }

    /**
     * Find notifications by user ID.
     *
     * @param int $userId
     * @return NotificationLogs[]
     * @throws Exception
     */
    public function findByUserId(int $userId): array
    {
        try {
            return $this->findAll(['user_id' => $userId]);
        } catch (\Throwable $e) {
            Yii::error("Error finding notifications by user ID: {$userId}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to find notifications for user.');
        }
    }

    /**
     * Find notifications by driver ID.
     *
     * @param int $driverId
     * @return NotificationLogs[]
     * @throws Exception
     */
    public function findByDriverId(int $driverId): array
    {
        try {
            return $this->findAll(['driver_id' => $driverId]);
        } catch (\Throwable $e) {
            Yii::error("Error finding notifications by driver ID: {$driverId}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to find notifications for driver.');
        }
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function markAsRead(int $id): bool
    {
        try {
            $notification = $this->findById($id);

            if (! $notification) {
                throw new Exception("Notification with ID {$id} not found.");
            }

            $notification->status = 1; // Assuming 1 means "read"

            return $notification->save();
        } catch (\Throwable $e) {
            Yii::error("Error marking notification as read. ID: {$id}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to mark notification as read.');
        }
    }

    /**
     * Get unread notifications for a user.
     *
     * @param int $userId
     * @return NotificationLogs[]
     * @throws Exception
     */
    public function getUnreadNotificationsByUserId(int $userId): array
    {
        try {
            return $this->findAll(['user_id' => $userId, 'status' => 0]); // Assuming 0 means "unread"
        } catch (\Throwable $e) {
            Yii::error("Error retrieving unread notifications for user ID: {$userId}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to retrieve unread notifications.');
        }
    }

    /**
     * Delete all notifications for a specific user.
     *
     * @param int $userId
     * @return int Number of deleted rows
     * @throws Exception
     */
    public function deleteByUserId(int $userId): int
    {
        try {
            return NotificationLogs::deleteAll(['user_id' => $userId]);
        } catch (\Throwable $e) {
            Yii::error("Error deleting notifications for user ID: {$userId}. Error: " . $e->getMessage(), __METHOD__);

            throw new Exception('Failed to delete notifications for user.');
        }
    }
}
