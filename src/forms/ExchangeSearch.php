<?php

namespace ashtokalo\currency\forms;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ExchangeSearch represents the model behind the search form of `ashtokalo\currency\web\forms\Exchange`.
 */
class ExchangeSearch extends Exchange
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'base_currency_id', 'quoted_currency_id', 'created_by', 'locked_by'], 'integer'],
            [['rate'], 'number'],
            [['created_at', 'locked_at'], 'safe'],
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
    public function search($params, $query = null)
    {
        if (!$query) $query = Exchange::find()->andWhere('next_currency_pair_id is null');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'base_currency_id' => $this->base_currency_id,
            'quoted_currency_id' => $this->quoted_currency_id,
            'rate' => $this->rate,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'locked_at' => $this->locked_at,
            'locked_by' => $this->locked_by,
        ]);

        return $dataProvider;
    }
}
