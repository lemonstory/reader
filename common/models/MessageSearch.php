<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Message;

/**
 * MessageSearch represents the model behind the search form about `common\models\Message`.
 */
class MessageSearch extends Message
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'chapter_id', 'story_id', 'number', 'status'], 'integer'],
            [['from_actor_name', 'from_actor_avatar', 'to_actor_name', 'to_actor_avatar', 'content', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = Message::find();

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
            'message_id' => $this->message_id,
            'chapter_id' => $this->chapter_id,
            'story_id' => $this->story_id,
            'number' => $this->number,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'from_actor_name', $this->from_actor_name])
            ->andFilterWhere(['like', 'from_actor_avatar', $this->from_actor_avatar])
            ->andFilterWhere(['like', 'to_actor_name', $this->to_actor_name])
            ->andFilterWhere(['like', 'to_actor_avatar', $this->to_actor_avatar])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
