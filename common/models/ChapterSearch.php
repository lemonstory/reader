<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Chapter;

/**
 * ChapterSearch represents the model behind the search form about `common\models\Chapter`.
 */
class ChapterSearch extends Chapter
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chapter_id', 'story_id', 'message_count', 'number', 'status', 'is_published'], 'integer'],
            [['name', 'background', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = Chapter::find()->orderBy(['story_id' => SORT_DESC,'number' => SORT_ASC]);

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
            'chapter_id' => $this->chapter_id,
            'story_id' => $this->story_id,
            'message_count' => $this->message_count,
            'number' => $this->number,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
            'status' => $this->status,
            'is_published' => $this->is_published,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'background', $this->background]);

        return $dataProvider;
    }
}
