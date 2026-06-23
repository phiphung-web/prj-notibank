<?php

namespace app\models;

use app\helpers\MyStringHelper;
use Yii;

/**
 * This is the model class for table "admin".
 *
 * @property int $id
 * @property int $agency_id
 * @property string $username
 * @property string $password
 */
class Admin extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $role;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            ['role', 'safe'],
            [['first_use'], 'integer'],
            [['username', 'password'], 'required', 'on' => ['create']], // 'username' and 'password' are required during 'create' scenario
            [['username', 'password'], 'string', 'max' => 32, 'on' => ['create']], // 'username' and 'password' should be strings with max length of 32 during 'create' scenario
            [['email', 'bonus'], 'string', 'max' => 255],
            [['consumer'], 'safe'],
            [['phone'], 'string', 'max' => 20],
            [['email', 'phone'], 'default', 'value' => ''],
            ['email', 'email'],
            ['bonus', 'filter', 'filter' => function ($value) {
                return (int) str_replace('.', '', $value);
            }],
            [['status'], 'integer'],
            [['bonus'], 'match', 'pattern' => '/^[\d.]+$/'],
            [['bonus'], 'default', 'value' => '0'],
        ];

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agency_id' => 'Đại lý',
            'username' => 'Tài khoản',
            'password' => 'Mật khẩu',
            'phone' => 'Số điện thoại',
            'role' => 'Quyền',
            'bonus' => 'Tiền thưởng',
            'status' => 'Trạng thái',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $accessToken = AccessToken::find()->where(['token' => $token])->andWhere(['>', 'expire_at', strtotime('now')])->one();
        if (! $accessToken) {
            return $accessToken;
        }

        return Admin::findOne(['id' => $accessToken->admin_id]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return ''; //$this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return true;
        //return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->bonus = MyStringHelper::convertStringToInteger($this->bonus);
            return true;
        }

        return false;
    }

    public function isAdmin()
    {
        return $this->getRole() === 'ADMIN_ROLE';
    }

    public function getRole()
    {
        $auth = \app\models\AuthAssignment::find()->where(['user_id' => $this->id])->one();

        if ($auth instanceof \app\models\AuthAssignment) {
            return $auth->item_name;
        }

        return null;
    }

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        AccessToken::generateAuthKey($this);
    }
}
