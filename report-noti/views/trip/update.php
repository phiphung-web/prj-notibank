<?php


/* @var $this yii\web\View */
/* @var $model app\models\Trip */
/* @var $modelTripGroup */

$this->title = 'Update Trip: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Trips', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
$this->params['module'] = (isset($module) ? $module : '');
?>
<div class="trip-update">
    <?= $this->render('_form_create', [
        'model' => $model,
        'modelTripGroup' => $modelTripGroup,
    ]) ?>
</div>