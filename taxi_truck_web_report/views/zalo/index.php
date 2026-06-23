<?php

use yii\helpers\Html;

$this->title = 'Danh sách các nguồn';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zalo-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $searchModel,
            ]) ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::a('Danh sách nhóm nguồn bán', ['/zalo-catalogue'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
    </div>
    <div class="table-view-list">
        <?php echo $this->render('table', compact(['dataProvider'])) ?>
    </div>
</div>