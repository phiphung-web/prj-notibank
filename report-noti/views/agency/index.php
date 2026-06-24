<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

$this->title = 'Danh sách đại lý';
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
      <div class="group_zalo-search">

        <?php $form = ActiveForm::begin([
          'action' => ['index'],
          'method' => 'get',
        ]); ?>
        <div class="d-flex flex-column-mobile">
          <?= $form->field($searchModel, 'keyword', [
            'options' => [
              'class' => 'form-group',
              'style' => 'width: calc((100% - 60px) / 4); margin-right:20px',
            ],
          ])->textInput() ?>
        </div>
        <div class="form-group">
          <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
          <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
          <?= Html::a('Thêm mới', ['create'], ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
  <div class="form-group">
  </div>
  <div class="table-view-list">
    <?php
    $attributeLabels = (new app\models\GroupZalo())->attributeLabels();
    ?>
    <?php $agencyList = $dataProvider->getModels(); ?>

    <table id="table-pagination" class="table table-striped table-bordered" width="100%" style="background: #fff;">
      <thead>
        <th>Thông tin đại lý</th>
        <th>Địa chỉ</th>
        <th>Ghi chú</th>
        <th class="text-center" style="width: 150px">Hoa hồng</th>
        <th class="text-center" style="width: 150px">QR Code</th>
        <th style="width: 150px">Tổng số chuyến</th>
        <th class="text-center" style="width: 150px">Trạng thái</th>
        <th style="width: 100px"></th>
      </thead>
      <tbody>
        <?php
        if (isset($agencyList) && is_array($agencyList) && count($agencyList)) {
            foreach ($agencyList as $agencyItem) {
                ?>
            <tr data-key="<?= $agencyItem['id'] ?>" role="row">
              <td>
                <div>Đại lý: <span class="text-primary"><?= $agencyItem['name'] ?></span></div>
                <div>Email: <span class="text-primary"><?= $agencyItem['email'] ?></span></div>
                <div>SĐT: <span class="text-primary"><?= $agencyItem['phone'] ?></span></div>
                <div>Liên hệ: <span class="text-primary"><?= $agencyItem['contact_person'] ?></span></div>
              </td>
              <td>
                <?= $agencyItem['address'] ?>
              </td>
              <td>
                <?= $agencyItem['note'] ?>
              </td>
              <td class="text-center text-primary">
                <?php echo MyStringHelper::convertIntegerToPrice($agencyItem['price']) . 'VNĐ - ' . $agencyItem['percent'] . '%' ?>
              </td>
              <td class="text-center text-primary">
                <?php
                if (! empty($agencyItem['qr_code'])) {
                    echo '<a href="' . $agencyItem['qr_code'] . '" download>Tải QrCode</a>';
                } ?>
              </td>
              <td>
                <div>Thành công: <span class="text-success text-bold"><?= $agencyItem['total_success'] ?></span></div>
                <div>Tổng số: <span class="text-primary text-bold"><?= $agencyItem['total_trip'] ?></span></div>
              </td>
              <td class="text-center">
                <div><?php echo $agencyItem['status'] == 1 ? '<span class="text-success">Đã kích hoạt</span>' : '<span class="text-danger">Chưa kích hoạt</span>' ?></div>
                <div><?php echo $agencyItem['send_price'] == 1 ? '<span class="text-success">Gửi giá</span>' : '<span class="text-danger">Không gửi giá</span>' ?></div>
                <div><?php echo $agencyItem['agency_debt'] == 1 ? '<span class="text-success">Đại lý nợ</span>' : '<span class="text-danger">Tổng đài nợ</span>' ?></div>
              </td>
              <td class="text-center">
                <?= Html::a('<span class="btn-primary btn mb2 glyphicon glyphicon-pencil" aria-hidden="true"></span> ', ['update', 'id' => $agencyItem['id'], [
                  'title' => 'Chỉnh sửa',
                ]]); ?>
                <?= Html::a('<span class="btn-danger btn mb2 glyphicon glyphicon-trash"></span>', ['delete', 'id' => $agencyItem['id']], [
                  'title' => 'Xóa Đại lý',
                  'data-confirm' => Yii::t('yii', 'Xóa Đại lý này?'),
                  'data-method' => 'post',
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
    <?php $pagination = $dataProvider->getPagination(); ?>
    <div class="d-flex" style="justify-content: space-between;">
      <div>
        <?php
        $startIndex = $pagination->getPage() * $pagination->getPageSize() + 1;
        $endIndex = $startIndex + count($dataProvider->getModels()) - 1;
        $totalCount = $dataProvider->getTotalCount();
        echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
        ?>
      </div>
      <?=
      LinkPager::widget([
        'pagination' => $pagination,
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'options' => ['class' => 'pagination', 'style' => 'margin:0'],
        'linkOptions' => ['class' => 'page-link'],
        'maxButtonCount' => 5,
      ]);
      ?>
    </div>
  </div>
</div>
