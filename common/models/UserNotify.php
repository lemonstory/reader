<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_notify".
 *
 * @property string $id
 * @property integer $uid
 * @property string $category
 * @property string $topic_id
 * @property string $content
 * @property string $senders
 * @property integer $count
 * @property integer $is_read
 * @property string $create_time
 * @property string $last_modify_time
 */
class UserNotify extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'category', 'topic_id', 'senders', 'count'], 'required'],
            [['uid', 'topic_id', 'count', 'is_read'], 'integer'],
            [['content', 'senders'], 'string'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['category'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', '主键'),
            'uid' => Yii::t('app', '用户uid'),
            'category' => Yii::t('app', '消息分类'),
            'topic_id' => Yii::t('app', '消息主题id'),
            'content' => Yii::t('app', '消息的内容'),
            'senders' => Yii::t('app', '发送者的uid列表'),
            'count' => Yii::t('app', '通知聚合数量'),
            'is_read' => Yii::t('app', '消息是否已读'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return UserNotifyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserNotifyQuery(get_called_class());
    }
}
