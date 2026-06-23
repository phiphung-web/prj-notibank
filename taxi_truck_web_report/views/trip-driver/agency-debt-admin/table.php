<?php
use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>

<table id="datatables_w0" class="table table-striped table-bordered" width="100%" cellspacing="0"
    style="background: #fff;">
    <thead>
        <tr>
            <th>
                Đại lý
            </th>
            <th>
                Thông tin liên lạc
            </th>
            <th class="text-center" style="width: 200px">
                Người liên hệ
            </th>
            <th>
                Ghi chú
            </th>
            <th class="text-center" style="width: 150px">
                Trạng thái
            </th>
            <th class="text-center" style="width: 150px">Hoa hồng</th>
            <th class="text-center">
                Tiền hoa hồng
            </th>
            <th class="text-center">
                Tiền đại lý nợ
            </th>
            <th class="text-center">
            </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $agencyTotalPriceRose = 0;
        $agencyTotalPrice = 0;
        if (!empty($agencyList) && is_array($agencyList) && count($agencyList)) {
            foreach ($agencyList as $agency) {
                $agencyId = $agency['id'];
                $agencyTotalPriceRose += $agency['total_price_rose'];
                $agencyTotalPrice += $agency['total_price']; ?>
                <tr data-key="<?= $agencyId ?>" role="row" class="tr-agency-debt">
                    <td>
                        <?= $agency['name'] ?>
                    </td>
                    <td>
                        <div>SĐT: <?= $agency['phone'] ?></div>
                        <div>Email: <?= $agency['email'] ?></div>
                        <div>Địa chỉ: <?= $agency['address'] ?></div>
                    </td>
                    <td class="text-center">
                        <?= $agency['contact_person'] ?>
                    </td>
                    <td>
                        <?= $agency['note'] ?>
                    </td>
                    <td class="text-center">
                        <?= $agency['status'] == 1 ? 'Đã kích hoạt' : 'Chưa kích hoạt' ?>
                    </td>
                    <td class="text-center text-primary">
                        <?php echo MyStringHelper::convertIntegerToPrice($agency['price']) . 'VNĐ - ' . $agency['percent'] . '%' ?>
                    </td>
                    <td class="text-center price-rose">
                        <?= MyStringHelper::convertIntegerToPrice($agency['total_price_rose']) ?>đ
                    </td>
                    <td class="text-center price-total">
                        <?= MyStringHelper::convertIntegerToPrice($agency['total_price']) ?>đ
                    </td>
                    <td class="text-center">
                        <div class="d-flex" style="justify-content: center; flex-wrap: wrap;">
                            <button type="button" class="js-btn-trip-agency btn-primary btn mb2" title="Thu tiền"
                                style="margin-right: 10px" data-id="<?= $agencyId ?>"><span>Thu tiền</span></button>
                            <?php
                            echo Html::button('Chi tiết', [
                                'title' => 'Chi tiết',
                                'class' => 'js-btn-detail btn-success btn mb2',
                                'data-target' => '#modalDetail',
                                'data-toggle' => 'modal',
                                'data-id' => $agencyId,
                            ])
                                ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
        } else { ?>
            <tr>
                <td colspan="100%"><span class="text-danger">Không có dữ liệu phù hợp...</span></td>
            </tr>
        <?php } ?>
        <tr>
            <td class="fw-bold"> Tổng số
            </td>
            <td>
            </td>
            <td>
            </td>
            <td>
            </td>
            <td>
            </td>
            <td>
            </td>
            <td class="text-center price-rose fw-bold">
                <?= MyStringHelper::convertIntegerToPrice($agencyTotalPriceRose) ?>đ
            </td>
            <td class="text-center price-total fw-bold">
                <?= MyStringHelper::convertIntegerToPrice($agencyTotalPrice) ?>đ
            </td>
            <td>
            </td>
        </tr>
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
<?php echo $this->render('modal'); ?>
