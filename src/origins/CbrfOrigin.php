<?php

namespace ashtokalo\yii2\currency\origins;

use ashtokalo\yii2\currency\models\Currency;
use ashtokalo\yii2\currency\models\CurrencyPair;
use yii\caching\Cache;

class CbrfOrigin extends RateOrigin
{
    const CBR_DAILY_REATE_URL = 'https://www.cbr.ru/scripts/XML_daily.asp?date_req=';

    public array $currencies = [
        ['USD', 'RUR'],
        ['EUR', 'RUR'],
    ];

    /**
     * Возвращает курсы валют по данным ЦБ РФ http://cbr.ru на заданную дату.
     *
     * @param ?int $time время на которое нужно получить курсы валют, по-умолчанию текущее
     * @return array
     */
    public function getRates(?int $time = null): array
    {
        $dayUrl = static::CBR_DAILY_REATE_URL . date('d/m/Y', $time ?: time());
        /** @var Cache $cache */
        $cache = \Yii::$app->get('cache');
        $cache = false;
        if (!$cache || empty($rates = $cache->get(__METHOD__ . $dayUrl))) {
            $xmlString = @file_get_contents($dayUrl);
            $xml = @json_decode(@json_encode(@simplexml_load_string($xmlString)), true);
            if (empty($xmlString) || empty($xml) || !is_array($xml['Valute'])) {
                throw new \Exception(sprintf('Can not retrieve currency data from %s', $dayUrl));
            }
            $rates = @array_column($xml['Valute'], null, 'CharCode') ?: false;
            if ($cache) $cache->set(__METHOD__ . $dayUrl, $rates, 3600 * 12);
            \Yii::info(sprintf('Получено %s ставок на дату %s от ЦБ РФ по запросу %s',
                count($rates), date('Y-m-d', $time ?: time()), $dayUrl), 'currency');
        }
        if (empty($rates)) {
            \Yii::error(sprintf('Не удалось получить ставки от ЦБ РФ на дату %s',
                date('Y-m-d', $time ?: time())), 'currency');
        }
        return $rates ?: [];
    }

    public function getName(): string
    {
        return 'Банк России';
    }

    protected function getRateValue(array $currency): float
    {
        return str_replace(',', '.', $currency['Value']) / ($currency['Nominal'] ?: 1);
    }
}
