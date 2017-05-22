<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "message".
 *
 * @property integer $message_id
 * @property integer $chapter_id
 * @property integer $story_id
 * @property string $from_actor_name
 * @property string $from_actor_avatar
 * @property string $to_actor_name
 * @property string $to_actor_avatar
 * @property string $content
 * @property integer $number
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class Message extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chapter_id', 'story_id', 'content', 'number'], 'required'],
            [['chapter_id', 'story_id', 'number', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['from_actor_name', 'to_actor_name', 'to_actor_avatar'], 'string', 'max' => 45],
            [['from_actor_avatar'], 'string', 'max' => 2833],
            [['content'], 'string', 'max' => 2048],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message_id' => Yii::t('app', '消息id'),
            'chapter_id' => Yii::t('app', '章节id'),
            'story_id' => Yii::t('app', '故事id'),
            'from_actor_name' => Yii::t('app', '发送者角色姓名'),
            'from_actor_avatar' => Yii::t('app', '发送者角色头像'),
            'to_actor_name' => Yii::t('app', '接收者角色姓名'),
            'to_actor_avatar' => Yii::t('app', '接收者角色头像'),
            'content' => Yii::t('app', '内容'),
            'number' => Yii::t('app', '序号'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return MessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }
}
