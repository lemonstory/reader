<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "oauth".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $source
 * @property string $source_id
 * @property string $union_id
 * @property string $access_token
 * @property integer $create_time
 * @property integer $last_modify_time
 * @property integer $status
 */
class Oauth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'oauth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'source', 'source_id', 'access_token'], 'required'],
            [['uid', 'create_time', 'last_modify_time', 'status'], 'integer'],
            [['source'], 'string', 'max' => 32],
            [['source_id'], 'string', 'max' => 140],
            [['union_id', 'access_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'user oauth id'),
            'uid' => Yii::t('app', 'uid'),
            'source' => Yii::t('app', '验证提供商的名称'),
            'source_id' => Yii::t('app', '外部服务在该用户成功登录后提供的唯一 ID'),
            'union_id' => Yii::t('app', '微信用户统一标识。针对一个微信开放平台帐号下的应用，同一用户的unionid是唯一的。'),
            'access_token' => Yii::t('app', '调用接口授权凭证'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @inheritdoc
     * @return OauthQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OauthQuery(get_called_class());
    }

    // 获取User
    public function getUser()
    {
        //同样第一个参数指定关联的子表模型类名
        return $this->hasOne(User::className(), ['uid' => 'uid']);
    }
}
