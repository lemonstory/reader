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
    const FILE_PARSE_TYPE_STORY = 'story';
    const FILE_PARSE_TYPE_CHAPTER = 'chapter';

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
                function ($query) {
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

    function arrValueEncoding(&$value, $key)
    {
        $value = mb_convert_encoding($value, "UTF-8", "Unicode,ASCII,GB2312,GBK,UTF-16");
    }

    /**
     * 将故事文件解析成数组
     * @param $file txt文件
     * @param $type txt文件类似(story:故事文件 chapter:章节文件)
     * @return array $story
     */
    public function parseFile($file,$type)
    {

        $story = array();
        if (file_exists($file) && is_readable($file)) {

            $fileArr = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            array_walk($fileArr, array($this, "arrValueEncoding"));
            //故事
            $story = array();
            //故事标题
            $story['name'] = "";
            //故事副标题
            $story['sub_name'] = "";
            //故事简介
            $story['description'] = "";
            //作者姓名
            $story['user_name'] = "";
            //角色信息
            //$actorArr = [['location' => 0,'name'=>'姓名','number'=>'序号'],...]
            $story['actorArr'] = array();
            //添加的章节信息
            //$addChapterArr = [['name'=>'章节名称','number'=>'序号']]
            $story['addChapterArr'] = array();
            $chapterItem = array();
            $chapterName = "";
            //故事章节数量
            $story['add_chapter_count'] = 0;
            //增加的故事消息数量
            $story['add_message_count'] = 0;
            //当前章节序号
            $currentChapterNumber = 0;
            //消息内容
            //$addMessageArr = ['章节序号'=>['actorName'=>'作者姓名','text'=>'消息文字','voice_over'=>'旁白'],...]
            $story['addMessageArr'] = array();
            $messageItem = array();
            //在解析过程中是否有错误
            $hasError = false;

            foreach ($fileArr as $index => $value) {

                //第一行
                //故事标题-故事短标题(躲灵—农夫与蛇的故事)
                if (0 == strcmp($type,Story::FILE_PARSE_TYPE_STORY) && 0 == $index) {
                    $value = str_replace("—","-",$value);
                    $titleArr = explode("-", $value);
                    if (!empty($titleArr) && is_array($titleArr) && count($titleArr) <= 2) {
                        $story['name'] = trim($titleArr[0]);
                        $story['sub_name'] = isset($titleArr[1]) ? trim($titleArr[1]) : "";
                    } else {
                        $hasError = true;
                        echo "故事标题解析错误：" . $value . "\r\n";
                    }
                }

                //第二行
                //故事简介(老伯去世前的种种离奇，有因有果，他是否可以平安躲过？)
                if (0 == strcmp($type,Story::FILE_PARSE_TYPE_STORY) && 1 == $index) {
                    $story['description'] = $value;
                }

                //第三行
                //作者姓名(凌晨小雨)
                if (0 == strcmp($type,Story::FILE_PARSE_TYPE_STORY) &&  2 == $index) {
                    $story['user_name'] = $value;
                }

                //第四行
                //角色信息(右=陈园，左=陈毅)
                if (0 == strcmp($type,Story::FILE_PARSE_TYPE_STORY) &&  3 == $index) {

                    $value = str_replace("，",",",$value);
                    $storyActorLocation = ArrayHelper::index(Yii::$app->params['storyActorLocation'], 'label');
                    $actorPairStrArr = explode(',',$value);
//                    var_dump($actorPairStrArr);
                    if (!empty($actorPairStrArr) && is_array($actorPairStrArr) && count($actorPairStrArr) > 0) {

                        foreach ($actorPairStrArr as $actorIndex => $actorPairStr) {

                            //-1表示不存在
                            $location = '-1';
                            $name = '';
                            $number = $actorIndex + 1;
                            $actorPairArr = preg_split("/[=＝]+/", $actorPairStr);
                            if(isset($actorPairArr[0]) && isset($actorPairArr[1])) {

                                $locationLabel = trim($actorPairArr[0]);
                                $name = trim($actorPairArr[1]);

                                if (ArrayHelper::keyExists($locationLabel, $storyActorLocation)) {
                                    $location = $storyActorLocation[$locationLabel]['value'];
                                } else {
                                    $hasError = true;
                                    print "故事角色位置方向解析错误：" . $locationLabel;
                                }
                                $actorItem = ['location' => $location, 'name' => $name, 'number' => $number];
                                $story['actorArr'][] = $actorItem;

                            }else {
                                $hasError = true;
                                print "故事角色信息解析错误：" . $value;
                            }
                        }
                    }
                }
                if ($index >= 4 || 0 == strcmp($type,Story::FILE_PARSE_TYPE_CHAPTER)) {

                    //第五行
                    //章节(#1这一天)
                    if (StringHelper::startsWith($value, "#", false)) {
                        $chapterNameArr = preg_split("/#\d/", $value, -1, PREG_SPLIT_NO_EMPTY);
                        if (!empty($chapterNameArr) && is_array($chapterNameArr)) {

                            if (count($chapterNameArr) > 1) {
                                $hasError = true;
                                echo "章节名称解析错误：" . $value . "\r\n";
                            } else {
                                $chapterName = $chapterNameArr[0];
                                $chapterItem['name'] = $chapterName;
                            }
                        } else {
                            $chapterItem['name'] = '';
                        }

                        $currentChapterNumber = str_replace("#", "", $value);
                        $currentChapterNumber = str_replace($chapterName, "", $currentChapterNumber);
                        $currentChapterNumber = intval($currentChapterNumber);
                        if (!empty($story['addChapterArr']) && is_array($story['addChapterArr'])) {

                            $chapterNumberArr = ArrayHelper::getColumn($story['addChapterArr'], 'number');
                            if (ArrayHelper::isIn($currentChapterNumber, $chapterNumberArr)) {
                                $hasError = true;
                                echo "章节序号重复：" . $value . "\r\n";
                            }
                        }
                        $chapterItem['number'] = $currentChapterNumber;
                        $story['addChapterArr'][] = $chapterItem;
                        $story['add_chapter_count'] = $story['add_chapter_count'] + 1;
                    } else {

                        //第六行(及后面的行)
                        //消息
//                        echo "行内容：【【【" . $value . "】】】\r\n";
                        if (!isset($messageItem['voiceOver'])) {
                            $messageItem['voiceOver'] = '';
                        }
                        if (!isset($messageItem['actorName'])) {
                            $messageItem['actorName'] = '';
                        }
                        if (!isset($messageItem['text'])) {
                            $messageItem['text'] = '';
                        }
                        //中文或英文字母或_
                        $pattern = '/^[\x{4e00}-\x{9fa5}_a-zA-Z0-9]+[：:]{1}/u';
                        $ret = preg_match($pattern,$value,$matches);
                        $actorIsExist = (1 == $ret) ? true : false;
                        if ($actorIsExist) {
                            $actorNameColon = str_replace("：",":",$matches[0],$count);
                            $actorName = str_replace(":","",$actorNameColon,$count);
                            $text = str_replace($matches[0], "", $value, $count);

                            if (empty($count)) {
                                $hasError = true;
                                echo "消息内容解析错误：" . $value . "\r\n";
                            } else {
                                $messageItem['actorName'] = $actorName;
                                $messageItem['text'] = $text;
                            }
//                            echo "【作者处理结束】打印消息结构\r\n";
//                            var_dump($messageItem);
                        } else {

//                            echo "【留白==RUN】\r\n";
//                            echo "【留白处理开始】打印消息结构\r\n";
//                            var_dump($messageItem);

                            //旁白
                            $messageItem['voiceOver'] = $value;
//                            echo "【留白处理结束】打印消息结构\r\n";
//                            var_dump($messageItem);
                        }

//                        echo "{开始}保存信息\r\n";
                        if (!empty($messageItem) && is_array($messageItem)) {
                            $story['addMessageArr'][$currentChapterNumber][] = $messageItem;
                            $story['add_message_count'] = $story['add_message_count'] + 1;
                            $messageItem['voiceOver'] = '';
                            $messageItem['actorName'] = '';
                            $messageItem['text'] = '';
                        }
//                        echo "{结束}保存信息\r\n";
                    }
                }
            }
        }

        if ($hasError) {
            echo "处理上面的错误后,故事才能正常保存\r\n";
            //将$story置为空
            $story = array();
        }
        return $story;
    }
}
