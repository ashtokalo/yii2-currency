<?php

namespace ashtokalo\yii2\currency\origins;

use ashtokalo\yii2\currency\models\Currency;
use ashtokalo\yii2\currency\models\CurrencyPair;

abstract class RateOrigin
{
    /**
     * Список валютных пар, по которым необходимо загружать котировки.
     *
     * Каждая запись - массив, нулевое элемент - валюта цитируемая, первый - валюта базовая.
     * Валюта указывается в виде ISO кода, например USD, RUR, EUR.
     *
     * @var array
     */
    public array $currencies = [];

    /**
     * Возвращает список ставок на заданный момент времени.
     *
     * @param integer $time момент времени, если не указан - текущее время
     */
    abstract public function getRates(?int $time = null): array;

    /**
     * Обновляет ставки полученными данными.
     *
     * @param array $rates
     * @return CurrencyPair[]
     */
    public function updateRates(array $rates): array
    {
        /** @var Currency[] $currencies */
        $currencies = array_column(Currency::find()->all(), null, 'alpha_code');
        $updates = [];
        foreach ($currencies as $quotedCurrency) {
            foreach ($rates as $alphaCode => $rate) {
                if (isset($currencies[$alphaCode]) && $alphaCode != $quotedCurrency->alpha_code) {
                    $baseCurrency = $currencies[$alphaCode];
                    $matched = false;
                    foreach ($this->currencies as $pair) {
                        if ($pair[0] == $baseCurrency->alpha_code && $pair[1] == $quotedCurrency->alpha_code) {
                            $matched = true;
                            break;
                        }
                    }
                    if (!$matched) continue;
                    $pair = CurrencyPair::find()->pair($baseCurrency, $quotedCurrency)->latest()->one();
                    $newRate = $this->getRateValue($rate);
                    $updates[] = $newPair = new CurrencyPair([
                        'base_currency_id' => $baseCurrency->id,
                        'quoted_currency_id' => $quotedCurrency->id,
                        'rate' => $newRate,
                        'origin' => $this->getName(),
                    ]);
                    if ($pair) {
                        if ($pair->locked_at) {
                            $newPair->addError('locked_at', 'Валютная пара заблокирована от автоматических изменений.');
                            continue;
                        };
                        if ($pair->rate == $newRate) {
                            $newPair->addError('rate', 'Ничего не изменилось.');
                            continue;
                        };
                    }
                    $newPair->save();
                }
            }
        }
        return $updates;
    }

    /**
     * Возвращает название источника ставок.
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Возвращает значение ставки для ставки полученной в getRates().
     *
     * @param array $currency
     * @return float
     */
    abstract protected function getRateValue(array $currency): float;
}