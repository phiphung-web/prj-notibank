<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Admin */
/* @var $dataAgency */

$this->title = 'Thay đổi mật khẩu : ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài khoản', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->username, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<?php $form = ActiveForm::begin(); ?>

<div class="changepw-container">
    <div class="row">
        <div class="col-lg-12">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true, 'disabled' => true]) ?>
        </div>

        <div class="col-lg-12">
            <?= $form->field($changePasswordForm, 'new_password')->passwordInput() ?>
        </div>

        <div class="col-lg-12">
            <?= $form->field($changePasswordForm, 'confirm_password')->passwordInput() ?>
        </div>

        <div class="col-lg-12">
        <?= Html::submitButton('Thay đổi', [
            'class' => 'btn btn-primary',
            'data-confirm' => Yii::t('yii', 'Bạn có chắc chắn muốn thay đổi mật khẩu của tài khoản {username}?', [
                'username' => $model->username,
            ]),
            'data-method' => 'post',
        ]) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>