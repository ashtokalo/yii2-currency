<?php

namespace ashtokalo\yii2\currency\console\controllers;

use ashtokalo\yii2\currency\models\Currency;
use ashtokalo\yii2\currency\models\CurrencyPair;
use ashtokalo\yii2\currency\Module;
use yii\base\InvalidValueException;
use yii\console\Controller;

/**
 * Интерфейс для обновления и контроля курса валют.
 *
 * @package app\console\controllers
 */
class CurrencyController extends Controller
{
    protected $cbrDailyRates = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';

    /**
     * Удаляет все котировки из базы данных.
     */
    public function actionClear()
    {
        $this->printLine('%s котировок было удалено из базы данных', CurrencyPair::deleteAll());
    }

    /**
     * @var string дата на которую нужно загрузить курсы валют, в формате ММ/ДД/ГГГГ или ДД.ММ.ГГГГ
     */
    public $date;

    /**
     * Обновляет курсы валют по настроенным источникам данных.
     *
     * Обновляются только те курсы для которых уже есть валютные пары в таблице `currency_pair`,
     * при условии что последняя запись не заблокирована пользователем. Информация о выполненных
     * изменениях выводится в процессе обновления, если не выключен интерактивный режим.
     *
     * Команда загружает курс на текущую дату, если не указана иная дата с параметром `--date`.
     * Данную команду лучше всего выполнять по расписанию каждые 3-6 часов начиная с полуночи.
     */
    public function actionUpdate()
    {
        if ($this->module instanceof Module) {
            $module = $this->module;
        } else {
            $module = \Yii::$app->getModule('currency') ?? null;
            if ($module) {
                $this->printLine('Используем модуль currency из настроек приложения');
            } else {
                $this->printLine('Модуль currency не определён');
            }
        }
        if ($module && method_exists($module, 'getRateOrigins')) {
            $origins = call_user_func([$module, 'getRateOrigins']);
        } else {
            $origins = [];
            $this->printLine('Не найдены источники курсов валют');
        }
        foreach ($origins as $origin) {
            $this->printLine(sprintf('Обновляем курсы валют от %s', $origin->getName()));
            if (empty($rates = $origin->getRates($time = $this->date ? strtotime($this->date) : time()))) {
                $this->printLine('Невозможно получить курсы валют на текущий %s', date('Y-m-d', $time));
                return null;
            }
            $updates = $origin->updateRates($rates);
            foreach ($updates as $pair) {
                if ($pair->hasErrors('locked_at')) {
                    $this->printLine('курс %s-%s заблокирован на значении %s с %s.',
                        $pair->baseCurrency->alpha_code, $pair->quotedCurrency->alpha_code, $pair->rate,
                        date('d.m.Y', strtotime($pair->locked_at)));
                    continue;
                }
                if ($pair->hasErrors('rate')) {
                    $this->printLine('курс %s-%s не изменился и равен %s.',
                        $pair->baseCurrency->alpha_code, $pair->quotedCurrency->alpha_code, $pair->rate);
                    continue;
                }
                if ($pair->hasErrors()) {
                    $this->printLine('курс %s-%s не получилось изменить из-за ошибки: ',
                        $pair->baseCurrency->alpha_code, $pair->quotedCurrency->alpha_code, $pair->getFirstError());
                } else {
                    $this->printLine('курс %s-%s обновился с %s на %s',
                        $pair->baseCurrency->alpha_code, $pair->quotedCurrency->alpha_code,
                        $pair->prevCurrencyPair ? $pair->prevCurrencyPair->rate : 0,
                        $pair->rate);
                }
            }
        }
    }

    /**
     * Конвертирует заданное значение из одной валюты в другую.
     *
     * @param $amount - сума для конвертирования
     * @param $base - код базовой валюты, например RUR
     * @param $quoted - код котируемой валюты, например USD
     */
    public function actionConvert($amount, $base, $quoted)
    {
        $baseCurrency = Currency::find()->byCode($base)->one();
        if ($baseCurrency)
        {
            try
            {
                $result = $baseCurrency->convertTo($amount, $quoted);
                $this->printLine('%s (%s) => %s (%s)', $amount, $base, $result, $quoted);
            }
            catch (InvalidValueException $e)
            {
                $this->printLine($e->getMessage());
            }
        }
        else
        {
            $this->printLine('Неизвестная базовая валюта');
        }

        return '';
    }

    protected function printLine($message)
    {
        if ($this->interactive)
        {
            $params = func_get_args();
            $params[0] .= PHP_EOL;
            call_user_func_array('printf', $params);
            \Yii::info(call_user_func_array('sprintf',$params), 'currency');
        }

        return $this;
    }

    public function options($actionId)
    {
        $options = parent::options($actionId);

        if ($actionId == 'update') $options[] = 'date';

        return $options;
    }

}