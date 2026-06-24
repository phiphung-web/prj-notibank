<?php

namespace app\models;

use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $new_password;
    public $confirm_password;

    public function rules()
    {
        return [
            [['new_password', 'confirm_password'], 'required', 'message' => 'Vui lòng nhập đầy đủ thông tin.'],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'message' => 'Mật khẩu nhập lại không khớp.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'new_password' => 'Mật khẩu mới',
            'confirm_password' => 'Nhập lại mật khẩu',
        ];
    }
}
