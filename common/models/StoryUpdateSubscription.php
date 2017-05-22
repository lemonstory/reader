<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "story_update_subscription".
 *
 * @property integer $uid
 * @property integer $story_id
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class StoryUpdateSubscription extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'story_update_subscription';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'story_id'], 'required'],
            [['uid', 'story_id', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['uid', 'story_id'], 'unique', 'targetAttribute' => ['uid', 'story_id'], 'message' => 'The combination of 用户uid and 故事id has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => Yii::t('app', '用户uid'),
            'story_id' => Yii::t('app', '故事id'),
            'create_time' => Yii::t('app', '订阅通知时间'),
            'last_modify_time' => Yii::t('app', '最后更新时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return StoryUpdateSubscriptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new StoryUpdateSubscriptionQuery(get_called_class());
    }
}
