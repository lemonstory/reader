<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Oauth]].
 *
 * @see Oauth
 */
class OauthQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Oauth[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Oauth|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
