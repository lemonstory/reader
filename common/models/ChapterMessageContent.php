<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "chapter_message_content".
 *
 * @property string $message_id
 * @property integer $story_id
 * @property integer $chapter_id
 * @property integer $number
 * @property string $voice_over
 * @property integer $actor_id
 * @property string $text
 * @property string $img
 * @property integer $is_loading
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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => 'last_modify_time',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['story_id', 'chapter_id', 'number'], 'required'],
            [['story_id', 'chapter_id', 'number', 'actor_id', 'is_loading', 'status'], 'integer'],
            [['voice_over', 'text'], 'string'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['img'], 'string', 'max' => 2083],
            [['story_id', 'chapter_id', 'number'], 'unique', 'targetAttribute' => ['story_id', 'chapter_id', 'number'], 'message' => '唯一性索引story_id,chapter_id,number已存在'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'message_id' => Yii::t('app', '消息id'),
            'story_id' => Yii::t('app', '故事id'),
            'chapter_id' => Yii::t('app', '章节id'),
            'number' => Yii::t('app', '消息序号'),
            'voice_over' => Yii::t('app', '旁白'),
            'actor_id' => Yii::t('app', '角色id'),
            'text' => Yii::t('app', '消息文字'),
            'img' => Yii::t('app', '配图'),
            'is_loading' => Yii::t('app', '是否有加载条'),
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
