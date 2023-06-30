<?php

namespace ashtokalo\yii2\currency;

use ashtokalo\yii2\currency\models\CurrencyPair;
use ashtokalo\yii2\currency\origins\CbrfOrigin;
use ashtokalo\yii2\currency\origins\RateOrigin;

class Module extends \yii\base\Module
{
    const ACCESS_UPDATE = 'update';
    const ACCESS_LOCK   = 'lock';
    const ACCESS_UNLOCK = 'unlock';

    public $defaultRoute = 'default';

    public $permissions = [];

    /**
     * @var array источники котировок, название классов или массивы для инициализации классов,
     *      наследников RateOrigin.
     */
    public $rateOrigins = [
        CbrfOrigin::class,
    ];

    public function can(string $permission, array $params = [], bool $allowCaching = true)
    {
        if (isset($this->permissions[$permission])) {
            if (is_bool($this->permissions[$permission])) return $this->permissions[$permission];
            if (is_callable($this->permissions[$permission])) {
                return call_user_func($this->permissions[$permission], $params, $allowCaching);
            }
            if (is_string($this->permissions[$permission])) $permission = $this->permissions[$permission];
        }
        return \Yii::$app->user->can($permission, $params, $allowCaching);
    }

    public function canUpdate(CurrencyPair $model, $allowCaching = true)
    {
        return $this->can(static::ACCESS_UPDATE, ['model' => $model], $allowCaching);
    }

    public function canLock(CurrencyPair $model, $allowCaching = true)
    {
        return $this->can(static::ACCESS_LOCK, ['model' => $model], $allowCaching);
    }

    public function canUnlock(CurrencyPair $model, $allowCaching = true)
    {
        return $this->can(static::ACCESS_UNLOCK, ['model' => $model], $allowCaching);
    }

    /**
     * @return RateOrigin[]
     */
    function getRateOrigins(): array
    {
        static $cleared = false;
        if (!$cleared) {
            foreach ($this->rateOrigins as &$rateOrigin) {
                if (is_string($rateOrigin) || is_array($rateOrigin)) {
                    $rateOrigin = \Yii::createObject($rateOrigin);
                }
            }
            $cleared = true;
        }
        return $this->rateOrigins;
    }
}
