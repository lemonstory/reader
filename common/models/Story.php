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
 * @property string $views
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
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
            [['uid', 'chapter_count', 'message_count', 'views', 'status'], 'integer'],
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
            'uid' => Yii::t('app', '作者id'),
            'chapter_count' => Yii::t('app', '章节数量'),
            'message_count' => Yii::t('app', '消息数量'),
            'views' => Yii::t('app', '阅读数'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', '状态'),
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
     * 创建故事
     * @param $data
     * @return bool
     */
    public function create($data) {

        $this->load($data,'');
        return $this->save();
    }

    /**
     * 更新故事
     * @param $data
     * @return bool
     */
    public function updateStory($id,$data) {

        $story = Story::findOne($id);
        $story->load($data, '');
        if ($this->update() !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新故事封面
     * @param $storyId
     * @param $cover
     * @return bool
     */
    public function updateCover($storyId,$cover) {

        $data['story_id'] = $storyId;
        $data['cover'] = $cover;
        $data['last_modify_time'] = time();
        return $this->updateStory($storyId,$data);
    }

}
