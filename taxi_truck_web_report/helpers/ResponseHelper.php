<?php

namespace app\helpers;

use Yii;
use yii\web\Response;

class ResponseHelper
{
    public static function renderResponse($code = 200, $message = '', $array = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'status' => $code,
            'message' => $message,
            'data' => $array,
        ];
    }
}
