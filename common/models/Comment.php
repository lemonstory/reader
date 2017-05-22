<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property integer $comment_id
 * @property integer $message_id
 * @property integer $chapter_id
 * @property integer $story_id
 * @property integer $uid
 * @property string $content
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'chapter_id', 'story_id', 'uid', 'content'], 'required'],
            [['message_id', 'chapter_id', 'story_id', 'uid', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['content'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => Yii::t('app', '评论id'),
            'message_id' => Yii::t('app', '消息id'),
            'chapter_id' => Yii::t('app', '章节id'),
            'story_id' => Yii::t('app', '故事id'),
            'uid' => Yii::t('app', '用户id'),
            'content' => Yii::t('app', '内容'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}
