<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_read_story_record".
 *
 * @property integer $uid
 * @property integer $story_id
 * @property integer $last_chapter_id
 * @property string $last_message_id
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class UserReadStoryRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_read_story_record';
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
            [['uid', 'story_id', 'last_chapter_id', 'last_message_id'], 'required'],
            [['uid', 'story_id', 'last_chapter_id', 'last_message_id', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => Yii::t('app', '用户id'),
            'story_id' => Yii::t('app', '故事id'),
            'last_chapter_id' => Yii::t('app', '最后阅读章节id'),
            'last_message_id' => Yii::t('app', '最后阅读信息id'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return UserReadStoryRecordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserReadStoryRecordQuery(get_called_class());
    }

    public function getStory() {

        //hasOne relation story->story_id =>  user_read_story_record表 => story_id
        return $this->hasOne(Story::className(), ['story_id' => 'story_id']);
    }
}
