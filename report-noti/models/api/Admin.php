<?php

namespace app\models\api;

use app\rbac\models\Role;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the admin model class extending AdminIdentity.
 * Here you can implement your custom admin solutions.
 *
 * @property Role[] $role
 * @property Article[] $articles
 */
class Admin extends AdminIdentity
{
    // the list of status values that can be stored in admin table
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    /**
     * List of names for each status.
     * @var array
     */
    public $statusList = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique'],
            [['consumer'], 'safe'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['status', 'required'],
        ];
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Tài khoản'),
            'password' => Yii::t('app', 'Mật khẩu'),
            'email' => Yii::t('app', 'Email'),
            'status' => Yii::t('app', 'Trạng thái'),
        ];
    }

    //------------------------------------------------------------------------------------------------//
    // ADMIN FINDERS
    //------------------------------------------------------------------------------------------------//

    /**
     * Finds admin by username.
     *
     * @param  string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => Admin::STATUS_ACTIVE]);
    }

    /**
     * Finds admin by email.
     *
     * @param  string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Finds admin by password reset token.
     *
     * @param  string $token Password reset token.
     * @return null|static
     */
    public static function findByPasswordResetToken($token)
    {
        if (! static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => Admin::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds admin by account activation token.
     *
     * @param  string $token Account activation token.
     * @return static|null
     */
    public static function findByAccountActivationToken($token)
    {
        return static::findOne([
            'account_activation_token' => $token,
            'status' => Admin::STATUS_INACTIVE,
        ]);
    }


    //------------------------------------------------------------------------------------------------//
    // HELPERS
    //------------------------------------------------------------------------------------------------//

    /**
     * Returns the admin status in nice format.
     *
     * @param  int $status Status integer value.
     * @return string          Nicely formatted status.
     */
    public function getStatusName($status)
    {
        return $this->statusList[$status];
    }

    /**
     * Generates new password reset token.
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Finds out if password reset token is valid.
     *
     * @param  string $token Password reset token.
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['admin.passwordResetTokenExpire'];

        return $timestamp + $expire >= time();
    }

    /**
     * Generates new account activation token.
     */
    public function generateAccountActivationToken()
    {
        $this->account_activation_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes account activation token.
     */
    public function removeAccountActivationToken()
    {
        $this->account_activation_token = null;
    }
}
