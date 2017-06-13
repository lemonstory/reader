<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $uid
 * @property string $name
 * @property string $cellphone
 * @property string $password
 * @property string $avatar
 * @property string $signature
 * @property integer $status
 * @property string $register_ip
 * @property string $register_time
 * @property string $last_login_ip
 * @property string $last_login_time
 * @property string $last_modify_time
 */
class User extends \yii\db\ActiveRecord
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
    public function rules()
    {
        return [
            [['name', 'register_ip', 'last_login_ip'], 'required'],
            [['status'], 'integer'],
            [['register_time', 'last_login_time', 'last_modify_time'], 'safe'],
            [['name'], 'string', 'max' => 16],
            [['cellphone'], 'string', 'max' => 11],
            [['password'], 'string', 'max' => 32],
            [['avatar'], 'string', 'max' => 2083],
            [['signature'], 'string', 'max' => 100],
            [['register_ip', 'last_login_ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => Yii::t('app', '用户uid'),
            'name' => Yii::t('app', '姓名'),
            'cellphone' => Yii::t('app', '手机号码'),
            'password' => Yii::t('app', '密码'),
            'avatar' => Yii::t('app', '头像'),
            'signature' => Yii::t('app', '个性签名'),
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
}
