<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_actor".
 *
 * @property integer $actor_id
 * @property integer $story_id
 * @property string $name
 * @property string $avator
 * @property integer $number
 * @property integer $is_visible
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 */
class StoryActor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'story_actor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['story_id', 'name', 'is_visible'], 'required'],
            [['story_id', 'number', 'is_visible', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['name'], 'string', 'max' => 16],
            [['avator'], 'string', 'max' => 2083],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'actor_id' => Yii::t('app', '角色id'),
            'story_id' => Yii::t('app', '故事id'),
            'name' => Yii::t('app', '姓名'),
            'avator' => Yii::t('app', '头像'),
            'number' => Yii::t('app', '序号'),
            'is_visible' => Yii::t('app', '是否可见'),
            'status' => Yii::t('app', '状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后更新时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return StoryActorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StoryActorQuery(get_called_class());
    }
}
