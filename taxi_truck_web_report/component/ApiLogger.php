<?php

namespace app\component;

use Yii;
use yii\base\Component;

class ApiLogger extends Component
{
    private const MAX_LOG_ITEMS_PER_DAY = 500;

    public function logApiAction(array $data)
    {
        $logDir = Yii::getAlias('@app/log/api_logs');
        if (! is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        $fileName = date('Y-m-d') . '.json';
        $logFilePath = $logDir . DIRECTORY_SEPARATOR . $fileName;
        if (! file_exists($logFilePath)) {
            $fp = fopen($logFilePath, 'wb');
            fwrite($fp, json_encode([]));
            fclose($fp);
        }
        $file = file_get_contents($logFilePath);
        $file = json_decode($file, true) ?? [];
        $logList = array_merge([$data], $file);
        $logList = array_slice($logList, 0, self::MAX_LOG_ITEMS_PER_DAY);
        file_put_contents($logFilePath, json_encode($logList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}
