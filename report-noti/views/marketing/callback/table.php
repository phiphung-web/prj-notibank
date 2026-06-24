<?php

use yii\widgets\LinkPager;

?>
<div class="d-flex" style="justify-content: space-between; margin-bottom: 20px">
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
        'linkOptions' => [
            'class' => 'page-link trip-pagination-item',
            'data-page' => function ($page) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page;

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>
<div class="table-scroll-mobile">
    <table id="datatables_w0" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
        <thead>
            <tr>
                <th>Số điện thoại</th>
                <th class="time">Thời gian</th>
                <th>Remote IP</th>
                <th>Utm source</th>
                <th>Utm medium</th>
                <th>Utm campaign</th>
                <th>URL</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($callbackList) && is_array($callbackList) && count($callbackList)) {
                foreach ($callbackList as $callbackList) {
                    ?>
                    <tr data-key="<?= $callbackList->id ?>" role="row">
                        <td>
                            <div class="text-bold">
                                <?= $callbackList->phone ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $html = '<div>Thời gian tạo: <span class="text-primary">' . date('d/m/Y H:i:s', strtotime($callbackList->created_on)) . '</span></div>';
                    echo $html; ?>
                        </td>
                        <td>
                            <?= isset($callbackList->remote_ip) ? $callbackList->remote_ip : '-' ?>
                        </td>
                        <td>
                            <?= isset($callbackList->utm_source) ? $callbackList->utm_source : '-' ?>
                        </td>
                        <td>
                            <?= isset($callbackList->utm_medium) ? $callbackList->utm_medium : '-' ?>
                        </td>
                        <td>
                            <?= isset($callbackList->utm_campaign) ? $callbackList->utm_campaign : '-' ?>
                        </td>
                        <td class="td-url">
                            <?= isset($callbackList->url) ? $callbackList->url : '-' ?>
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
</div>
<div class="d-flex" style="justify-content: space-between;">
    <div>
        <?php
        echo 'Showing ' . $startIndex . ' to ' . $endIndex . ' of ' . $totalCount . ' entries';
        ?>
    </div>
    <?=
    LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'options' => ['class' => 'pagination', 'style' => 'margin:0'],
        'linkOptions' => [
            'class' => 'page-link trip-pagination-item',
            'data-page' => function ($page) {
                $options = [];
                $options['tag'] = 'a';
                $options['data-page'] = $page;

                return $options;
            },
        ],
        'maxButtonCount' => 5,
    ]);
    ?>
</div>