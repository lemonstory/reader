<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[UserOauth]].
 *
 * @see UserOauth
 */
class UserOauthQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return UserOauth[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserOauth|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
