<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $uid
 * @property string $username
 * @property string $mobile_phone
 * @property string $password
 * @property string $avatar
 * @property string $signature
 * @property integer $gender
 * @property string $city
 * @property string $province
 * @property string $birthday
 * @property string $taps
 * @property integer $status
 * @property string $register_ip
 * @property string $register_time
 * @property string $last_login_ip
 * @property string $last_login_time
 * @property string $last_modify_time
 * @property string password_hash
 * @property mixed auth_key
 * @property string password_reset_token
 * @property string access_token
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
//            TimestampBehavior::className(),
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            //用户名验证规则
            ['username', 'required'],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'string', 'min' => 2,'max' => 12, 'message' => '姓名长度限制 4-24个字符','tooShort' => '姓名长度限制 4-24个字符', 'tooLong' => '姓名长度限制 4-24个字符'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => '该用户名已经被占用'],

            //个性签名验证规则
            [['signature'], 'string', 'max' => 100, 'message' => '个性签名长度限制 最多100个字符','tooShort' => '个性签名长度限制 最多100个字符', 'tooLong' => '个性签名长度限制 最多100个字符'],

            ['auth_key', 'string', 'max' => 32],
            [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 256],
            [['status'], 'integer'],
            [['register_time', 'create_time', 'last_modify_time'], 'safe'],
            [['mobile_phone'], 'string', 'max' => 11],
            [['avatar'], 'string', 'max' => 2083],

            [['register_ip', 'last_login_ip'], 'string', 'max' => 15],
            ['status', 'default', 'value' => Yii::$app->params['STATUS_ACTIVE']],
            ['status', 'in', 'range' => [Yii::$app->params['STATUS_ACTIVE'],Yii::$app->params['STATUS_DELETED']]],

            //手机号验证规则
            ['mobile_phone', 'number'],
            ['mobile_phone', 'string', 'min' => 11],
            ['mobile_phone', 'filter', 'filter' => 'trim'],
            ['mobile_phone', 'unique', 'targetClass' => '\common\models\User', 'message' => '手机号已被使用'],

            //头像验证规则
            ['avatar', 'url', 'defaultScheme' => 'http','message' => '头像地址不是一个合法的url'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => Yii::t('app', '用户uid'),
            'username' => Yii::t('app', '姓名'),
            'auth_key' => Yii::t('app', 'Oauth Key'),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'email' => Yii::t('app', 'Email'),
            'mobile_phone' => Yii::t('app', '手机号码'),
            'avatar' => Yii::t('app', '头像'),
            'signature' => Yii::t('app', '个性签名'),
            'gender' => Yii::t('app', '性别'),
            'city' => Yii::t('app', '城市'),
            'province' => Yii::t('app', '省份'),
            'birthday' => Yii::t('app', '生日'),
            'taps' => Yii::t('app', '点击数'),
            'status' => Yii::t('app', '状态'),
            'register_ip' => Yii::t('app', '注册ip'),
            'register_time' => Yii::t('app', '注册时间'),
            'last_login_ip' => Yii::t('app', '最后登录ip'),
            'last_login_time' => Yii::t('app', '最后登录时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($uid)
    {
        // echo "findIdentity RUN!!! <br/>";
        return static::findOne(['uid' => $uid, 'status' => Yii::$app->params['STATUS_ACTIVE']]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * 生成access_token
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        // echo "findByUsername RUN!!! <br/>";
        return static::findOne(['username' => $username, 'status' => Yii::$app->params['STATUS_ACTIVE']]);
    }

    /**
     * Finds user by mobilePhone
     *
     * @param string $mobilePhone
     * @return static|null
     */
    public static function findByMobilePhone($mobilePhone)
    {
        return static::findOne(['mobile_phone' => $mobilePhone, 'status' => Yii::$app->params['STATUS_ACTIVE']]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        // echo "findByPasswordResetToken RUN!!! <br/>";
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => Yii::$app->params['STATUS_ACTIVE'],
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        // echo "isPasswordResetTokenValid RUN!!! <br/>";
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        // echo "getId RUN!!! <br/>";
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        // echo "getAuthKey RUN!!! <br/>";
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        // echo "validateAuthKey RUN!!! <br/>";
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        // echo "validatePassword RUN!!! <br/>";
//        var_dump($password);
//        var_dump($this->password_hash);
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        // echo "setPassword RUN!!! <br/>";
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        // echo "generateAuthKey RUN!!! <br/>";
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        // echo "generatePasswordResetToken RUN!!! <br/>";
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        // echo "removePasswordResetToken RUN!!! <br/>";
        $this->password_reset_token = null;
    }
}
