<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * BaseController is an extended controller that enforces account status check.
 */
class BaseController extends Controller
{
    public $roleCurrentUser;
    public $listPermission;

    /**
     * Initializes the controller and enforces account status check.
     */
    public function init()
    {
        // Check if the user is logged in and their status is not 1
        if (! Yii::$app->user->isGuest && Yii::$app->user->identity->status !== 1) {
            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'Tài khoản bị khóa, liên hệ quản lý.');
            header('Location: ' . Url::base(true) . '/admin/login');
        }
        // Lấy danh sách các route được phân cho vai trò của người dùng
        $this->roleCurrentUser = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
        // Khởi tạo mảng để chứa danh sách các route được phân cho vai trò của người dùng
        $this->listPermission = [];

        foreach ($this->roleCurrentUser as $role) {
            $permissions = Yii::$app->authManager->getPermissionsByRole($role->name);
            foreach ($permissions as $permission) {
                $this->listPermission[] = $permission->name;
            }
        }
        // Call the parent's init method
        parent::init();
    }
}
