<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách khu vực';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="driver-index">
    <div class="box box-green" style="margin-bottom: 20px; border-top: 0;">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $model,
            ]) ?>
        </div>
    </div>

    <?php
    $areaList = $dataProvider->getModels();
    ?>
    <table id="datatables_w0" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th><?= $model->attributeLabels()['area_name'] ?></th>
                <th><?= $model->attributeLabels()['districtid'] ?></th>
                <th><?= $model->attributeLabels()['provinceid'] ?></th>
                <th><?= $model->attributeLabels()['description'] ?></th>
                <th style="width: 150px"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($areaList) && is_array($areaList) && count($areaList)) {
                foreach ($areaList as $areaItem) {
                    ?>
                    <tr data-key="<?= $areaItem->id ?>" role="row">
                        <td><?= $areaItem->area_name ?></td>
                        <td><?= $areaItem->vnDistrict->name ?></td>
                        <td><?= $areaItem->vnProvince->name ?></td>
                        <td><?= $areaItem->description ?></td>
                        <td class="d-flex" style="justify-content: space-evenly;">
                            <a href="/area/update?id=<?= $areaItem->id ?>" class="btn btn-primary" title="Update" aria-label="Update" data-pjax="0"><span class="fa fa-pencil" aria-hidden="true"></span></a>
                            <?php
                            echo Html::a('<i class="fa fa-trash" aria-hidden="true"></i>', Url::to(['/area/delete', 'id' => $areaItem->id]), [
                                'title' => 'Xóa khu vực',
                                'data-confirm' => Yii::t('yii', 'Xóa khu vực này?'),
                                'data-method' => 'post',
                                'class' => 'btn-danger btn ',
                            ])
                            ?>
                            <a href="/area/create?id-clone=<?= $areaItem->id ?>" class="btn btn-info" title="Update" aria-label="Update" data-pjax="0"><i class="fa fa-files-o" aria-hidden="true"></i></a>
                        </td>
                    </tr>
                <?php
                }
            } else { ?>
                <tr>
                    <td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="d-flex" style="justify-content: space-between;">
        <div>
            <?php
            $startIndex = $dataProvider->getPagination()->getPage() * $dataProvider->getPagination()->getPageSize() + 1;
            $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
            $totalCount = $dataProvider->getTotalCount();
            echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
            ?>
        </div>
        <?=
        LinkPager::widget([
            'pagination' => $dataProvider->getPagination(),
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]);
        ?>
    </div>
</div>