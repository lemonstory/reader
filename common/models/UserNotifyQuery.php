<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[UserNotify]].
 *
 * @see UserNotify
 */
class UserNotifyQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return UserNotify[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserNotify|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
