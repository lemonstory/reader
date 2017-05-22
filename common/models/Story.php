<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story".
 *
 * @property integer $story_id
 * @property string $cover
 * @property string $name
 * @property string $description
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
            [['cover', 'name', 'uid'], 'required'],
            [['uid', 'chapter_count', 'message_count', 'views', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['cover'], 'string', 'max' => 2083],
            [['name'], 'string', 'max' => 150],
            [['description'], 'string', 'max' => 750],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'story_id' => Yii::t('app', '故事id'),
            'cover' => Yii::t('app', '封面图'),
            'name' => Yii::t('app', '名称'),
            'description' => Yii::t('app', '介绍'),
            'uid' => Yii::t('app', '作者id'),
            'chapter_count' => Yii::t('app', '章节数量'),
            'message_count' => Yii::t('app', '消息数量'),
            'views' => Yii::t('app', '阅读数'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }
}
