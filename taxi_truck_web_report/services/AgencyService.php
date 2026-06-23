<?php

namespace app\services;

use Da\QrCode\QrCode;
use Yii;
use yii\base\Component;
use yii\base\Exception;

class AgencyService extends Component
{
    /**
     * @throws Exception
     */
    public function createTokenAndQrCode()
    {
        $data['token'] = Yii::$app->security->generateRandomString(64);
        $data['qrCode'] = (new QrCode(URL_WEB_CLIENT . $data['token']))
            ->setSize(250)
            ->setMargin(5)
            ->setBackgroundColor(255, 255, 255)->writeDataUri();

        return $data;
    }
}
