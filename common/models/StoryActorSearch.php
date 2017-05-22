<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\StoryActor;

/**
 * StoryActorSearch represents the model behind the search form about `common\models\StoryActor`.
 */
class StoryActorSearch extends StoryActor
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['story_actor_id', 'story_id', 'number', 'status'], 'integer'],
            [['name', 'avator', 'create_time', 'last_modify_time'], 'safe'],
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
            'story_actor_id' => $this->story_actor_id,
            'story_id' => $this->story_id,
            'number' => $this->number,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'avator', $this->avator]);

        return $dataProvider;
    }
}
