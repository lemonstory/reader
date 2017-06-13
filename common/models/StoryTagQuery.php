<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[StoryTag]].
 *
 * @see StoryTag
 */
class StoryTagQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return StoryTag[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoryTag|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
