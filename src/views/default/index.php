<?php

use yii\helpers\Html;
use yii\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel ashtokalo\currency\web\forms\ExchangeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Курсы валют';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="alert alert-info alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <h5><i class="icon fas fa-info"></i> Внимание!</h5>
    Курс валют обновляется автоматически несколько раз в день по данным с сайта Центрального Банка России.
    Нажмите на кнопку <b>Подробности...</b> чтобы увидеть старые значения, установить курс вручную или
    включить/выключить автоматическое обновление.
</div>

<div class="p-3 mb-3 card currency-pair-index box box-primary">
    <div class="box-body table-responsive no-padding">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                [
                    'class' => \yii\grid\DataColumn::class,
                    'attribute' => 'base_currency_id',
                    'content' => function ($model)
                    {
                        return $model->baseCurrency->alpha_code . ' (' . $model->baseCurrency->code . ')';
                    }
                ],
                [
                    'class' => \yii\grid\DataColumn::class,
                    'attribute' => 'quoted_currency_id',
                    'content' => function ($model)
                    {
                        return $model->quotedCurrency->alpha_code . ' (' . $model->quotedCurrency->code . ')';
                    }
                ],
                'rate',
                'period:raw',
                'origin:raw',
                'lockedShort',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key)
                        {
                            return Html::a('Подробности...', $url, ['class' => 'btn btn-success']);
                        }
                    ]],
            ],
        ]); ?>
    </div>
</div>
