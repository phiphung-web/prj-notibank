<?php

namespace app\models\api;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * AdminIdentity class for "admin" table.
 * This is a base admin class that is implementing IdentityInterface.
 * Admin model should extend from this model, and other admin related models should
 * extend from Admin model.
 *
 * @property int $id
 * @property string  $username
 * @property string  $password
 * @property string  $password_reset_token
 * @property string  $email
 * @property string  $consumer
 * @property string  $access_given
 * @property string  $account_activation_token
 * @property string  $auth_key
 * @property int $status
 * @property int $created_on
 * @property int $updated_on
 */
class AdminIdentity extends ActiveRecord implements IdentityInterface
{
    public $consumer;
    /**
     * Declares the name of the database table associated with this AR class.
     *
     * @return string
     */
    public static function tableName()
    {
        return '{{%admin}}';
    }

    //------------------------------------------------------------------------------------------------//
    // IDENTITY INTERFACE IMPLEMENTATION
    //------------------------------------------------------------------------------------------------//

    /**
     * Finds an identity by the given ID.
     *
     * @param  int|string $id The admin id.
     * @return IdentityInterface|static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => Admin::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given access token.
     *
     * @param  mixed $token
     * @param  null  $type
     * @return void|IdentityInterface
     *
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $accessToken = AccessToken::find()->where(['token' => $token])->andWhere(['>', 'expire_on', strtotime('now')])->one();
        if (! $accessToken) {
            return $accessToken;
        }

        return Admin::findOne(['id' => $accessToken->admin_id]);
        // return Admin::findOne(['auth_key' => $token, 'status' => Admin::STATUS_ACTIVE]);
    }

    /**
     * Returns an ID that can uniquely identify a admin identity.
     *
     * @return int|mixed|string
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given
     * identity ID. The key should be unique for each individual admin, and
     * should be persistent so that it can be used to check the validity of
     * the admin identity. The space of such keys should be big enough to defeat
     * potential identity attacks.
     *
     * @return string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     *
     * @param  string  $authKey The given auth key.
     * @return bool          Whether the given auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    //------------------------------------------------------------------------------------------------//
    // IMPORTANT IDENTITY HELPERS
    //------------------------------------------------------------------------------------------------//

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
        AccessToken::generateAuthKey($this);
    }

    /**
     * Validates password.
     *
     * @param  string $password
     * @return bool
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function validatePassword($password)
    {
        return $this->password === md5($password);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param  string $password
     *
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }
}
