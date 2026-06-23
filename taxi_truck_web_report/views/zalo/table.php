<?php

use app\helpers\MyStringHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

?>
<?php
$attributeLabels = (new app\models\GroupZalo())->attributeLabels();
?>
<?php $zaloList = $dataProvider->getModels(); ?>

<table id="table-pagination" class="table table-striped table-bordered" width="100%" style="background: #fff;">
    <thead>
        <th style="width: 250px">
            <?= $attributeLabels['name'] ?>
        </th>
        <th>
            <?= $attributeLabels['group_zalo_catalogue'] ?>
        </th>
        <th>
            <?= $attributeLabels['note'] ?>
        </th>
        <th style="width: 150px"></th>
    </thead>
    <tbody>
        <?php
        if (isset($zaloList) && is_array($zaloList) && count($zaloList)) {
            foreach ($zaloList as $zaloItem) {
                ?>
                <tr data-key="<?= $zaloItem->id ?>" role="row">
                    <td>
                        <?= $zaloItem->name ?>
                    </td>
                    <td>
                        <?= $zaloItem->group_zalo_catalogue_name ?>
                    </td>
                    <td>
                        <?= $zaloItem->note ?>
                    </td>
                    <td class="d-flex justify-content-evenly">
                        <?= Html::a(
                    '<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> ',
                    ['zalo/update', 'id' => $zaloItem->id],
                    [
                                'title' => 'Chỉnh sửa',
                                'class' => 'btn-primary btn mb2',
                            ]
                ); ?>
                        <?= Html::a(
                            '<span class="glyphicon glyphicon-trash"></span>',
                            ['zalo/delete', 'id' => $zaloItem->id],
                            [
                                'title' => 'Xóa nguồn bán ',
                                'data-confirm' => Yii::t('yii', 'Xóa nguồn này?'),
                                'data-method' => 'post',
                                'class' => 'btn-danger btn mb2',
                            ]
                ); ?>
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
