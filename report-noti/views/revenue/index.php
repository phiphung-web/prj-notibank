<?php

use yii\widgets\LinkPager;

$this->registerJsFile('/js/pages/revenue-chart.js', ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerJsFile('/js/pages/apexcharts.min.js', ['depends' => [\yii\web\YiiAsset::class]]);
/* @var $this yii\web\View */

$this->title = 'Doanh thu theo ngày';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="revenue-index">
    <div class="box box-green">
        <div class="box-header with-border">
            <h3 class="box-title">Filter</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="box-body">
            <?php echo $this->render('_search', [
                'model' => $searchModel,
            ]) ?>
        </div>
    </div>
    <div class="box">
        <div class="row">
            <div class="col-12 col-lg-4">
                <div id="cancel"></div>
            </div>
            <div class="col-12 col-lg-4">
                <div id="profit"></div>
            </div>
            <div class="col-12 col-lg-4">
                <div id="total"></div>
            </div>
        </div>
    </div>
    <div class="wrap-revenue" style="overflow-x: scroll;">
        <div class="wrap-table" style="width: 2000px">
            <table class="table table-striped table-bordered table-trip" style="background: #fff;">
                <thead>
                    <tr>
                        <th>Thao tác</th>
                        <th>Thời gian</th>
                        <th>Tổng số chuyến</th>
                        <th>Đã hủy</th>
                        <th>Đã điều</th>
                        <th>Thu khách</th>
                        <th>Lái xe thu</th>
                        <th>Tiền nhận</th>
                        <th>Tiền thu hộ</th>
                        <th>Chi</th>
                        <th class="text-nowrap">Lợi nhuận</th>
                        <?php foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) : ?>
                            <th><?= $value ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider as $model) : ?>
                        <tr id="date-<?= $model['dtDate'] ?>" data-profit="<?= $model['customerPrice'] - $model['driverPrice'] ?>">
                            <td><button class="btn btn-primary btn-openchart" data-date="<?= $model['dtDate'] ?>"><i class="fa fa-eye" aria-hidden="true"></i></button></td>
                            <td class="text-nowrap"><?= $model['dtDate'] ?></td>
                            <td><?= $model['totalTrips'] ?></td>
                            <td><?= $model['totalCancelTrips'] ?></td>
                            <td><?= $model['totalCompleteTrips'] ?></td>
                            <td class="text-bold"><?= Yii::$app->formatter->asInteger($model['customerPrice']) ?></td>
                            <td class="text-bold"><?= Yii::$app->formatter->asInteger($model['driverPrice']) ?></td>
                            <td class="text-bold"><?= Yii::$app->formatter->asInteger($model['customerPrice'] - $model['driverPrice']) ?></td>
                            <td class="text-bold"><?= Yii::$app->formatter->asInteger($model['moneyDebtAgency']) ?></td>
                            <td class="update_price text-nowrap" style="width: 130px">
                                <div class="index_update_price" style="position: relative;">
                                    <input type="text" name="price" value="<?= (int)$model['spend_price'] ?>" class="update_price form-control int" style="text-align: left;padding: 6px 3px; padding-right: 35px;" readonly>
                                    <button style="position: absolute;top: 50%;right: 3px;transform: translateY(-50%);" data-date="<?php echo $model['dtDate'] ?>"><i class="fa fa-check" aria-hidden="true"></i></button>
                                </div>
                            </td>
                            <td class="text-bold price-receive">
                                <?= Yii::$app->formatter->asInteger($model['customerPrice'] - $model['driverPrice'] - $model['moneyDebtAgency'] - $model['spend_price']) ?>
                            </td>
                            <?php foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) : ?>
                                <td style="text-align: left">
                                    <span class="text-primary">Total: <?= (int)$model['source_' . $key] ?></span>
                                    <br>
                                    <span class="text-success text-bold">Done: <?= (int)$model['source_' . $key . '_success'] ?> (<?= (! empty($model['source_' . $key]) ? round((int)$model['source_' . $key . '_success'] / (int)$model['source_' . $key] * 100) : 0) ?>%)</span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <tr class="chart" style="display: none;">
                            <td colspan="21">
                                <div class="revenue revenue-<?= $model['dtDate'] ?>" style="width: calc(100vw - 276px);"></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-total">
                        <td colspan="2"><strong>Total</strong></td>
                        <td><?= $totalRevenue['totalTrips'] ?></td>
                        <td><?= $totalRevenue['totalCancelTrips'] ?></td>
                        <td><?= $totalRevenue['totalCompleteTrips'] ?></td>
                        <td class="text-bold"><?= Yii::$app->formatter->asInteger($totalRevenue['totalCustomerPrice']) ?></td>
                        <td class="text-bold"><?= Yii::$app->formatter->asInteger($totalRevenue['totalDriverPrice']) ?></td>
                        <td class="text-bold"><?= Yii::$app->formatter->asInteger($totalRevenue['totalProfit']) ?></td>
                        <td class="text-bold"><?= Yii::$app->formatter->asInteger($totalRevenue['totalMoneyDebtAgency']) ?></td>
                        <td class="text-nowrap"><?= Yii::$app->formatter->asInteger($totalRevenue['totalSpendPrice']) ?></td>
                        <td class="text-bold"><?= Yii::$app->formatter->asInteger($totalRevenue['totalReceivePrice']) ?></td>
                        <?php foreach (SOURCE_TRIP_TYPE_LIST as $key => $value) : ?>
                            <td>
                                <span class="text-primary">Total: <?= $totalRevenue['sourceTotals'][$key] ?></span>
                                <br>
                                <span class="text-success text-bold">Done: <?= $totalRevenue['sourceSuccessTotals'][$key] ?> (<?= (! empty($totalRevenue['sourceTotals'][$key]) ? round(($totalRevenue['sourceSuccessTotals'][$key] / $totalRevenue['sourceTotals'][$key]) * 100) : 0) ?>%)</span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
/*
    <?php $pagination = $dataProvider->getPagination(); ?>
    <div class="d-flex" style="justify-content: space-between;margin-top: 10px;">
        <div>
            <?php
            $startIndex = $pagination->getPage() * $pagination->getPageSize() + 1;
            $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
            $totalCount = $dataProvider->getTotalCount();
            echo "Showing " . $startIndex . " to " . $endIndex . " of " . $totalCount . " entries";
            ?>
        </div>
        <?= LinkPager::widget([
            'pagination' => $pagination,
            'prevPageLabel' => 'Previous',
            'nextPageLabel' => 'Next',
            'options' => ['class' => 'pagination', 'style' => 'margin:0'],
            'linkOptions' => ['class' => 'page-link'],
            'maxButtonCount' => 5,
        ]) ?>
    </div>
*/
?>
<script>
    var TIME_RANGE = JSON.parse('<?php echo json_encode($timeRange) ?>');
    var SOURCE_TRIP_TYPE_LIST = JSON.parse('<?php echo json_encode(array_values(SOURCE_TRIP_TYPE_LIST)) ?>');
</script>