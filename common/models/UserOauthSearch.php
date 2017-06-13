<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UserOauth;

/**
 * UserOauthSearch represents the model behind the search form about `common\models\UserOauth`.
 */
class UserOauthSearch extends UserOauth
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_oauth_id', 'uid', 'status'], 'integer'],
            [['oauth_name', 'oauth_id', 'oauth_access_token', 'oauth_expire', 'create_time', 'last_modify_time'], 'safe'],
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
        $query = UserOauth::find();

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
            'user_oauth_id' => $this->user_oauth_id,
            'uid' => $this->uid,
            'oauth_expire' => $this->oauth_expire,
            'create_time' => $this->create_time,
            'last_modify_time' => $this->last_modify_time,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'oauth_name', $this->oauth_name])
            ->andFilterWhere(['like', 'oauth_id', $this->oauth_id])
            ->andFilterWhere(['like', 'oauth_access_token', $this->oauth_access_token]);

        return $dataProvider;
    }
}
