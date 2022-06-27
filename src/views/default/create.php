<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model ashtokalo\yii2\currency\models\CurrencyPair */

$this->title = 'Create Currency Pair';
$this->params['breadcrumbs'][] = ['label' => 'Currency Pairs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="currency-pair-create">

    <?= $this->render('_form', [
    'model' => $model,
    ]) ?>

</div>
