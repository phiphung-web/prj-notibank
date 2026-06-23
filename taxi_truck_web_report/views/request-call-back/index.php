<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->registerCssFile('/css/pages/request-call-back.css');
$this->registerJsFile('/js/pages/request-call-back.js', ['depends' => [YiiAsset::class]]);
$this->title = 'Danh sách yêu cầu liên hệ';
$this->params['breadcrumbs'][] = $this->title;

$paramPhone = ! empty($_GET['SearchRequestCallBack']['phone']) ? $_GET['SearchRequestCallBack']['phone'] : '';
?>

<div class="request-call-back-warp">
  <div class="box box-green">
    <div class="box-header with-border">
      <h3 class="box-title">Filter</h3>
      <div class="box-tools pull-right">
        <button type="button" class="btn btn-box-tool" data-widget="collapse">
          <i class="fa fa-minus"></i>
        </button>
      </div>
    </div>

    <div class="box-body">
      <?php
      $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
          'class' => 'request-call-back-search',
        ],
        'enableClientValidation' => true,
        'validateOnType' => true,
      ])
      ?>

      <div class="fields">
        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-12">
            <?= $form->field($model, 'phone')->textInput(['value' => $paramPhone]) ?>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <?= $form->field($model, 'status')->dropDownList(
          STATUS_REQUEST_CALL_BACK,
          isset($_GET['SearchRequestCallBack']['status']) ? ['options' => [$_GET['SearchRequestCallBack']['status'] => ['selected' => true]]] : []
      )
            ?>
          </div>
          <div class="col-lg-3 col-md-6 col-sm-12">
            <?=
            $form->field($model, 'createdOnTimeRange')->widget(
                DateRangePicker::className(),
                [
                'presetDropdown' => true,
                'hideInput' => true,
                'startAttribute' => 'createdOnTimeStart',
                'endAttribute' => 'createdOnTimeEnd',
                'pluginOptions' => [
                  'locale' => ['format' => 'Y-MM-DD'],
                ],
              ]
            );
            ?>
          </div>
        </div>

        <div class="action-box">
          <?= Html::submitButton('Tìm kiếm', ['class' => 'btn btn-primary']) ?>
        </div>
      </div>

      <?php ActiveForm::end() ?>
    </div>
  </div>

  <div class="table-view-list mt-10">
    <table class="table table-striped table-bordered table-request-call-back" style="background-color: #fff">
      <thead>
        <tr>
          <th class="phone">Số điện thoại</th>
          <th class="status">Trạng thái</th>
          <th class="type-reject">Loại từ chối</th>
          <th class="note">Ghi chú</th>
          <th class="create-date">Ngày tạo</th>
          <th class="edit-date">Lần sửa cuối</th>
          <th class="action"></th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (! empty($dataRequestCallBack)) :
          $requestCallBackList = $dataRequestCallBack->getModels();
          foreach ($requestCallBackList as $itemRequestCallBack) :
        ?>
            <tr data-key="<?= $itemRequestCallBack->id ?>">
              <td class="phone"><?= $itemRequestCallBack->phone ?></td>
              <td>
                <?php if ($itemRequestCallBack->status == REQUEST_CALL_BACK_WAITING) : ?>
                  Chờ xử lí
                <?php elseif ($itemRequestCallBack->status == REQUEST_CALL_BACK_CONFIRM) : ?>
                  Đã xác nhận
                <?php else : ?>
                  Hủy bỏ
                <?php endif; ?>
              </td>
              <td>
                <?= ! empty($dataReasonReject) && ! empty($dataReasonReject[$itemRequestCallBack->type_reject]) ? $dataReasonReject[$itemRequestCallBack->type_reject] : '' ?>
              </td>
              <td><?= $itemRequestCallBack->note ?></td>
              <td><?= $itemRequestCallBack->created_on ?></td>
              <td><?= $itemRequestCallBack->modified_on ?></td>
              <td class="action-box">
                <?php if ($itemRequestCallBack->status == REQUEST_CALL_BACK_WAITING) : ?>
                  <a href="/call-quote?idCallBack=<?= $itemRequestCallBack->id ?>&phone=<?= $itemRequestCallBack->phone ?>&source=<?= $itemRequestCallBack->source_trip ?>" class="btn">Tư vấn</a>
                  <button type="button" class="btn btn-modal-cancel-phone" data-toggle="modal" data-target="#cancelPhoneModal" data-id="<?= $itemRequestCallBack->id ?>">
                    Hủy
                  </button>
                <?php endif; ?>
              </td>
            </tr>
        <?php
          endforeach;
        endif;
        ?>
      </tbody>
    </table>

    <?=
    LinkPager::widget([
      'pagination' => $dataRequestCallBack->getPagination(),
      'prevPageLabel' => 'Trước',
      'nextPageLabel' => 'Sau',
      'options' => ['class' => 'pagination', 'style' => 'margin:0'],
      'linkOptions' => ['class' => 'page-link'],
      'maxButtonCount' => 5,
    ]);
    ?>
  </div>
</div>

<!-- Modal cancel phone -->
<div class="modal-cancel-phone modal fade" id="cancelPhoneModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Hủy tư vấn</h4>
      </div>

      <div class="modal-body">
        <?php
        $form = ActiveForm::begin([
          'action' => ['cancel'],
          'method' => 'post',
          'options' => [
            'class' => 'form-request-call-back-cancel',
          ],
          'enableClientValidation' => true,
          'validateOnType' => true,
        ])
        ?>

        <div class="fields">
          <div class="group-control">
            <?= $form->field($model, 'type_reject')->dropDownList($dataReasonReject); ?>
          </div>

          <div class="group-control">
            <?= $form->field($model, 'note')->textarea([
              'rows' => 8,
            ]) ?>

            <?= $form->field($model, 'id')->textInput(['type' => 'hidden'])->label(false) ?>
          </div>
        </div>

        <div class="action-box">
          <button type="button" class="btn btn-default" data-dismiss="modal">Không</button>
          <?= Html::submitButton('Hủy', ['id' => 'btn-cancel-request-call-back', 'class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end() ?>
      </div>
    </div>
  </div>
</div>