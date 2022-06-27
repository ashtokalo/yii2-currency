<?php

namespace ashtokalo\yii2\currency\models;
use yii\base\InvalidArgumentException;

/**
 * This is the ActiveQuery class for [[CurrencyPair]].
 *
 * @see CurrencyPair
 */
class CurrencyPairQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return CurrencyPair[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return CurrencyPair|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function latest()
    {
        return $this->andWhere('next_currency_pair_id is null');
    }

    public function base(Currency $currency)
    {
        return $this->andWhere(['base_currency_id' => $currency->id]);
    }

    public function quoted(Currency $currency)
    {
        return $this->andWhere(['quoted_currency_id' => $currency->id]);
    }

    public function pair(Currency $baseCurrency, Currency $quotedCurrency)
    {
        return $this->base($baseCurrency)->quoted($quotedCurrency);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
    }
}
