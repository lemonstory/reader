<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Chapter]].
 *
 * @see Chapter
 */
class ChapterQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Chapter[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Chapter|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
