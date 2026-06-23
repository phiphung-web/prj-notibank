<?php

use app\models\Driver;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use yii\widgets\LinkPager;

$this->title = 'Danh sách vị trí lái xe';
$this->params['breadcrumbs'][] = $this->title;

$modelDriver = new Driver();
$this->registerJsFile('/js/pages/driver.js', ['depends' => [\yii\web\YiiAsset::class]]);
?>
<style>
    .location{
        width: 35%;
    }
</style>
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
      <?php echo $this->render('_location_search', [
        'model' => $model,
      ]) ?>
    </div>
  </div>
  <?php $driverList = $dataProvider->getModels(); ?>
  <table id="datatables_w0" class="table table-striped table-bordered" style="background: #fff;">
    <thead>
    <tr>

      <th>
        <?= $model->attributeLabels()['display_name'] ?>
      </th>
      <th>
        <?= $model->attributeLabels()['bks'] ?>
      </th>
      <th class="text-center">Hạng</th>
      <th class="text-center">Loại tài khoản</th>
      <th class="text-center location">
        Vị trí
      </th>
        <th class="text-center">Thời gian cập nhật</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($driverList) && is_array($driverList) && count($driverList)) {
        foreach ($driverList as $driverItem) {
            ?>
        <tr data-key="<?= $driverItem['id'] ?>" role="row">
          <td>
            <?= $driverItem['display_name'] ?> -
            <?= $driverItem['username'] ?>
          </td>
          <td>
            <?= $driverItem['bks'] ?>
          </td>
          <td class="text-center text-bold text-primary">
            <?= isset($driverItem['driver_rank']) && ! empty($driverItem['driver_rank']) ? RANK_DRIVER_LIST[$driverItem['driver_rank']] : 'Bình thường' ?>
          </td>
          <td class="text-center text-bold">
            <?= isset($driverItem['driver_ban']) && $driverItem['driver_ban'] == STATUS_DRIVER_BAN ? '<span class="text-danger">' . STATUS_DRIVER_BAN_LIST[STATUS_DRIVER_BAN] . '</span>' : '<span class="text-info">' . STATUS_DRIVER_BAN_LIST[$driverItem['driver_ban']] . '</span>' ?>
          </td>
          <td> <?= $driverItem['location'] ?>
          </td>
            <td>
              <?= $driverItem['driving_at'] ?>
            </td>
        </tr>
        <?php
        if (isset($driverItem['driver_ban']) && ! empty($driverItem['driver_ban'])) {
            $driverSubList = Driver::find()->andWhere(['parent_id' => $driverItem['id']])->innerJoinWith('car')->all();
            if (isset($driverSubList)) {
                foreach ($driverSubList as $driverSubItem) {
                    ?>
              <tr data-key="<?= $driverSubItem['id'] ?>" role="row">
                <td>
                  |----
                  <?= $driverSubItem['display_name'] ?> -
                  <?= $driverSubItem['username'] ?>
                </td>
                <td>
                  <?= $driverSubItem->car->bks ?>
                </td>
                <td class="text-center text-bold text-primary">
                  <?= isset($driverSubItem['driver_rank']) && ! empty($driverSubItem['driver_rank']) ? RANK_DRIVER_LIST[$driverSubItem['driver_rank']] : 'Bình thường' ?>
                </td>
                <td class="text-center text-bold">
                  <span class="text-yellow">Tài xế phụ</span>
                </td>
                  <td> <?= $driverSubItem['location'] ?>
                  </td>
                <td>
                  <?= $driverSubItem['driving_at'] ?>
                </td>
              </tr>
              <?php
                }
            }
        } ?>
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
<div class="modal fade" id="modalReject" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <?php $form = ActiveForm::begin(['method' => 'post', 'id' => 'form-update-status-driver']); ?>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Cập nhật trạng thái</h4>
      </div>
      <div class="modal-body">
        <?= $form->field($modelDriver, 'reason')->widget(Select2::classname(), [
          'data' => $reason_reject_array,
          'options' => ['placeholder' => 'Chọn lý do'],
          'pluginOptions' => [
            'class' => 'type-reject-driver-modal',
          ],
        ]); ?>
        <div class="note-driver-modal hidden">
          <?= $form->field($modelDriver, 'reason')->textarea(['maxlength' => true, 'class' => 'form-control input-reason-lock', 'disabled' => true]) ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary btn-submit-status">Save changes</button>
      </div>
    </div>
    <?php ActiveForm::end(); ?>
  </div>
</div>
