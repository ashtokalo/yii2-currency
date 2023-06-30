<?php

namespace ashtokalo\yii2\currency\origins;

use ashtokalo\yii2\currency\models\Currency;
use ashtokalo\yii2\currency\models\CurrencyPair;
use yii\caching\Cache;

class TcmbOrigin extends RateOrigin
{
    const DAILY_REATE_URL = 'https://www.tcmb.gov.tr/kurlar/today.xml';
    const CUSTOM_DATE_RATE_URL = 'https://www.tcmb.gov.tr/kurlar/{year}{month}/{day}{month}{year}.xml';

    /**
     * @var callable метод выбора ставки, по-умолчанию используется среднее по всем имеющимся
     * значениями - ForexBuying, ForexSelling, BanknoteBuying и BanknoteSelling:
     *
     *      function (array $currency): float {
     *          $rates = [];
     *          if (!empty($currency['ForexBuying'])) $rates[] = $currency['ForexBuying'];
     *          if (!empty($currency['ForexSelling'])) $rates[] = $currency['ForexSelling'];
     *          if (!empty($currency['BanknoteBuying'])) $rates[] = $currency['BanknoteBuying'];
     *          if (!empty($currency['BanknoteSelling'])) $rates[] = $currency['BanknoteSelling'];
     *          return round(array_sum($rates)/count($rates), 4) / ($currency['Unit'] ?: 1);
     *      }
     */
    public $rateSelector = null;

    /**
     * Возвращает курсы валют на заданную дату по данным ЦБ Турции
     * https://www.tcmb.gov.tr/wps/wcm/connect/EN/TCMB+EN/Main+Menu/Statistics/Exchange+Rates/Indicative+Exchange+Rates
     *
     * @param ?int $time время на которое нужно получить курсы валют, по-умолчанию текущее
     * @return array
     */
    public function getRates(?int $time = null): array
    {
        if (!$time) {
            $time = time();
        }
        $start = $time;
        $errors = 0;
        $rates = [];
        while (true) {
            if (date('Y-m-d', $time) == date('Y-m-d')) {
                $dayUrl = static::DAILY_REATE_URL;
            } else {
                $dayUrl = str_replace(['{year}', '{month}', '{day}'],
                    [date('Y', $time), date('m', $time), date('d', $time)],
                    static::CUSTOM_DATE_RATE_URL);
            }
            /** @var Cache $cache */
            $cache = \Yii::$app->get('cache');
            if (!$cache || empty($rates = $cache->get(__METHOD__ . $dayUrl))) {
                $xmlString = @file_get_contents($dayUrl);
                $xml = @json_decode(@json_encode(@simplexml_load_string($xmlString)), true);
                if (empty($xmlString) || empty($xml) || !is_array($xml['Currency'])) {
                    // check the day before, because weekend and holidays might return nothing
                    $time -= 86400;
                    if ($errors++ > 10) {
                        break;
                    }
                    continue;
                }
                foreach ($xml['Currency'] as $currency) {
                    $rates[$currency['@attributes']['CurrencyCode']] = $currency;
                }
                if ($cache && $rates) {
                    $cache->set(__METHOD__ . $dayUrl, $rates, 3600 * 12);
                }
                \Yii::info(sprintf('Получено %s ставок на дату %s от ЦБ Турции по запросу %s',
                    count($rates), date('Y-m-d', $start), $dayUrl), 'currency');
                break;
            } else {
                break;
            }
        }
        if (empty($rates) || $errors > 10) {
            \Yii::error(sprintf('Не удалось получить ставки от ЦБ Турции на дату %s',
                date('Y-m-d', $start)), 'currency');
        }
        return $rates ?: [];
    }

    protected function getRateValue(array $currency): float
    {
        if (is_callable($this->rateSelector)) {
            return call_user_func($this->rateSelector, $currency);
        }
        $rates = [];
        if (!empty($currency['ForexBuying'])) {
            $rates[] = $currency['ForexBuying'];
        }
        if (!empty($currency['ForexSelling'])) {
            $rates[] = $currency['ForexSelling'];
        }
        if (!empty($currency['BanknoteBuying'])) {
            $rates[] = $currency['BanknoteBuying'];
        }
        if (!empty($currency['BanknoteSelling'])) {
            $rates[] = $currency['BanknoteSelling'];
        }
        return round(array_sum($rates)/count($rates), 4)  / ($currency['Unit'] ?: 1);
    }

    public function getName(): string
    {
        return 'ЦБ Турции';
    }
}