<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * This is the model class for table "story".
 *
 * @property integer $story_id
 * @property string $name
 * @property string $sub_name
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
            [['uid'], 'required'],
            [['uid', 'chapter_count', 'message_count', 'taps', 'is_published', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['name'], 'string', 'max' => 150],
            [['sub_name'], 'string', 'max' => 300],
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
            'name' => Yii::t('app', '标题'),
            'sub_name' => Yii::t('app', '副标题'),
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
    public function getTags()
    {

        //hasMany relation tag表->tag_id =>  story_tag_relation表 => tag_id
        return $this->hasMany(Tag::className(), ['tag_id' => 'tag_id'])
            //viaTable relation story_tag_relation表->story_id => story表->story_id
            ->viaTable('story_tag_relation', ['story_id' => 'story_id'],
                function($query) {
                    $query->onCondition(
                        ['story_tag_relation.status' => Yii::$app->params['STATUS_ACTIVE']]);
                });
    }

    /**
     * 获取故事章节
     */
    public function getChapters()
    {

        //hasMany relation chapter表->story_id =>  story表 => story_id
        return $this->hasMany(Chapter::className(), ['story_id' => 'story_id']);
    }

    /**
     *
     */
    public function getUserReadStoryRecord()
    {

        //hasMany relation user_read_story_record表->story_id =>  story表 => story_id
        return $this->hasMany(UserReadStoryRecord::className(), ['story_id' => 'story_id']);
    }

    // 获取故事的作者
    public function getUser()
    {
        //同样第一个参数指定关联的子表模型类名
        return $this->hasOne(User::className(), ['uid' => 'uid']);
    }

    function arrValueEncoding(&$value,$key)
    {
        $value = mb_convert_encoding($value, "UTF-8", "Unicode,ASCII,GB2312,GBK");
    }

    /**
     * 将故事文件解析成数组
     * @param $file
     * @return array $story
     */
    public function parseFile($file) {

        $story = array();
        if(file_exists($file) && is_readable($file)) {

            $fileArr = file($file,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            array_walk($fileArr,array($this,"arrValueEncoding"));
            //故事
            $story = array();
            //故事标题
            $story['name'] = "";
            //故事副标题
            $story['subName'] = "";
            //故事简介
            $story['description'] = "";
            //作者姓名
            $story['userName'] = "";
            //角色信息
            //$actorArr = [['location' => 0,'name'=>'姓名','number'=>'序号'],...]
            $story['actorArr'] = array();
            //章节信息
            //$chapterArr = [['name'=>'章节名称','number'=>'序号']]
            $story['chapterArr'] = array();
            $chapterItem = array();
            $chapterName = "";
            //上一个章节序号
            $lastChapterNumber = 0;
            //当前章节序号
            $currentChapterNumber = 0;
            //消息内容
            //$messageArr = ['章节序号'=>['actorName'=>'作者姓名','text'=>'消息文字','voice_over'=>'旁白'],...]
            $story['messageArr'] = array();
            $messageItem = array();
            //在解析过程中是否有错误
            $hasError = false;
            $isNewChapter = false;

            foreach ($fileArr as $index => $value) {

                //第一行
                //故事标题-故事短标题(躲灵—农夫与蛇的故事)
                if(0 == $index) {
                    $titleArr = preg_split("/[—-]+/", $value);
                    if(!empty($titleArr) && is_array($titleArr) && 2 == count($titleArr)) {

                        $story['name'] = trim($titleArr[0]);
                        $story['subName'] = trim($titleArr[1]);
                    }else {
                        $hasError = true;
                        print "故事标题解析错误：" + $value;
                    }
                }

                //第二行
                //故事简介(老伯去世前的种种离奇，有因有果，他是否可以平安躲过？)
                if(1 == $index) {
                    $story['description'] = $value;
                }

                //第三行
                //作者姓名(凌晨小雨)
                if(2 == $index) {
                    $story['userName'] = $value;
                }

                //第四行
                //角色信息(右=陈园，左=陈毅)
                if(3 == $index) {
                    $storyActorLocation = ArrayHelper::index(Yii::$app->params['storyActorLocation'],'label');
                    $actorPairStrArr = preg_split("/[，,]+/", $value);
                    if(!empty($actorPairStrArr) && is_array($actorPairStrArr) && count($actorPairStrArr) > 0) {

                        foreach ($actorPairStrArr as $index => $actorPairStr) {

                            //-1表示不存在
                            $location = '-1';
                            $name = '';
                            $number = $index + 1;
                            $actorPairArr = preg_split("/[=]+/", $actorPairStr);
                            $locationLabel = trim($actorPairArr[0]);
                            $name= trim($actorPairArr[1]);

                            if(ArrayHelper::keyExists($locationLabel,$storyActorLocation)) {
                                $location = $storyActorLocation[$locationLabel]['value'];
                            }else {
                                print "故事角色位置方向解析错误：" + $locationLabel;
                            }
                            $actorItem = ['location' => $location,'name'=>$name,'number'=>$number];
                            $story['actorArr'][] = $actorItem;
                        }
                    }
                }
                if($index >= 4) {

                    //第五行
                    //章节(#1这一天)
                    if(StringHelper::startsWith($value,"#",false)) {
                        $isNewChapter = true;
                        $lastChapterNumber = $currentChapterNumber;
                        $chapterNameArr = preg_split("/#\d/", $value,-1,PREG_SPLIT_NO_EMPTY);
                        if(!empty($chapterNameArr) && is_array($chapterNameArr)) {

                            if(count($chapterNameArr) > 1) {
                                $hasError = true;
                                print "章节名称解析错误：" + $value;
                            }else {
                                $chapterName = $chapterNameArr[0];
                                $chapterItem['name'] = $chapterName;
                            }
                        }else {
                            $chapterItem['name'] = '';
                        }

                        $currentChapterNumber = str_replace("#","",$value);
                        $currentChapterNumber = str_replace($chapterName,"",$currentChapterNumber);
                        $currentChapterNumber = intval($currentChapterNumber);
                        if(!empty($story['chapterArr']) && is_array($story['chapterArr'])) {

                            $chapterNumberArr = ArrayHelper::getColumn($story['chapterArr'],'number');
                            $lastChapterNumber = end($chapterNumberArr);
                            if(ArrayHelper::isIn($currentChapterNumber,$chapterNumberArr)) {
                                $hasError = true;
                                print  "章节序号重复：" . $value;
                            }
                        }

                        $chapterItem['number'] = $currentChapterNumber;
                        $story['chapterArr'][] = $chapterItem;
                    }else {

                        //第六行(及后面的行)
                        //消息
                        if(!isset($messageItem['voiceOver'])) {
                            $messageItem['voiceOver'] = '';
                        }
                        if(!isset($messageItem['actorName'])) {
                            $messageItem['actorName'] = '';
                        }
                        if(!isset($messageItem['text'])) {
                            $messageItem['text'] = '';
                        }
                        $actorNameArr = array_keys(ArrayHelper::index($story['actorArr'],'name'));
                        $actorIsExist = false;
                        $actorName = '';
                        foreach ($actorNameArr as $actorNameItem) {
                            $actorIsExist = StringHelper::startsWith($value,$actorNameItem,false);
                            if($actorIsExist) {
                                $actorName = $actorNameItem;
                                break;
                            }
                        }
                        if($actorIsExist) {

                            //将上一条消息添加到消息数组中
                            if(!empty($messageItem) && is_array($messageItem) && ArrayHelper::keyExists('text',$messageItem) && !empty($messageItem['text'])) {

                                $key = $currentChapterNumber;
                                if($isNewChapter) {
                                    if(!empty($lastChapterNumber)) {
                                        $key = $lastChapterNumber;
                                    }
                                    $isNewChapter = false;
                                }
                                $story['messageArr'][$key][] = $messageItem;
                                $messageItem = array();
                            }

                            $text = str_replace($actorName."：","",$value,$count);
                            if(empty($count)) {
                                $text = str_replace($actorName.":","",$value,$count);
                            }

                            if(empty($count)) {
                                $hasError = true;
                                print "消息内容解析错误：" + $value;
                            }else{
                                $messageItem['actorName'] = $actorName;
                                //消息文字-支持换行
                                $messageItem['text'] .= $text;
                            }
                        }else if(!empty($messageItem['text'])){

                            //消息文字-支持换行
                            $messageItem['text'] .= $value;
                        }else {

                            //旁白-支持换行
                            $messageItem['voiceOver'] .= $value;
                        }
                    }

                    //将最后一条消息添加到消息数组中
                    if($index == count($fileArr) - 1) {
                        if(!empty($messageItem) && is_array($messageItem) && ArrayHelper::keyExists('text',$messageItem) && !empty($messageItem['text'])) {
                            $story['messageArr'][$currentChapterNumber][] = $messageItem;
                            $messageItem = array();
                        }
                    }
                }
            }
        }

        if(hasError) {
            echo "处理上面的错误后,故事才能正常保存";
        }
        return $story;
    }
}
