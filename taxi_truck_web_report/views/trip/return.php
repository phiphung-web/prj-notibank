<?php



/* @var $this yii\web\View */
/* @var $model app\models\Trip */

$this->title = 'Trả lịch';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách chuyến xe', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['module'] = (isset($module) ? $module : '');
?>
<div class="trip-create">


    <?= $this->render('_form_create', [
        'model' => $model,
        'modelTripGroup' => $modelTripGroup,
        'tripReturn' => $tripReturn,
    ]) ?>

</div>