<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model ashtokalo\yii2\currency\models\CurrencyPair */

$this->title = 'Новый курс валют';
$this->params['breadcrumbs'][] = ['label' => 'Курсы валют', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => sprintf('Курс валют для пары %s-%s',
    $model->baseCurrency->alpha_code, $model->quotedCurrency->alpha_code), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Новый курс валют';
?>
<div class="currency-pair-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
