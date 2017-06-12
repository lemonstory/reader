<?php

namespace common\models;

use Yii;
use yii\db\ActiveQuery;

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
            'is_published' => Yii::t('app', '是否发布'),
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
     * Relation method
     * @see http://www.yiiframework.com/doc-2.0/guide-db-active-record.html#relational-data
     * @return ActiveQuery
     */
    public function getTags() {

        return $this->hasMany(Tag::className(), ['tag_id' => 'story_id'])
            ->viaTable('story_tag_relation', ['tag_id' => 'story_id'])
            ->where(['status' => Yii::$app->params['STATUS_ACTIVE']]);
    }

    public function storys() {
        return $this->find()
            ->select('*');
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

    public function getStory($storyId) {

        $story = Story::findOne($storyId);
        $data = $story->getAttributes();
        $data['tags'] = $this->getStoryTags($storyId);
        return $data;
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


    public function setPublished($storyId) {

        $data['story_id'] = $storyId;
        $data['is_published'] = Yii::$app->params['STATUS_PUBLISHED'];
        $data['last_modify_time'] = time();
        return $this->updateStory($storyId,$data);

    }

    public function setUnPublished($storyId) {

        $data['story_id'] = $storyId;
        $data['is_published'] = Yii::$app->params['STATUS_UNPUBLISHED'];
        $data['last_modify_time'] = time();
        return $this->updateStory($storyId,$data);
    }


    /**
     * 获取故事标签
     * @param $storyId
     * @return array
     */
    public function getStoryTags($storyId) {


        #TODO:没有对返回结果做排序
        $story = Story::findOne($storyId);
        $data = $story->getTags()
//            ->orderBy('id')
            ->asArray()
            ->all();
        $tags = array();
        if(!empty($data) && count($data) > 0) {

            foreach ($data as $item) {

                $tag['tag_id'] = $item['tag_id'];
                $tag['name'] = $item['name'];
                $tags[] = $tag;
            }
        }
        return $tags;
    }


    /**
     * 添加故事标签
     * @param $storyId
     * @param $tagIds
     * @return int
     */
    public function addTags($storyId,$tagIds) {

        $createTime = time();
        $lastModifyTime = null;
        $status = Yii::$app->params['STATUS_ACTIVE'];
        $rowsAffected = 0;
        if(count($tagIds) > 0) {

            $columns = array('story_id','tag_id','create_time','last_modify_time','status');
            $rows = array();
            foreach ($tagIds as $tagId) {
                $rows[] = array($storyId,$tagId,$createTime,$lastModifyTime,$status);
            }
            $rowsAffected = Yii::$app->db->createCommand()->batchInsert('story_tag_relation',$columns, $rows)->execute();
        }
        return $rowsAffected;
    }


    /**
     * 删除故事标签
     * @param $storyId
     * @param $tagIds
     * @return bool
     */
    public function delTags($storyId,$tagIds) {

        return true;
    }

}
