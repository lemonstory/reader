<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Like;

/**
 * LikeSearch represents the model behind the search form about `common\models\Like`.
 */
class LikeSearch extends Like
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['target_id', 'target_type', 'owner_uid', 'target_uid', 'status'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
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
        $query = Like::find()->orderBy(['create_time' => SORT_DESC]);

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
            'target_id' => $this->target_id,
            'target_type' => $this->target_type,
            'owner_uid' => $this->owner_uid,
            'target_uid' => $this->target_uid,
            'status' => $this->status,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
        ]);

        return $dataProvider;
    }
}
