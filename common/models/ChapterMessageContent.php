<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chapter_message_content".
 *
 * @property integer $chapter_id
 * @property integer $story_id
 * @property string $message_content
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class ChapterMessageContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chapter_message_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['chapter_id', 'story_id', 'message_content'], 'required'],
            [['chapter_id', 'story_id', 'status'], 'integer'],
            [['message_content'], 'string'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['chapter_id', 'story_id'], 'unique', 'targetAttribute' => ['chapter_id', 'story_id'], 'message' => 'The combination of 章节id and 故事id has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chapter_id' => Yii::t('app', '章节id'),
            'story_id' => Yii::t('app', '故事id'),
            'message_content' => Yii::t('app', '内容'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return ChapterMessageContentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ChapterMessageContentQuery(get_called_class());
    }
}
