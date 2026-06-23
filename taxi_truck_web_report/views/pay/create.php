<?php



/* @var $this yii\web\View */
/* @var $model app\models\PayTransaction */

$this->title = 'Nạp tiền';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách nạp tiền', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-transaction-create">


    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

