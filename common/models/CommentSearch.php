<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Comment;

/**
 * CommentSearch represents the model behind the search form about `common\models\Comment`.
 */
class CommentSearch extends Comment
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment_id','parent_comment_id', 'target_id', 'target_type', 'owner_uid', 'target_uid', 'like_count', 'status'], 'integer'],
            [['content','create_time', 'last_modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Comment::find()->orderBy(['comment_id' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'comment_id' => $this->comment_id,
            'parent_comment_id' => $this->parent_comment_id,
            'target_id' => $this->target_id,
            'target_type' => $this->target_type,
            'owner_uid' => $this->owner_uid,
            'target_uid' => $this->target_uid,
            'like_count' => $this->like_count,
            'content' => $this->content,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'content', $this->content]);
        return $dataProvider;
    }
}
