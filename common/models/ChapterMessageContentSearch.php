<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ChapterMessageContent;

/**
 * ChapterMessageContentSearch represents the model behind the search form about `common\models\ChapterMessageContent`.
 */
class ChapterMessageContentSearch extends ChapterMessageContent
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'story_id', 'chapter_id', 'number', 'actor_id', 'is_loading', 'status'], 'integer'],
            [['voice_over', 'text', 'img', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = ChapterMessageContent::find()->orderBy(['story_id' => SORT_DESC,'chapter_id' => SORT_ASC,'number' => SORT_ASC]);

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
            'story_id' => $this->story_id,
            'chapter_id' => $this->chapter_id,
            'number' => $this->number,
            'actor_id' => $this->actor_id,
            'is_loading' => $this->is_loading,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'voice_over', $this->voice_over])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'img', $this->img]);

        return $dataProvider;
    }
}
