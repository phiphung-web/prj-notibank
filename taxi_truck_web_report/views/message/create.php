<?php



/* @var $this yii\web\View */
/* @var $model app\models\Message */

$this->title = 'Gửi thông báo';
$this->params['breadcrumbs'][] = ['label' => 'Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="message-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
