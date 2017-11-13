<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Story;

/**
 * StorySearch represents the model behind the search form about `common\models\Story`.
 */
class StorySearch extends Story
{

    public $tag_name;
    public $tag_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['story_id', 'tag_id', 'uid', 'chapter_count', 'message_count', 'comment_count', 'taps', 'is_published','is_serialized', 'is_pay', 'status'], 'integer'],
            [['name', 'sub_name', 'description', 'cover', 'tag_name', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = Story::find()->innerJoinWith('tags', true);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = [
            'story_id' => SORT_DESC,];

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            Story::tableName().'.story_id' => $this->story_id,
            Tag::tableName() .'.tag_id' => $this->tag_id,
            Story::tableName().'.uid' => $this->uid,
            Story::tableName().'.chapter_count' => $this->chapter_count,
            Story::tableName().'.comment_count' => $this->comment_count,
            Story::tableName().'.taps' => $this->taps,
            Story::tableName().'.is_published' => $this->is_published,
            Story::tableName().'.status' => $this->status,
            Story::tableName().'.create_time' => $this->create_time,
            Story::tableName().'.last_modify_time' => $this->last_modify_time,
        ]);

        $query->andFilterWhere(['>', 'message_count', $this->message_count]);

        $query->andFilterWhere(['like', Story::tableName().'.name', $this->name])
            ->andFilterWhere(['like', Story::tableName().'.sub_name', $this->sub_name])
            ->andFilterWhere(['like', Story::tableName().'.description', $this->description])
            ->andFilterWhere(['like', Story::tableName().'.cover', $this->cover])
            ->andFilterWhere(['like', Tag::tableName() .'.name', $this->tag_name]);

        return $dataProvider;
    }
}
