<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[StoryTagRelation]].
 *
 * @see StoryTagRelation
 */
class StoryTagRelationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return StoryTagRelation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return StoryTagRelation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
