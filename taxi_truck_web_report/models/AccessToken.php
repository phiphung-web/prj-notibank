<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * AccessToken Class for access_token table.
 * This is class to manage access_token than will be used in UserIdentity Class
 * UserIdentity class will find any token that active at current date and give Authorization based on access_token status
 *
 * @property int $id
 * @property string  $admin_id
 * @property string  $consumer
 * @property string  $token
 * @property string  $access_given
 * @property string  $used_at
 * @property string  $expire_at
 * @property int $created_at
 * @property int $updated_at
 * @property string  $defaultAccessGiven
 * @property int $defaultConsumern
 *
 * @method generateAuthKey($user)
 * @method makeAllUserTokenExpiredByUserId($userId)
 *
 * @author Heru Arief Wijaya @2020
 *
 */
class AccessToken extends ActiveRecord
{
    public $defaultAccessGiven = '{"access":["all"]}';
    public $defaultConsumer = 'mobile';

    /**
     * Declares the name of the database table associated with this AR class
     *
     * @return string
     */
    public static function tableName()
    {
        return 'access_token';
    }

    /**
     * Generate new access_token that will be used at Authorization
     *
     * @param object $user the User Object (User::findOne($id))
     * @return nothing
     */
    public static function generateAuthKey($user)
    {
        // $this->auth_key = Yii::$app->security->generateRandomString();
        $accessToken = new AccessToken();
        $accessToken->admin_id = $user->id;
        $accessToken->consumer = $user->consumer ?? $accessToken->defaultConsumer;
        $accessToken->access_given = $user->access_given ?? $accessToken->defaultAccessGiven;
        $accessToken->token = $user->auth_key;
        $accessToken->used_at = strtotime('now');
        if ($user->agency_id > 0) {
            $accessToken->expire_at = (30 * 24 * 3600) + strtotime('now');
        } else {
            $accessToken->expire_at = (999 * 24 * 3600) + strtotime('now');
        }
        $accessToken->created_at = strtotime('now');
        $accessToken->updated_at = strtotime('now');
        $accessToken->save();
    }

    /**
     * Make all user token based on any admin_id expired
     *
     * @param int @userId
     * @return nothing
     */
    public static function makeAllUserTokenExpiredByUserId($userId)
    {
        AccessToken::updateAll(['expire_at' => strtotime('now')], ['admin_id' => $userId]);
    }

    /**
     * Expire any access_token
     *
     * @return bool
     */
    public function expireThisToken()
    {
        $this->expire_at = strtotime('now');

        return $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'used_at', 'expire_at', 'created_at', 'updated_at'], 'integer'],
            [['token'], 'required'],
            [['access_given'], 'string'],
            [['consumer'], 'string', 'max' => 255],
            [['token'], 'string', 'max' => 32],
            [['token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'consumer' => 'Consumer',
            'token' => 'Token',
            'access_given' => 'Access Given',
            'used_at' => 'Used At',
            'expire_at' => 'Expire At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function findByToken($bearerToken)
    {
        // Tách phần "Bearer " khỏi token
        $token = str_replace('Bearer ', '', $bearerToken);

        $accessToken = self::find()
            ->where(['token' => $token])
            ->with('admin')
            ->andWhere(['>=', 'expire_at', strtotime('now')])
            ->one();

        return $accessToken->admin;
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['id' => 'admin_id']);
    }
}
