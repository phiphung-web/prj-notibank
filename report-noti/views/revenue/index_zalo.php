<?php

use yii\helpers\Html;

$this->title = 'Danh sách thống kê nguồn bán';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zalo-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                        class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search_zalo', [
                'model' => $searchModel,
            ]) ?>
        </div>
    </div>
    <div class="form-group">
        <!-- Html::a('Xuất Excel <span class="glyphicon glyphicon-download-alt"></span>',
        array_merge(['/revenue/zalo-export'], $_GET), ['class' => 'btn btn-success']) -->
    </div>
    <div class="table-view-list">
        <?php echo $this->render('table_zalo', compact(['dataProvider'])) ?>
    </div>
</div>
