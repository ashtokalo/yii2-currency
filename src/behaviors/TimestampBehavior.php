<?php

namespace ashtokalo\currency\behaviors;

use yii\db\Expression;

class TimestampBehavior extends \yii\behaviors\TimestampBehavior
{
    /**
     * {@inheritdoc}
     */
    protected function getValue($event)
    {
        if ($this->value === null) return new Expression('UTC_TIMESTAMP(6)');

        return parent::getValue($event);
    }
}