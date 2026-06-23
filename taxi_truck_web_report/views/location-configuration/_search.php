<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="location-search">

  <?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
  ]); ?>
  <div class="d-flex flex-column-mobile">
    <?= $form->field($model, 'keyword', [
      'options' => [
        'class' => 'form-group',
        'style' => 'width: calc((100% - 60px) / 4); margin-right:20px',
      ],
    ])->textInput(['value' => isset(Yii::$app->request->get('Location')['keyword']) ? Yii::$app->request->get('Location')['keyword'] : '']) ?>
  </div>
  <div class="form-group">
    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
  </div>

  <?php ActiveForm::end(); ?>

</div>