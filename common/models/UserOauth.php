<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_oauth".
 *
 * @property integer $user_oauth_id
 * @property integer $uid
 * @property string $oauth_name
 * @property string $oauth_id
 * @property string $oauth_access_token
 * @property string $oauth_expire
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class UserOauth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_oauth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'oauth_name', 'oauth_id'], 'required'],
            [['uid', 'status'], 'integer'],
            [['oauth_expire', 'create_time', 'last_modify_time'], 'safe'],
            [['oauth_name'], 'string', 'max' => 32],
            [['oauth_id'], 'string', 'max' => 140],
            [['oauth_access_token'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_oauth_id' => Yii::t('app', 'user oauth id'),
            'uid' => Yii::t('app', 'uid'),
            'oauth_name' => Yii::t('app', 'oauth name'),
            'oauth_id' => Yii::t('app', 'oauth id'),
            'oauth_access_token' => Yii::t('app', 'oauth access token'),
            'oauth_expire' => Yii::t('app', 'oauth expire'),
            'create_time' => Yii::t('app', 'create time'),
            'last_modify_time' => Yii::t('app', 'last modify time'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @inheritdoc
     * @return UserOauthQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserOauthQuery(get_called_class());
    }
}
