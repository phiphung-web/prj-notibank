<?php



/* @var $this yii\web\View */
/* @var $model app\models\Trip */
/* @var $modelTripGroup */
/* @var $modelBooking */
/* @var $method */

$this->title = ($method == 'copy') ? 'Copy chuyến xe' : 'Thêm chuyến xe';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách chuyến xe', 'url' => ['index']];
if ($method == 'copy') {
    $this->params['breadcrumbs'][] = ['label' => 'Copy chuyến xe', 'url' => ['view', 'id' => Yii::$app->request->get('id')]];
}
$this->params['breadcrumbs'][] = $this->title;
$this->params['module'] = (isset($module) ? $module : '');
?>
<div class="trip-create">
  <?= $this->render('_form_create', [
    'model' => $model,
    'modelTripGroup' => $modelTripGroup,
    'modelBooking' => $modelBooking,
    'modelRequestCallBack' => isset($modelRequestCallBack) ? $modelRequestCallBack : null,
    'method' => $method,
  ]) ?>
</div>