<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[ChapterMessageContent]].
 *
 * @see ChapterMessageContent
 */
class ChapterMessageContentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return ChapterMessageContent[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ChapterMessageContent|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
