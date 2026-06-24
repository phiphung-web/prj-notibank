<?php



/* @var $this yii\web\View */
/* @var $model app\models\Trip */

$this->title = 'Bán lịch';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách chuyến xe', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trip-create">


    <?= $this->render('_form_create', [
        'model' => $model,
        'modelTripGroup' => $modelTripGroup,
    ]) ?>

</div>