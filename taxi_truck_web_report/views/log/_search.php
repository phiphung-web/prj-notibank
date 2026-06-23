<?php

use yii\helpers\Html;
use yii\jui\DatePicker;

?>

<form method="get" class="js-form">
    <div class="d-flex flex-column-mobile">
        <?= Html::dropDownList('user_name', Yii::$app->request->get('user_name'), $searchData['usernames'], ['class' => 'form-group form-control', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px', 'prompt' => 'Chọn người dùng']) ?>

        <?= Html::dropDownList('action', Yii::$app->request->get('action'), $searchData['actions'], ['class' => 'form-group form-control', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px', 'prompt' => 'Chọn hành động']) ?>

        <?= DatePicker::widget([
            'name' => 'created_on',
            'options' => ['class' => 'form-group form-control', 'style' => 'width: calc((100% - 60px) / 4); margin-right: 20px', 'placeholder' => 'Select date ...'],
            'dateFormat' => 'yyyy-MM-dd',
            'value' => $searchData['created_on'],
            'clientOptions' => ['defaultDate' => $searchData['created_on']],
        ]) ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>
</form>
<?php
$script = <<< JS
     $('button[type=reset]').click(function(e) {
         e.preventDefault();
         $('.js-form select, .js-form input').val('');
         $('.js-form input.hasDatepicker').val(new Date().toLocaleDateString('vi', { year: 'numeric', month: '2-digit', day: '2-digit' }).split('/').reverse().join('-'));
     });
JS;
$this->registerJs($script);
?>