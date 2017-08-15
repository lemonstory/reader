<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "auth".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $source
 * @property string $source_id
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $user
 * @property integer $status
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'source', 'source_id'], 'required'],
            [['uid', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['source'], 'string', 'max' => 32],
            [['source_id'], 'string', 'max' => 140],
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
            'create_time' => Yii::t('app', 'create time'),
            'last_modify_time' => Yii::t('app', 'last modify time'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @inheritdoc
     * @return AuthQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuthQuery(get_called_class());
    }

    // 获取User
    public function getUser()
    {
        //同样第一个参数指定关联的子表模型类名
        return $this->hasOne(User::className(), ['uid' => 'uid']);
    }
}
