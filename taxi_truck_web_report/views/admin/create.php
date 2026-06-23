<?php



/* @var $this yii\web\View */
/* @var $model app\models\Admin */
/* @var $dataAgency */

$this->title = 'Thêm tài khoản';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài khoản', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="admin-create">
    <?= $this->render('_form', [
        'model' => $model,
        'dataAgency' => $dataAgency,
    ]) ?>
</div>
