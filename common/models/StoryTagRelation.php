<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_tag_relation".
 *
 * @property integer $story_id
 * @property integer $tag_id
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class StoryTagRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'story_tag_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['story_id', 'tag_id'], 'required'],
            [['story_id', 'tag_id', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['story_id', 'tag_id'], 'unique', 'targetAttribute' => ['story_id', 'tag_id'], 'message' => 'The combination of 故事id and 标签id has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'story_id' => Yii::t('app', '故事id'),
            'tag_id' => Yii::t('app', '标签id'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return StoryTagRelationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StoryTagRelationQuery(get_called_class());
    }
}
