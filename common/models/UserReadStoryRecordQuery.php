<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[UserReadStoryRecord]].
 *
 * @see UserReadStoryRecord
 */
class UserReadStoryRecordQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return UserReadStoryRecord[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserReadStoryRecord|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
