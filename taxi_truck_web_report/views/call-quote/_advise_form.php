<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
?>

<div class="col-md-8 col-xs-12">
    <div class="box box-warning">
        <div class="box-header with-border d-flex align-items-center" style="display: flex">
            <h3 class="box-title">Tư vấn khách:
                <span class="js-phone-call"><?php echo (!empty($_GET['phone']) ? $_GET['phone'] : ''); ?></span>
            </h3>
            <?php if (isset($_GET['idCallBack'])) { ?>
                <div class="ml5 text-primary wrap-source"> <span style="margin: 0 5px">-</span>Nguồn:
                    <span class=""><?php echo (isset($_GET['source']) ? SOURCE_TRIP_TYPE_LIST[$_GET['source']] : ''); ?></span>
                    <input type="hidden" class="js-request-callback-id" value="<?php echo (isset($_GET['idCallBack']) ? $_GET['idCallBack'] : 0); ?>">
                </div>
            <?php } ?>
        </div>
        <div class="box-body">
            <div id="advise-call">
                <?php $form = ActiveForm::begin(); ?>
                <form id="form-get-detail-area">
                    <div class="d-flex justify-content-center align-items-stretch col-lg-12" style="padding-right: 12px;padding-left: 12px">
                        <div class="container">
                            <div class="position-relative address-autocomplete-container">
                                <div class="text-bold" style="width: 130px;">Điểm đi</div>
                                <?php echo Html::textInput('pickup_address', '', [
                                    'class' => 'form-control input-search-address pickup_address',
                                    'autocomplete' => 'off',
                                    'data-autocomplete' => 'address',
                                    'placeholder' => 'Nhập địa chỉ điểm đi...',
                                ]) ?>
                                <div class="address-suggestions" id="pickup-suggestions">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column justify-content-center reverse">
                            <div class="">
                                <button type="button" class="btn btn-box-tool btn-reverse"><i class="fa fa-retweet"></i></button>
                            </div>
                        </div>
                        <div class="container">
                            <div class="position-relative address-autocomplete-container">
                                <div class="text-bold" style="width: 130px;">Điểm đến</div>
                                <?php echo Html::textInput('destination_address', '', [
                                    'class' => 'form-control input-search-address destination_address',
                                    'autocomplete' => 'off',
                                    'data-autocomplete' => 'address',
                                    'placeholder' => 'Nhập địa chỉ điểm đến...',
                                ]) ?>
                                <div class="address-suggestions" id="destination-suggestions">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3" style="margin-top: 10px; margin-bottom: 10px">
                        <div class="text-bold" style="width: 130px;">Khoảng cách (km)</div>
                        <div>
                            <input class="distance-general form-control" style="background-color: #fff;">
                            <div id="error-message" class="text-danger"></div>
                        </div>
                    </div>
                    <div class="col-lg-3" style="margin-top: 10px; margin-bottom: 10px">
                        <div class="text-bold">Thời gian đi:</div>
                        <?php
                        $dateTime = date_create('', new DateTimeZone('Asia/Ho_Chi_Minh'));
                        ?>
                        <?= Html::textInput('pickup_time', $dateTime->format('Y-m-d H:i'), ['maxlength' => true, 'class' => 'form-control date-time-picker pickup-time']) ?>
                    </div>
                    <div class="col-lg-3" style="margin-top: 10px; margin-bottom: 10px">
                        <div class="text-bold" style="width: 130px;">Số giờ chờ</div>
                        <div>
                            <input class="wait-general int form-control" style="background-color: #fff;">
                        </div>
                    </div>

                    <div class="col-lg-3" style="margin-top: 10px; margin-bottom: 10px">
                        <div class="text-bold" style="width: 130px;">Phụ phí (VNĐ)</div>
                        <div>
                            <input class="surcharge int form-control" style="background-color: #fff;">
                        </div>
                    </div>
                    <div class="col-lg-3" style="margin-top: 10px; margin-bottom: 10px">
                        <div class="text-bold" style="width: 130px;">Số giờ lưu đêm</div>
                        <div class="d-flex flex-wrap">
                            <div class="app-border mr-10 mb-1">
                                <input type="checkbox" id="overnight-general-checkbox" class="option-input checkbox overnight-general-checkbox" name="overnight_general" value="1" />
                                <label for="overnight-general-checkbox" class="app-label" style="cursor: pointer;">
                                    Chọn nếu có lưu đêm
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
                <?php ActiveForm::end(); ?>
                <div class="col-lg-12" style="margin-top: 16px">
                    <div class="box box-success">
                        <div class="box-body">
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="wrap-table-detail-area">
                        <div class="table-advise">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
