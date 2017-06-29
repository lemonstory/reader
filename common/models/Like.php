<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "like".
 *
 * @property integer $object_id
 * @property integer $object_type
 * @property integer $uid
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 */
class Like extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'like';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_type', 'uid'], 'required'],
            [['object_id', 'object_type', 'uid', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'object_id' => Yii::t('app', '被点赞对象id'),
            'object_type' => Yii::t('app', '被点赞对象类型'),
            'uid' => Yii::t('app', '用户id'),
            'status' => Yii::t('app', '状态'),
            'create_time' => Yii::t('app', '创建时间'),
            'last_modify_time' => Yii::t('app', '最后修改时间'),
        ];
    }

    /**
     * @inheritdoc
     * @return LikeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LikeQuery(get_called_class());
    }
}
