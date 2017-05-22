<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[StoryUpdateSubscription]].
 *
 * @see StoryUpdateSubscription
 */
class StoryUpdateSubscriptionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return StoryUpdateSubscription[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoryUpdateSubscription|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
