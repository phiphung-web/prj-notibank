<?php



/* @var $this yii\web\View */
/* @var $model app\models\Driver */

$this->title = 'Thêm tài xế';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài xế', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="driver-create">


    <?= $this->render('_form', [
        'model' => $model,
        'carModel' => $carModel,
    ]) ?>

</div>
