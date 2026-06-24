<?php

namespace app\modules\cronjob\controllers;

use app\services\DriverService;
use yii\helpers\FileHelper;
use yii\rest\ActiveController;
class BidImageCleanupController extends ActiveController
{
    public $modelClass = 'app\models\Bid';
    public $basePath = '/var/www/report/web/upload/bids/';

    public function actionOld()
    {
        $now = new \DateTime();
        $limitDate = $now->modify('-2 months');

        if (!is_dir($this->basePath)) {
            echo "Base path not found\n";
            return;
        }

        $folders = scandir($this->basePath);

        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            // folder YYYY_MM_DD
            if (!preg_match('/^\d{4}_\d{2}_\d{2}$/', $folder)) continue;

            $folderDate = \DateTime::createFromFormat('Y_m_d', $folder)->format('Y-m-d');

            if (strtotime($folderDate) < strtotime($limitDate->format('Y-m-d'))) {
                $dirPath = $this->basePath . $folder;
                $this->deleteDirectory($dirPath);
                echo "Deleted: $dirPath\n";
            }
        }
    }

    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        if (!is_dir($dir)) {
            unlink($dir);
            return;
        }

        FileHelper::removeDirectory($dir);
    }
}
