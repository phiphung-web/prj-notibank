<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\services\NotificationService;

/**
 * Queue management controller
 *
 * Usage:
 * php yii queue/start - Start queue listener
 * php yii queue/test - Test notification with 6000 tokens
 * php yii queue/clear - Clear all queue jobs
 * php yii queue/info - Show queue information
 */
class QueueController extends Controller
{
    /**
     * Start queue listener with specified workers
     *
     * @param int $workers Number of workers (default: 3)
     * @param int $verbose Verbose level (0-3, default: 1)
     */
    public function actionStart($workers = 3, $verbose = 1)
    {
        $this->stdout("Starting queue listener with {$workers} workers...\n");
        $this->stdout("Verbose level: {$verbose}\n");
        $this->stdout("Press Ctrl+C to stop\n\n");

        // Build the command
        $command = "php yii queue/listen {$workers} --verbose={$verbose}";

        // Execute the command
        $this->stdout("Executing: {$command}\n");
        $this->stdout("----------------------------------------\n");

        // Use passthru to show real-time output
        passthru($command);

        return ExitCode::OK;
    }

    /**
     * Clear all queue jobs
     */
    public function actionClear()
    {
        $this->stdout("Clearing queue jobs...\n");

        try {
            $queue = Yii::$app->queue;
            $queue->clear();

            $this->stdout("Queue cleared successfully!\n");

            // Also clear Redis keys
            $redis = Yii::$app->redis;
            $channel = $queue->channel;

            $keys = $redis->keys("*{$channel}*");
            if (!empty($keys)) {
                foreach ($keys as $key) {
                    $redis->del($key);
                    $this->stdout("Deleted key: {$key}\n");
                }
            }

            $this->stdout("All queue data cleared!\n");

        } catch (\Exception $e) {
            $this->stderr("Error clearing queue: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Show queue information
     */
    public function actionInfo()
    {
        $this->stdout("Queue Information:\n");
        $this->stdout("==================\n");

        try {
            $queue = Yii::$app->queue;
            $redis = Yii::$app->redis;
            $channel = $queue->channel;

            // Get queue stats
            $waiting = $redis->llen("{$channel}.waiting");
            $delayed = $redis->zcard("{$channel}.delayed");
            $reserved = $redis->zcard("{$channel}.reserved");
            $done = $redis->zcard("{$channel}.done");

            $this->stdout("Channel: {$channel}\n");
            $this->stdout("Waiting jobs: {$waiting}\n");
            $this->stdout("Delayed jobs: {$delayed}\n");
            $this->stdout("Reserved jobs: {$reserved}\n");
            $this->stdout("Done jobs: {$done}\n");

            $total = $waiting + $delayed + $reserved;
            $this->stdout("Total pending: {$total}\n");

        } catch (\Exception $e) {
            $this->stderr("Error getting queue info: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Start queue in background (for server deployment)
     */
    public function actionStartBackground($workers = 3, $verbose = 1)
    {
        $this->stdout("Starting queue listener in background...\n");

        $command = "php yii queue/listen {$workers} --verbose={$verbose}";

        // For Windows
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $command = "start /B {$command}";
        } else {
            // For Linux/Unix
            $command = "nohup {$command} > /dev/null 2>&1 &";
        }

        $this->stdout("Executing: {$command}\n");

        // Execute in background
        exec($command);

        $this->stdout("Queue listener started in background\n");
        $this->stdout("Use 'php yii queue/stop' to stop it\n");

        return ExitCode::OK;
    }

    /**
     * Stop background queue processes
     */
    public function actionStop()
    {
        $this->stdout("Stopping queue processes...\n");

        // Find and kill queue processes
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            exec('taskkill /F /IM php.exe /FI "WINDOWTITLE eq *queue/listen*"');
        } else {
            // Linux/Unix
            exec('pkill -f "queue/listen"');
        }

        $this->stdout("Queue processes stopped\n");

        return ExitCode::OK;
    }

}
