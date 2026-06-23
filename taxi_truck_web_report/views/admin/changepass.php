<?php

use app\helpers\MyStringHelper;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Thông tin tài khoản';
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="site-changepassword">

	<?php if (Yii::$app->getSession()->hasFlash('success')) : ?>
		<div class="alert alert-success">
            Đổi mật khẩu thành công.
        </div>
	<?php elseif (Yii::$app->getSession()->hasFlash('error')) :?>
		<div class="alert alert-danger">
            Lỗi!
        </div>
	<?php else:?>

    <h3>Đổi mật khẩu</h3>


    <?php $form = ActiveForm::begin([
        'id' => 'changepassword-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">
                        {input}</div>\n<div class=\"col-lg-5\">
                        {error}</div>",
            'labelOptions' => ['class' => 'col-lg-2 control-label'],
        ],
    ]); ?>
        <?= $form->field($model, 'oldpass', ['inputOptions' => [
            'placeholder' => 'Old Password',
        ]])->passwordInput() ?>

        <?= $form->field($model, 'newpass', ['inputOptions' => [
            'placeholder' => 'New Password',
        ]])->passwordInput() ?>

        <?= $form->field($model, 'repeatnewpass', ['inputOptions' => [
            'placeholder' => 'Repeat New Password',
        ]])->passwordInput() ?>

        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-11">
                <?= Html::submitButton('Change password', [
                    'class' => 'btn btn-primary',
                ]) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
	<?php endif;?>
</div>

<div class="revenue-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">
          <?php echo $this->render('_search_user', [
            'model' => $searchModel,
          ]) ?>
        </div>
    </div>
    <div class="box box-green">
      <?php $data = $dataProvider->getModels(); ?>
      <?php
        if (isset($data) && is_array($data) && count($data)) {
            ?>
        <div><h4> Tổng đài viên: <?php echo $data[0]['username'] ?> </h4></div>
        <div><h4> Số chuyến đã điều: <?php echo $data[0]['countTrip'] ?> </h4></div>
        <div><h4> Tổng tiền thưởng: <?php echo MyStringHelper::convertIntegerToPrice($data[0]['money_bonus']) ?> ₫</h4></div>
    <?php
        } ?>
    </div>
</div>
