<?php

namespace ashtokalo\currency\models;

/**
 * This is the ActiveQuery class for [[Currency]].
 *
 * @see Currency
 */
class CurrencyQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return Currency[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Currency|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * Ищет валюту по её цифровому или буквенному коду
     *
     * @param $alpha_code
     *
     * @return $this
     */
    public function byCode($alpha_code)
    {
        return is_numeric($alpha_code)
            ? $this->andWhere(['code' => $alpha_code])
            : $this->andWhere(['alpha_code' => strtoupper($alpha_code)]);
    }
}
