<?php

namespace ashtokalo\yii2\currency\models;

use Yii;
use yii\base\InvalidValueException;

/**
 * This is the model class for table "{{%currency}}".
 *
 * @property int $id
 * @property int $code числовой код валюты ISO 4217, например `810` для российского рубля
 * @property string $alpha_code буквенный код валюты ISO 4217, например `RUR` для российского рубля
 * @property int $fractional_size количество цифр после запятой, дробная часть валюты
 * @property string $name наименование валюты по-русски
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%currency}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'alpha_code', 'name'], 'required'],
            [['code', 'fractional_size'], 'integer'],
            [['alpha_code'], 'string', 'max' => 3],
            [['name'], 'string', 'max' => 255],
            [['alpha_code'], 'unique'],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Числовой код',
            'alpha_code' => 'Буквенный код',
            'name' => 'Наименование валюты',
            'fractional_size' => 'Дробная часть',
        ];
    }

    /**
     * {@inheritdoc}
     * @return CurrencyQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CurrencyQuery(get_called_class());
    }

    /**
     * Конвертировать заданное значение в текущей валюте в другую валюту
     *
     * @param float $value значение для конвертации
     * @param string|Currency $quotedCurrency буквенный или цифровой код валюты или объект Currency
     *
     * @return float
     */
    public function convertTo($value, $quotedCurrency)
    {
        if (!($quotedCurrency instanceof Currency))
        {
            $quotedCurrencyCode = $quotedCurrency;
            $quotedCurrency = Currency::find()->byCode($quotedCurrencyCode)->cache()->one();
            if (!$quotedCurrency)
            {
                throw new InvalidValueException(sprintf('Неизвестная валюта %s',
                    $quotedCurrencyCode));
            }
        }

        $pair = CurrencyPair::find()->pair($this, $quotedCurrency)->cache(60)->latest()->one();

        if (!$pair)
        {
            $backPair = CurrencyPair::find()->pair($quotedCurrency, $this)->cache(60)->latest()->one();

            if (!$backPair)
            {
                throw new InvalidValueException(sprintf('Курс валют для пары %s-%s не определён',
                    $this->alpha_code, $quotedCurrency->alpha_code));
            }

            return $value / $backPair->rate;
        }

        return $value * $pair->rate;
    }
}
