<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "chapter".
 *
 * @property integer $chapter_id
 * @property integer $story_id
 * @property string $background
 * @property integer $message_count
 * @property integer $number
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
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
    public function rules()
    {
        return [
            [['story_id', 'background'], 'required'],
            [['story_id', 'message_count', 'number', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
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
            'story_id' => Yii::t('app', '故事id'),
            'background' => Yii::t('app', '背景图'),
            'message_count' => Yii::t('app', '消息数量'),
            'number' => Yii::t('app', '序号'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后更新时间'),
            'status' => Yii::t('app', '状态'),
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
