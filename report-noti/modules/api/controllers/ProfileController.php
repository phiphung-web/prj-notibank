<?php

namespace app\modules\api\controllers;

use app\helpers\MyHelper;
use app\helpers\ResponseHelper;
use app\models\AccessToken;
use app\models\Agency;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\rest\ActiveController;
use yii\web\MethodNotAllowedHttpException;

class ProfileController extends ActiveController
{
    public $modelClass = 'app\models\Admin';
    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
    }

    public function actionDetail()
    {
        if (! Yii::$app->request->isGet) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        try {
            $user = AccessToken::findByToken(Yii::$app->request->getHeaders()->get('authorization'));
            if ($user) {
                $userName = $user->username;
                $email = $user->email;
                $phone = $user->phone;
                $agencyId = $user->agency_id;
                if ($agencyId != null) {
                    $agency = Agency::find()->where(['id' => $agencyId])->select(['id', 'parent_id', 'name', 'address', 'phone', 'email', 'contact_person', 'note', 'token', 'qr_code'])->one();
                    ;
                } else {
                    $agency = '';
                }
                $data = [
                    'username' => $userName,
                    'email' => $email,
                    'phone' => $phone,
                    'agency' => $agency,
                ];

                return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $data);
            }
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('PayTransactionController - actionRecharge() - ' . $e->getMessage());
        }
    }
}
