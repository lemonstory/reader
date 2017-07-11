<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StoryActor;

/**
 * StoryActorSearch represents the model behind the search form about `common\models\StoryActor`.
 * @property mixed avatar
 */
class StoryActorSearch extends StoryActor
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['actor_id', 'story_id', 'number', 'location', 'is_visible', 'status'], 'integer'],
            [['name', 'avatar', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = StoryActor::find();

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
            'actor_id' => $this->actor_id,
            'story_id' => $this->story_id,
            'number' => $this->number,
            'location' => $this->location,
            'is_visible' => $this->is_visible,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'avatar', $this->avatar]);

        return $dataProvider;
    }
}
