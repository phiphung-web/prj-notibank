<?php

namespace app\modules\api\controllers;

use app\helpers\BehaviorsFromParamsHelper;
use app\helpers\ResponseHelper;
use app\models\api\SearchBookingApi;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;

class BookingController extends ActiveController
{
    public $modelClass = 'app\models\api\SearchBookingApi';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = BehaviorsFromParamsHelper::behaviors($behaviors);

        return $behaviors;
    }

    public function actionList()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }
        if (! Yii::$app->user->can('/api/booking/list') && ! Yii::$app->user->can('/api/booking/*')) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        $searchModel = new SearchBookingApi();
        $param = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($param);
        $models = $dataProvider->getModels();
        $dataArray = ArrayHelper::toArray($models);
        if (isset($dataArray) && is_array($dataArray) && count($dataArray)) {
            foreach ($dataArray as $key => $value) {
                $dataArray[$key]['type_of_car_value'] = TYPE_OF_CAR_LIST[$value['type_of_car']];
            }
        }

        return ResponseHelper::renderResponse(200, 'Thu thập dữ liệu thành công!', [
            'records' => $dataArray,
            'pagination' => [
                'total' => $dataProvider->getTotalCount(),
                'page' => $dataProvider->pagination->page + 1,
                'pageSize' => $dataProvider->pagination->pageSize,
            ],
        ]);
    }
}
