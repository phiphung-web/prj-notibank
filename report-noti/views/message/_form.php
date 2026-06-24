<?php

use app\models\Driver;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Message */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="message-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php

    $listDriver = Driver::find()->andWhere(['is_sub_driver' => DRIVER_TYPE_NORMAL])->all();

    $data = ['0' => 'Tất cả'];

    foreach ($listDriver as $driver) {
        $data[$driver->username] = $driver->toString();
    }
    ?>
    <?= $form->field($model, 'phone')->widget(Select2::classname(), [
        'data' => $data,
        'language' => 'vi',
        'options' => ['placeholder' => 'Select a state ...'],
        'pluginOptions' => [
            //                'allowClear' => true
        ],
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
