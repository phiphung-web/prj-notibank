<?php
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\GroupZalo */

$this->title = 'Thêm mới người bán';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-create">
    <div class="driver-form">
        <?php $form = ActiveForm::begin(); ?>
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Thông tin người bán </h3>
                </div>
                <div class="box-body">
                    <?= $form->field($model, 'name')->textInput() ?>
                    <?php
                        $categories = \app\models\GroupZaloCatalogue::find()->where(['status' => '1'])->orderBy('name', 'asc')->all();

                        foreach ($categories as $category) {
                            $options[$category->id] = $category->name;
                        }
                    ?>
                    <?= $form->field($model, 'group_zalo_catalogue_id')->widget(Select2::classname(), [
                        'data' => $options,
                        'options' => [
                            'multiple' => true,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>
                </div>
            </div>
            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
        <div class="clear" style="clear:both"></div>

    </div>

</div>
    