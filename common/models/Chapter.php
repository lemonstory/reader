<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "chapter".
 *
 * @property integer $chapter_id
 * @property string $name
 * @property integer $story_id
 * @property string $background
 * @property integer $message_count
 * @property integer $number
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 * @property integer $is_published
 */
class Chapter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'chapter';
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
            [['story_id'], 'required'],
            [['story_id', 'message_count', 'number', 'status', 'is_published'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['name'], 'string', 'max' => 150],
            [['background'], 'string', 'max' => 2083],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chapter_id' => Yii::t('app', '章节id'),
            'name' => Yii::t('app', '章节名称'),
            'story_id' => Yii::t('app', '故事id'),
            'background' => Yii::t('app', '背景图'),
            'message_count' => Yii::t('app', '消息数量'),
            'number' => Yii::t('app', '序号'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后更新时间'),
            'status' => Yii::t('app', '状态'),
            'is_published' => Yii::t('app', '是否发布'),
        ];
    }

    /**
     * @inheritdoc
     * @return ChapterQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ChapterQuery(get_called_class());
    }
}
