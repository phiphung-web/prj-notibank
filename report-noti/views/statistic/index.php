<?php

use yii\bootstrap\ActiveForm;
use yii\grid\GridViewAsset;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel */
/* @var $reason_reject_array */
/* @var $agencyList */

GridViewAsset::register($this);

$this->title = 'Danh sách đặt hàng trên web';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pay-transaction-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i></button>
            </div>
        </div>

        <div class="box-body">
            <?php echo $this->render('_search', [
                'agencyList' => $agencyList,
                'model' => $searchModel,
                'reason_reject_array' => $reason_reject_array,
            ]) ?>
        </div>
    </div>

    <div class="table-view-list js-ajax-table table-statistic">
        <?php
        $bookingList = $dataProvider->getModels();

        echo $this->render('table', compact(['searchModel', 'bookingList', 'dataProvider', 'reason_reject_array']));
        ?>
    </div>
</div>

<?php echo $this->render('modal_status', compact(['reason_reject_array'])) ?>

<div class="modal fade" id="modalDeleteList" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <?php $form = ActiveForm::begin(['method' => 'post']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Xóa lựa chọn</h4>
            </div>
            <div class="modal-body">
                <span>Bạn có chắc muốn xóa những chuyến xe này không? Không thể quay lại!</span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Delete</button>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>