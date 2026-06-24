<?php

namespace app\services;

use app\models\Booking;
use app\models\RequestCallBack;
use Google\Client;
use Google\Service\Sheets;
use Yii;
use yii\base\Component;
use yii\db\Expression;

class GoogleService extends Component
{
    public function getClient()
    {
        $client = new Client();
        $client->setApplicationName(GOOGLE_APP_NAME);
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(Yii::getAlias('@app/web/upload/credentials.json'));
        $client->setAccessType('offline');

        return $client;
    }

    public function authenticateAndGetToken($authCode)
    {
        $client = $this->getClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);

        return $accessToken;
    }

    public function insertDataToSheet($client, $spreadsheetId, $range, $values)
    {
        $service = new Sheets($client);
        $body = new \Google_Service_Sheets_ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'RAW'];

        return $service->spreadsheets_values->update($spreadsheetId, $range, $body, $params);
    }

    public function getDataFromSheet($client, $spreadsheetId, $startColumn, $startRow)
    {
        $service = new Sheets($client);
        $range = $startColumn . $startRow . ':' . $startColumn . '';

        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);

            return $response->getValues();
        } catch (\Google\Service\Exception $e) {
            return [];
        }
    }

    public function updateBatchGoogleExcel($bookingIds, $requestCallbackIds)
    {
        Booking::updateAll(['google_excel' => 1], ['id' => $bookingIds]);
        RequestCallBack::updateAll(['google_excel' => 1], ['id' => $requestCallbackIds]);
    }

    private function processData($list, $key, $phoneColumn)
    {
        $values = $ids = [];

        foreach ($list as $item) {
            $values[] = [
                '84' . ltrim($item[$phoneColumn], '0'),
                'OCT Conver',
                date('m/d/Y 06:00:00', strtotime(date('m/d/Y'))),
            ];
            $ids[] = $item['id'];
        }

        return ['data' => $values, 'id' => $ids, 'key' => $key];
    }

    public function getBookingSendByMail()
    {
        $bookingList = Booking::find()
            ->where(['google_excel' => 0])
            ->andWhere(['IN', 'type', [SOURCE_TRIP_TYPE_MAIL_1, SOURCE_TRIP_TYPE_AGENCY, SOURCE_TRIP_TYPE_MAIL_2, SOURCE_TRIP_TYPE_CALL_1, SOURCE_TRIP_TYPE_CALL_2]])
            ->andWhere(['>=', 'created_on', new Expression('CURDATE() - INTERVAL 1 DAY')])
            ->andWhere(['<', 'created_on', new Expression('CURDATE()')])
            ->orderBy(['created_on' => SORT_ASC])
            ->asArray()
            ->all();

        return $this->processData($bookingList, 'booking', 'customer_phone');
    }

    public function getRequestCallBackByMail()
    {
        $requestCallbackList = RequestCallBack::find()
            ->where(['google_excel' => 0])
            ->andWhere(['>=', 'created_on', new Expression('CURDATE() - INTERVAL 1 DAY')])
            ->andWhere(['<', 'created_on', new Expression('CURDATE()')])
            ->asArray()
            ->all();
        $validRequestCallbackList = array_filter($requestCallbackList, function ($requestCallback) {
            return $this->isValidVietnamesePhoneNumber($requestCallback['phone']);
        });

        return $this->processData($validRequestCallbackList, 'requestCallback', 'phone');
    }

    private function isValidVietnamesePhoneNumber($phoneNumber)
    {
        return preg_match('/^0\d{9}$/', $phoneNumber);
    }

    public function clearDataGoogleExcel($client, $spreadsheetId, $count)
    {
        $valueEmpty = [];
        for ($i = 0; $i < $count; $i++) {
            $valueEmpty[$i] = [0 => '', 1 => '', 2 => '',];
        }
        $service = new Sheets($client);
        $body = new \Google_Service_Sheets_ValueRange(['values' => $valueEmpty]);
        $params = ['valueInputOption' => 'RAW'];

        return $service->spreadsheets_values->update($spreadsheetId, 'B4', $body, $params);
    }
}
