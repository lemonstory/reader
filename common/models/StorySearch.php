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
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['story_id', 'uid', 'chapter_count', 'message_count', 'taps', 'is_published', 'status'], 'integer'],
            [['name', 'sub_name', 'description', 'cover', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = Story::find();

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
            'story_id' => $this->story_id,
            'uid' => $this->uid,
            'chapter_count' => $this->chapter_count,
            'message_count' => $this->message_count,
            'taps' => $this->taps,
            'is_published' => $this->is_published,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'sub_name', $this->sub_name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'cover', $this->cover]);

        return $dataProvider;
    }
}
