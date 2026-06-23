<?php

namespace app\modules\google\controllers;

use app\services\GoogleService;
use app\services\SystemConfigurationService;
use yii\rest\ActiveController;

class GoogleExcelController extends ActiveController
{
    public $modelClass = 'app\models\google\Google';
    public $systemConfigurationService;
    public $googleService;

    public function init()
    {
        parent::init();
        $this->systemConfigurationService = new SystemConfigurationService();
        $this->googleService = new GoogleService();
    }

    public function actionInsert()
    {
        $booking = $this->googleService->getBookingSendByMail();
        $requestCallback = $this->googleService->getRequestCallBackByMail();
        $combinedValues = array_merge($booking['data'], $requestCallback['data']);
        if (isset($combinedValues) && is_array($combinedValues) && count($combinedValues)) {
            $client = $this->googleService->getClient();
            $spreadsheetId = GOOGLE_ID_SHEET;
            $lastRowData = $this->googleService->getDataFromSheet($client, $spreadsheetId, 'B', 3);
            $this->googleService->clearDataGoogleExcel($client, $spreadsheetId, count($lastRowData));
            $this->googleService->insertDataToSheet($client, $spreadsheetId, 'B4', $combinedValues);
            $this->googleService->updateBatchGoogleExcel($booking['id'], $requestCallback['id']);
        }
        pre($combinedValues);
    }
}
