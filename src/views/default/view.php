<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel ashtokalo\currency\forms\ExchangeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model ashtokalo\currency\models\CurrencyPair */

$this->title = sprintf('Курс валют для пары %s-%s',
    $model->baseCurrency->alpha_code, $model->quotedCurrency->alpha_code);
$this->params['breadcrumbs'][] = ['label' => 'Курсы валют', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

/** @var \ashtokalo\currency\Module $module */
$module = \Yii::$app->controller->module;

?>

<?php if ($model->locked_by): ?>
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-info"></i> Внимание!</h5>
        Автоматическое обновление курса валют отключено сотрудником
        <?= $model->lockedBy->name ?> (<?= $model->lockedBy->email ?>).
        <?php if ($module->canLock($model)): ?>
        Нажмите кнопку <b>Включить автоматическое обновление</b> чтобы курсы автоматически обновлялись
        данными с сайта Центрального Банка России.
        <?php endif ?>
    </div>
<?php else: ?>
    <div class="alert alert-info alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h5><i class="icon fas fa-info"></i> Внимание!</h5>
        Курс валют обновляется автоматически несколько раз в день по данным с сайта Центрального Банка России.
        <?php if ($module->canUnlock($model)): ?>
        Нажмите кнопку <b>Выключить автоматическое обновление</b> чтобы отключить обновления.
        <?php endif ?>
    </div>
<?php endif ?>

<div class="p-3 mb-3 card currency-pair-index box box-primary">
    <?php Pjax::begin(); ?>
    <?php if ($module->canUpdate($model) || $module->canLock($model)): ?>
    <div class="mb-3 box-header with-border">
        <?php if ($module->canUpdate($model)): ?>
        <?= Html::a('Установить курс вручную', ['update', 'id' => $model->id], ['class' => 'btn btn-danger']) ?>
        <?php endif ?>
        <?php if ($model->locked_by): ?>
            <?php if ($module->canUnlock($model)): ?>
            <?= Html::a('Включить автоматическое обновление', ['unlock', 'id' => $model->id],
                ['class' => 'btn btn-success']) ?>
            <?php endif ?>
        <?php else: ?>
            <?php if ($module->canLock($model)): ?>
            <?= Html::a('Выключить автоматическое обновление', ['lock', 'id' => $model->id],
                ['class' => 'btn btn-danger']) ?>
            <?php endif ?>
        <?php endif ?>
    </div>
    <?php endif ?>
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
            ],
        ]); ?>
    </div>
    <?php Pjax::end(); ?>
</div>
