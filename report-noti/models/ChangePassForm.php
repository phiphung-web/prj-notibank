<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class ChangePassForm extends Model
{
    public $oldpass;
    public $newpass;
    public $repeatnewpass;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['oldpass','newpass','repeatnewpass'],'required'],
            ['oldpass','findPasswords'],
            ['repeatnewpass','compare','compareAttribute' => 'newpass'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function findPasswords($attribute, $params)
    {
        $user = Admin::find()->where([
            'username' => Yii::$app->user->identity->username,
        ])->one();
        $password = $user->password;
        if ($password != md5($this->oldpass)) {
            $this->addError($attribute, 'Old password is incorrect');
        }
    }

    public function attributeLabels()
    {
        return [
            'oldpass' => 'Old Password',
            'newpass' => 'New Password',
            'repeatnewpass' => 'Repeat New Password',
        ];
    }
}
