<?php

use app\models\Driver;
use app\models\LogRequest;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách tài xế';
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
            <div class="trip-search">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                ]); ?>
                <?php
                $listDriver = Driver::find()->where(['driver_ban' => STATUS_DRIVER_BAN_WAIT_REVIEW])->all();
                $data = ['0' => 'Tất cả'];
                foreach ($listDriver as $driver) {
                    $data[$driver->username] = $driver->toString();
                }
                ?>
                <?= $form->field($model, 'username')->widget(Select2::classname(), [
                    'data' => $data,
                    'language' => 'vi',
                    'options' => ['placeholder' => 'Select a state ...'],
                    'pluginOptions' => [],
                ]); ?>
                <div class="form-group">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

    <?php
    $driverList = $dataProvider->getModels();
    ?>
    <table id="datatables_w0" class="table table-striped table-bordered table-register-driver" width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th><?= $model->attributeLabels()['display_name'] ?></th>
                <th><?= $model->attributeLabels()['username'] ?></th>
                <th><?= $model->attributeLabels()['bks'] ?></th>
                <th class="text-center"><?= $model->attributeLabels()['color'] ?></th>
                <th>Lịch sử</th>
                <th class="text-center"><?= $model->attributeLabels()['created_on'] ?></th>
                <th style="width: 100px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($driverList) && is_array($driverList) && count($driverList)) {
                foreach ($driverList as $driverItem) {
                    ?>
                    <tr data-key="<?= $driverItem->id ?>" role="row">
                        <td><?= $driverItem->display_name ?></td>
                        <td><?= $driverItem->username ?></td>
                        <td><?= $driverItem->bks ?></td>
                        <td class="text-center"><?= $driverItem->color ?></td>
                        <td>
                            <?php
                            $listLog = LogRequest::find()->where(['driver_id' => $driverItem->id])->orderBy(['created_on' => 'DESC'])->all();
                    if (isset($listLog) && is_array($listLog) && count($listLog)) {
                        foreach ($listLog as $key => $value) {
                            echo 'Lần ' . ($key + 1) . ' - ' . date('d/m/Y H:i', strtotime($value->created_on)) . ': ' . STATUS_DRIVER_BAN_LIST[$value->status] . (! empty($value->accepted_on) ? ' (' . date('d/m/Y H:i', strtotime($value->accepted_on)) . ')' : '') . ' <br>';
                        }
                    } ?>
                        </td>
                        <td class="text-center"><?= $driverItem->created_on ?></td>
                        <td class="d-flex" style="justify-content: space-evenly;">
                            <?php $url_accept = Url::to(['driver/accept-driver-sub', 'id' => $driverItem->id, 'status' => STATUS_DRIVER_BAN]); ?>
                            <?php $url_reject = Url::to(['driver/accept-driver-sub', 'id' => $driverItem->id, 'status' => STATUS_DRIVER_NORMAL]); ?>
                            <?=
                            Html::a('<span class="fa fa-check"></span>', $url_accept, [
                                'title' => 'Xác nhận tài khoản',
                                'data-confirm' => Yii::t('yii', 'Chấp nhận yêu cầu làm tài xế nhiều xe của tài khoản này?'),
                                'data-method' => 'post',
                                'class' => 'btn-success btn ',
                            ]); ?>
                            <?=
                            Html::a('<span class="fa fa-trash"></span>', $url_reject, [
                                'title' => 'Xóa tài khoản',
                                'data-confirm' => Yii::t('yii', 'Hủy bỏ yêu cầu làm tài xế nhiều xe của tài khoản này?'),
                                'data-method' => 'post',
                                'class' => 'btn-danger btn ',
                            ]); ?>
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