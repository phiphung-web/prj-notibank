<?php

namespace app\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\RetryableJobInterface;
use yii\log\Logger;
use yii\helpers\Console;
use app\repositories\DriverTokenRepository;

class SendNotificationJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    public array $tokens = [];
    public string $title = '';
    public string $content = '';
    public string $image = '';
    public array $data = [];

    // Retry configuration
    public int $maxAttempts = 3;
    public int $ttr = 30;

    // Job metadata
    public string $jobId = '';
    public int $attempt = 1;
    public ?string $errorMessage = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->jobId = uniqid('notification_', true);
    }
    public function execute($queue)
    {
        try {
            $firebaseService = Yii::$app->firebaseService;
            $result = $firebaseService->sendNotification(
                $this->tokens,
                $this->title,
                $this->content,
                $this->image,
                $this->data
            );

            $this->processResult($result);
        } catch (\Exception $e) {
            $this->handleException($e);
            Console::output(Console::ansiFormat("❌ [FAIL] Job {$this->jobId} failed: " . $e->getMessage(), [Console::FG_RED]));
            throw $e;
        }
    }

    private function processResult(array $result): void
    {
        $successCount = 0;
        $retryableErrors = [];
        $nonRetryableErrors = [];

        foreach ($result as $item) {
            if ($item['status'] === 'success') {
                $successCount++;
            } else {
                $errorCode = $this->extractErrorCode($item['message']);
                if ($this->isRetryableError($errorCode)) {
                    $retryableErrors[] = $item;
                } else {
                    $nonRetryableErrors[] = $item;
                }
            }
        }

        // Invalid processing token
        $invalidTokens = [];
        foreach ($nonRetryableErrors as $errorItem) {
            $badToken = $errorItem['token'] ?? null;
            if ($badToken) {
                $invalidTokens[$badToken] = true;
            }
        }

        if (!empty($invalidTokens)) {
            $repo = new DriverTokenRepository();
            $deleted = $repo->deleteAllByTokens(array_keys($invalidTokens));
        }

        // If there is a temporary error → throw to queue retry
        if (!empty($retryableErrors) && $this->attempt < $this->maxAttempts) {
            $this->errorMessage = "Retryable errors found: " . count($retryableErrors) . " tokens failed";
            throw new \Exception($this->errorMessage);
        }
    }

    /**
     * Extract error code from Firebase error message
     */
    private function extractErrorCode(string $message): string
    {
        // Common Firebase error patterns
        $patterns = [
            '/unregistered/i' => 'UNREGISTERED',
            '/invalid-argument/i' => 'INVALID_ARGUMENT',
            '/quota-exceeded/i' => 'QUOTA_EXCEEDED',
            '/unavailable/i' => 'UNAVAILABLE',
            '/internal/i' => 'INTERNAL',
            '/sender-id-mismatch/i' => 'SENDER_ID_MISMATCH',
            '/third-party-auth-error/i' => 'THIRD_PARTY_AUTH_ERROR',
        ];

        foreach ($patterns as $pattern => $code) {
            if (preg_match($pattern, $message)) {
                return $code;
            }
        }

        return 'UNSPECIFIED_ERROR';
    }

    /**
     * Determine if error is retryable
     */
    private function isRetryableError(string $errorCode): bool
    {
        $retryableErrors = [
            'UNAVAILABLE',
            'INTERNAL',
            'QUOTA_EXCEEDED',
            'THIRD_PARTY_AUTH_ERROR',
        ];

        $nonRetryableErrors = [
            'UNREGISTERED',
            'SENDER_ID_MISMATCH',
            'UNSPECIFIED_ERROR'
        ];

        return in_array($errorCode, $retryableErrors);
    }

    /**
     * Handle exceptions during job execution
     */
    private function handleException(\Exception $e): void
    {
        $this->errorMessage = $e->getMessage();
        Yii::error("Job {$this->jobId} failed on attempt {$this->attempt}: {$this->errorMessage}", 'notification');

        // Log additional context
        Yii::error([
            'job_id' => $this->jobId,
            'attempt' => $this->attempt,
            'tokens_count' => count($this->tokens),
            'title' => $this->title,
            'error' => $this->errorMessage,
            'trace' => $e->getTraceAsString()
        ], 'notification');
    }

    /**
     * RetryableJobInterface implementation
     */
    public function canRetry($attempt, $error): bool
    {
        if ($attempt >= $this->maxAttempts) {
            Yii::warning("Job {$this->jobId} exceeded max attempts ({$this->maxAttempts})", 'notification');
            return false;
        }

        // If it's a known non-retryable error, don't retry
        if ($this->errorMessage && $this->extractErrorCode($this->errorMessage) !== 'UNSPECIFIED_ERROR') {
            $errorCode = $this->extractErrorCode($this->errorMessage);
            if (!$this->isRetryableError($errorCode)) {
                Yii::info("Job {$this->jobId} failed with non-retryable error: {$errorCode}", 'notification');
                return false;
            }
        }

        return true;
    }

    public function getTtr(): int
    {
        return $this->ttr;
    }

}
