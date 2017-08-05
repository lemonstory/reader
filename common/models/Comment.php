<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "comment".
 *
 * @property integer $comment_id
 * @property integer $parent_comment_id
 * @property string $target_id
 * @property integer $target_type
 * @property string $content
 * @property integer $owner_uid
 * @property integer $target_uid
 * @property integer $like_count
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $status
 */
class Comment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     * @see http://www.dodobook.net/php/2522
     */
    public function rules()
    {
        return [

            //字段必填
            [['owner_uid','target_id', 'target_type','content'],'required','message'=>'{attribute}不能为空!'],
            //去除首尾空白字符
            ['content', 'trim'],
            //owner_uid不能为"空"
            [['owner_uid'], function ($attribute, $param) {
                if (empty($this->owner_uid)) {
                    $this->addError($attribute, '发表评论uid不能为空!');
                }
            }, 'skipOnEmpty' => false],
            [['parent_comment_id', 'target_id', 'target_type', 'owner_uid', 'target_uid', 'like_count', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['content'], 'string', 'max' => 1024],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'comment_id' => Yii::t('app', '评论id'),
            'parent_comment_id' => Yii::t('app', '父评论id'),
            'target_id' => Yii::t('app', '评论目标id'),
            'target_type' => Yii::t('app', '评论目标类型'),
            'content' => Yii::t('app', '内容'),
            'owner_uid' => Yii::t('app', '发表评论uid'),
            'target_uid' => Yii::t('app', '评论目标uid'),
            'like_count' => Yii::t('app', '点赞数'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
            'status' => Yii::t('app', '状态'),
        ];
    }

    /**
     * @inheritdoc
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }

    // 获取评论的作者
    public function getUser()
    {
        //同样第一个参数指定关联的子表模型类名
        return $this->hasOne(User::className(), ['uid' => 'owner_uid']);
    }
}
