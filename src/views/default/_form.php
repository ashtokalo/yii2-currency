<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model ashtokalo\yii2\currency\models\CurrencyPair */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="currency-pair-form box box-primary">
    <?php $form = ActiveForm::begin(); ?>
    <div class="box-body table-responsive">
        <?= $form->field($model->baseCurrency, 'name')
            ->label($model->attributeLabels()['base_currency_id'])
            ->textInput(['disabled' => true]) ?>
        <?= $form->field($model->quotedCurrency, 'name')
            ->label($model->attributeLabels()['quoted_currency_id'])
            ->textInput(['disabled' => true]) ?>
        <?= $form->field($model, 'rate')->textInput() ?>
        <?= $form->field($model, 'locked')
            ->checkbox(['label' => 'Не обновлять автоматически']) ?>
    </div>
    <div class="box-footer">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success btn-flat']) ?>
        <?= Html::a('Отменить', ['view', 'id' => $model->id],
            ['class' => 'btn btn-default btn-flat']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<?php $this->registerCss('
.currency-pair-form { max-width: 800px; }
'); ?>