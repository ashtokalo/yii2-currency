<?php

namespace ashtokalo\yii2\currency\forms;

use ashtokalo\yii2\currency\models\Currency;
use Yii;
use ashtokalo\yii2\currency\models\CurrencyPair;
use yii\debug\models\search\User;
use yii\helpers\Url;

class Exchange extends CurrencyPair
{
    public $locked;

    public function rules()
    {
        return [
            [['base_currency_id', 'quoted_currency_id', 'rate'], 'required'],
            [['base_currency_id', 'quoted_currency_id', 'locked_by'], 'integer'],
            [['rate'], 'number', 'min' => 0.0001],
            [['locked_at'], 'safe'],
            [['locked', 'rate'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'lockedShort' => 'Обновление',
            'created_at' => 'Действует с',
            'origin' => 'Источник',
            'rateExplained' => 'Курс обмена',
            'period' => 'Период действия',
        ] + parent::attributeLabels();
    }

    public function getLockedShort()
    {
        return $this->locked_at ? 'ручное' : 'автоматическое';
    }

    public function getCreated()
    {
        return Yii::$app->formatter->format($this->created_at, 'datetime') .
            ($this->created_by
                ? ' / ' . $this->createdBy->name . ' (' . $this->createdBy->email . ')'
                : 'автоматически');
    }

    public function getOrigin()
    {
        return $this->created_by
            ? sprintf('<a href="mailto:%s">%s</a>', $this->createdBy->email, $this->createdBy->name)
            : '<nobr><a href="http://cbr.ru">Центральный Банк РФ</a></nobr>';
    }

    public function getRateExplained()
    {
        return $this->rate . ' <nobr><em>(1 ' . $this->baseCurrency->alpha_code . ' = ' .
            $this->rate . ' ' . $this->quotedCurrency->alpha_code . ')</em></nobr>';
    }

    public function getPeriod()
    {
        return sprintf('<nobr>с %s по %s</nobr>',
            Yii::$app->formatter->format($this->created_at, 'datetime'),
            $this->nextCurrencyPair
                ? Yii::$app->formatter->format($this->nextCurrencyPair->created_at, 'datetime')
                : 'настоящее время');
    }
}
