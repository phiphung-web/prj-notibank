<?php
$this->registerJsFile('/datetimepicker/jquery.datetimepicker.full.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCssFile('/datetimepicker/jquery.datetimepicker.css');

/* @var $this yii\web\View */
/* @var $model app\models\Booking */
/* @var $data_booking app\models\Booking */
/* @var $reason_reject_array */

$this->title = 'Sửa lịch đặt xe';
$this->params['breadcrumbs'][] = ['label' => 'Danh sách tài xế', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="booking-page booking-edit">
    <?php echo $this->render('form', compact(['model', 'reason_reject_array'])) ?>
</div>