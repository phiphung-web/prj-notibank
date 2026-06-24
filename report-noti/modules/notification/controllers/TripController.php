<?php

namespace app\modules\notification\controllers;

use app\helpers\MyHelper;
use app\helpers\ResponseHelper;
use app\services\CalculationFormulaService;
use app\services\CallService;
use app\services\SearchService;
use Yii;
use yii\base\ErrorException;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;

class TripController extends Controller
{
    public string $modelClass = 'app\models\NotificationLogs';
    public $calculationFormulaService;
    public $callService;
    public $searchService;

    public function init()
    {
        parent::init();
        $this->callService = new CallService();
        $this->calculationFormulaService = new CalculationFormulaService();
        $this->searchService = new SearchService();
    }

    public function actionAddressStart()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $keyword = Yii::$app->request->get('keyword');
            $addressList = $this->callService->searchAddress(! empty($keyword) ? $keyword : '');
            if (! empty($keyword)) {
                Yii::$app->apiLogger->logApiAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'agency_id' => 100, // Id của Én Việt
                    'user_name' => 'Khách hàng đại lý Én việt',
                    'message' => Yii::t('app', 'address_start', [
                        'keyword' => $keyword,
                    ]),
                    'data' => $addressList,
                    'action' => ACTION_SEARCH_LOG,
                ]);
            }

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $addressList);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('SearchController - actionAddressStart() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }
}
