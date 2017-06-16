<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story".
 *
 * @property integer $story_id
 * @property string $name
 * @property string $description
 * @property string $cover
 * @property integer $uid
 * @property integer $chapter_count
 * @property integer $message_count
 * @property string $taps
 * @property integer $is_published
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 */
class Story extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'story';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'uid'], 'required'],
            [['uid', 'chapter_count', 'message_count', 'taps', 'is_published', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['name'], 'string', 'max' => 150],
            [['description'], 'string', 'max' => 750],
            [['cover'], 'string', 'max' => 2083],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'story_id' => Yii::t('app', '故事id'),
            'name' => Yii::t('app', '名称'),
            'description' => Yii::t('app', '介绍'),
            'cover' => Yii::t('app', '封面图'),
            'uid' => Yii::t('app', '作者uid'),
            'chapter_count' => Yii::t('app', '章节数量'),
            'message_count' => Yii::t('app', '消息数量'),
            'taps' => Yii::t('app', '点击数'),
            'is_published' => Yii::t('app', '是否发布'),
            'status' => Yii::t('app', '状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return StoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StoryQuery(get_called_class());
    }

    /**
     * 获取故事角色
     * @return \yii\db\ActiveQuery
     */
    public function getActors()
    {
        //hasMany relation story_actor表->story_id =>  story表 => story_id
        return $this->hasMany(StoryActor::className(), ['story_id' => 'story_id']);
    }

    /**
     * 获取故事标签
     * @return $this
     */
    public function getTags() {

        //hasMany relation tag表->tag_id =>  story_tag_relation表 => tag_id
        return $this->hasMany(Tag::className(), ['tag_id' => 'tag_id'])
            //viaTable relation story_tag_relation表->story_id => story表->story_id
            ->viaTable('story_tag_relation', ['story_id' => 'story_id']);
    }

    /**
     * 获取故事章节
     */
    public function getChapters() {

        //hasMany relation chapter表->story_id =>  story表 => story_id
        return $this->hasMany(Chapter::className(), ['story_id' => 'story_id']);
    }

    /**
     *
     */
    public function getUserReadStoryRecord() {

        //hasMany relation user_read_story_record表->story_id =>  story表 => story_id
        return $this->hasMany(UserReadStoryRecord::className(), ['story_id' => 'story_id']);
    }
}
