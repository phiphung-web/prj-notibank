<?php

namespace app\modules\api\controllers;

use app\models\Admin;
use app\models\Status;
use Yii;
use yii\rest\Controller;
use yii\web\MethodNotAllowedHttpException;

class AuthController extends Controller
{
    public function actionLogin()
    {
        if (! Yii::$app->request->isPost) {
            throw new MethodNotAllowedHttpException('Method Not Allowed');
        }

        $params = Yii::$app->request->post();
        if (empty($params['username']) || empty($params['password'])) {
            return [
                'status' => Status::STATUS_BAD_REQUEST,
                'message' => 'Xin vui lòng nhập tài khoản và mật khẩu!',
                'data' => '',
            ];
        }

        $admin = Admin::findByUsername($params['username']);
        if (isset($admin) && $admin->validatePassword($params['password'])) {
            if (isset($params['consumer'])) {
                $admin->consumer = $params['consumer'];
            }
            Yii::$app->response->statusCode = Status::STATUS_OK;
            $admin->generateAuthKey();
            $admin->save();

            return [
                'status' => Status::STATUS_OK,
                'message' => 'Đăng nhập thành công!',
                'data' => [
                    'id' => $admin->username,
                    'token' => $admin->auth_key,
                    'username' => $admin['username'],
                    'email' => $admin['email'],
                    'phone' => $admin['phone'],
                ],
            ];
        } else {
            Yii::$app->response->statusCode = Status::STATUS_UNAUTHORIZED;

            return [
                'status' => Status::STATUS_UNAUTHORIZED,
                'message' => 'Tài khoản hoặc mật khẩu không chính xác!',
                'data' => '',
            ];
        }
    }
}
