<?php

namespace ashtokalo\yii2\currency\helpers;

use ashtokalo\yii2\currency\models\Currency;
use ashtokalo\yii2\currency\models\CurrencyPair;
use yii\caching\Cache;

class CbrfHelper
{
    const CBR_DAILY_REATE_URL = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';

    /**
     * Возвращает курсы валют по данным ЦБ РФ http://cbr.ru на заданную дату.
     *
     * @param ?int $time время на которое нужно получить курсы валют, по-умолчанию текущее
     * @return null|string
     */
    public static function getRates(int $time = null)
    {
        $dayUrl = static::CBR_DAILY_REATE_URL . date('d/m/Y', $time ?: time());
        /** @var Cache $cache */
        $cache = \Yii::$app->get('cache');
        if (!$cache || empty($rates = $cache->get(__METHOD__ . $dayUrl))) {
            $xmlString = @file_get_contents($dayUrl);
            $xml = @json_decode(@json_encode(@simplexml_load_string($xmlString)), true);
            $rates = @array_column($xml['Valute'], null, 'CharCode') ?: false;
            if ($cache) $cache->set(__METHOD__ . $dayUrl, $rates, 3600 * 12);
        }

        return $rates;
    }

    /**
     * @param $rates
     * @return CurrencyPair[]
     */
    public static function updateRates($rates): array
    {
        /** @var Currency[] $currencies */
        $currencies = array_column(Currency::find()->all(), null, 'alpha_code');
        $updates = [];
        foreach ($currencies as $quotedCurrency) {
            foreach ($rates as $alphaCode => $rate) {
                if (isset($currencies[$alphaCode]) && $alphaCode != $quotedCurrency->alpha_code) {
                    $baseCurrency = $currencies[$alphaCode];
                    $pair = CurrencyPair::find()->pair($baseCurrency, $quotedCurrency)->latest()->one();
                    if (!$pair) continue;
                    $newRate = str_replace(',', '.', $rate['Value']) / ($rate['Nominal'] ?: 1);
                    $updates[] = $newPair = new CurrencyPair([
                        'base_currency_id' => $baseCurrency->id,
                        'quoted_currency_id' => $quotedCurrency->id,
                        'rate' => $newRate,
                    ]);
                    if ($pair->locked_at) {
                        $newPair->addError('locked_at', 'Валютная пара заблокирована от автоматических изменений.');
                        continue;
                    };
                    if ($pair->rate == $newRate) {
                        $newPair->addError('rate', 'Ничего не изменилось.');
                        continue;
                    };
                    $newPair->save();
                }
            }
        }
        return $updates;
    }
}