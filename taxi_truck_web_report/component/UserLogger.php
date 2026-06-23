<?php

namespace app\component;

use Yii;
use yii\base\Component;

class UserLogger extends Component
{
    public function logUserAction(array $data)
    {
        $logDir = Yii::getAlias('@app/log/user_logs');
        if (! is_dir($logDir)) {
            mkdir($logDir, 0666, true);
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
        file_put_contents($logFilePath, json_encode($logList));
    }
}
