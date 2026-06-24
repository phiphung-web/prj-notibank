<?php
use app\assets\AppAsset;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

AppAsset::register($this);

$this->registerCssFile('/css/toastr.min.css', ['position' => \yii\web\View::POS_HEAD]);
$this->registerJsFile('/js/toastr.min.js', ['position' => \yii\web\View::POS_HEAD]);
$this->title = 'Sign In';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>",
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>",
];
?>
<?php
$flashes = Yii::$app->session->getAllFlashes();
if (isset($flashes) && is_array($flashes) && count($flashes)) {
    $toastrMessages = [];
    foreach ($flashes as $type => $message) {
        $toastrMessages[] = ['type' => $type, 'message' => $message];
    }
    $toastrMessagesJson = json_encode($toastrMessages);
    $this->registerJs("var toastrMessages = $toastrMessagesJson;", \yii\web\View::POS_HEAD);
    $this->registerJs('toastrMessages.forEach(function(message) {toastr[message.type](message.message);});', \yii\web\View::POS_READY);
}
?>


<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>Admin</b>LTE</a>
    </div>
    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form
            ->field($model, 'username', $fieldOptions1)
            ->label(false)
            ->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

        <?= $form
            ->field($model, 'password', $fieldOptions2)
            ->label(false)
            ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

        <div class="row">
            <div class="col-xs-8">
                <?= $form->field($model, 'rememberMe')->checkbox() ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-4">
                <?= Html::submitButton('Sign in', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
        </div>


        <?php ActiveForm::end(); ?>

        <div class="social-auth-links text-center">
            <p>- OR -</p>
            <a href="#" class="btn btn-block btn-social btn-facebook btn-flat"><i class="fa fa-facebook"></i> Sign in
                using Facebook</a>
            <a href="#" class="btn btn-block btn-social btn-google-plus btn-flat"><i class="fa fa-google-plus"></i> Sign
                in using Google+</a>
        </div>
        <!-- /.social-auth-links -->

        <a href="#">I forgot my password</a><br>
        <a href="register.html" class="text-center">Register a new membership</a>

    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->