<?php

namespace ashtokalo\currency\models;

use ashtokalo\currency\behaviors\EditorBehavior;
use ashtokalo\currency\behaviors\TimestampBehavior;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "{{%currency_pair}}".
 *
 * @property int $id
 * @property int $next_currency_pair_id идентификатор валютной пары, которая действовала после данной
 * @property int $base_currency_id идентификатор базовой валюты
 * @property int $quoted_currency_id идентификатор котируемой валюты
 * @property double $rate отношение цен двух валют
 * @property string $created_at
 * @property int $created_by
 * @property string $locked_at дата блокировки, если для валютной пары заблокированы автоматические обновления
 * @property int $locked_by идентификатор пользователя, который выполнил блокировку
 *
 * @property boolean $locked TRUE если автоматическое обновление выключено, FALSE если включено
 *
 * @property CurrencyPair $nextCurrencyPair
 * @property CurrencyPair $prevCurrencyPair
 * @property Currency $baseCurrency
 * @property Currency $quotedCurrency
 */
class CurrencyPair extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%currency_pair}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['base_currency_id', 'quoted_currency_id', 'rate'], 'required'],
            [['base_currency_id', 'quoted_currency_id', 'locked_by'], 'integer'],
            [['rate'], 'number'],
            [['locked_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'next_currency_pair_id' => 'Следующая пара',
            'base_currency_id' => 'Базовая валюта',
            'quoted_currency_id' => 'Котируемая валюта',
            'rate' => 'Курс',
            'created_at' => 'Время создания',
            'created_by' => 'Создал',
            'locked_at' => 'Дата заморозки',
            'locked_by' => 'Заморозил',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'base_currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getQuotedCurrency()
    {
        return $this->hasOne(Currency::class, ['id' => 'quoted_currency_id']);
    }

    /**
     * {@inheritdoc}
     * @return CurrencyPairQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CurrencyPairQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
            [
                'class' => EditorBehavior::class,
                'editorAttribute' => false,
            ],
        ];
    }

    public function getNextCurrencyPair()
    {
        return $this->hasOne(CurrencyPair::class, ['id' => 'next_currency_pair_id']);
    }

    public function getPrevCurrencyPair()
    {
        return $this->hasOne(CurrencyPair::class, ['next_currency_pair_id' => 'id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert)
        {
            $prevCurrencyPair = CurrencyPair::find()->andWhere([
                'base_currency_id' => $this->base_currency_id,
                'quoted_currency_id' => $this->quoted_currency_id,
                'next_currency_pair_id' => null,
            ])->one();

            if ($prevCurrencyPair)
            {
                $prevCurrencyPair->next_currency_pair_id = $this->id;
                $prevCurrencyPair->save();
            }
        }
    }

    public function lock()
    {
        if ($this->locked_at) return true;

        $pair = new CurrencyPair([
            'base_currency_id'   => $this->base_currency_id,
            'quoted_currency_id' => $this->quoted_currency_id,
            'rate'               => $this->rate,
            'locked'             => true,
        ]);

        if ($pair->save())
        {
            $this->refresh();

            return true;
        }

        return false;
    }

    public function unlock()
    {
        if (!$this->locked_at) return true;

        $pair = new CurrencyPair([
            'base_currency_id'   => $this->base_currency_id,
            'quoted_currency_id' => $this->quoted_currency_id,
            'rate'               => $this->rate,
        ]);

        if ($pair->save())
        {
            $this->refresh();

            return true;
        }

        return false;
    }

    public function getLocked()
    {
        return $this->locked_at ? true : false;
    }

    public function setLocked($value)
    {
        if ($value)
        {
            $this->locked_by = Yii::$app->user->id ?: null;
            $this->locked_at = new Expression('UTC_TIMESTAMP()');
        }
        else
        {
            $this->locked_by = $this->locked_at = null;
        }
    }
}
