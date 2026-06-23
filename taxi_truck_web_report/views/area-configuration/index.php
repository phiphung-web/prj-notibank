<?php
    use yii\helpers\Html;
    use yii\helpers\Url;

    $this->title = 'Cấu hình chung khu vực';
    $this->params['breadcrumbs'][] = $this->title;
?>

<div class="col-md-12">
    <div class="box box-primary">
        <div class="box-header with-border" style="display: flex;justify-content: space-between; align-items: center;">
            <div style="width: 100%"><h3 class="box-title">Cấu hình chung khu vực</h3></div>
            <div>
                <a href="#" class="btn btn-primary display-block btn-add-area-configuration" data-toggle="modal" data-target="#areaConfigurationModal">Thêm cấu hình khu vực</a>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <?php foreach (TYPE_AREA_CONFIGURATION as $key => $value) { ?>
                    <div class="col-lg-4">
                        <table id="datatables_w0" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
                            <thead>
                                <tr>
                                    <th colspan="3" class="text-center bg-info"><?php echo $value ?></th>
                                </tr>
                                <tr>
                                    <th>Loại cấu hình</th>
                                    <th>Giá trị</th>
                                    <th ></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if (isset($areaConfigurations) && is_array($areaConfigurations) && count($areaConfigurations)) {
                                        foreach ($areaConfigurations as $areaConfiguration) {
                                            if ($areaConfiguration->type == $key) {
                                                ?>
                                    <tr data-key="<?= $areaConfiguration->id ?>" role="row">
                                        <td><?= TYPE_AREA_CONFIGURATION[$areaConfiguration->type] ?></td>
                                        <td><?= $areaConfiguration->value ?></td>
                                        <td style="width:150px;">
                                            <div class="d-flex" style="justify-content: center; flex-wrap: wrap;">
                                                <a href="#" class="btn btn-info btn-update-area-configuration" data-toggle="modal" data-target="#areaConfigurationModal" data-id="<?= $areaConfiguration->id ?>" style="margin-right: 10px;"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                                <?php
                                                    echo Html::a('<i class="fa fa-trash" aria-hidden="true"></i>', Url::to(['/area-configuration/delete-area-configuration', 'id' => $areaConfiguration->id, 'type' => $areaConfiguration->type]), [
                                                        'title' => 'Xóa cấu hình',
                                                        'data-confirm' => Yii::t('yii', 'Xóa cấu hình này?'),
                                                        'data-method' => 'delete',
                                                        'class' => 'btn-action-list btn-danger btn ',
                                                    ])
                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                            }
                                        }
                                    } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="areaConfigurationModal" tabindex="-1" role="dialog" aria-labelledby="areaConfigurationModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="areaConfigurationModalLabel">Thêm mới cấu hình khu vực</h4>
            </div>
            <div class="modal-body" id="areaConfiguration-form-container">
                <form class="form-area-configuration">
                    <input type="hidden" class="form-control id-area-configuration" name="AreaConfiguration[id]" value="">
                    <div class="mb-10">
                        <label>Loại cấu hình</label>
                        <?= Html::dropDownList('AreaConfiguration[type]', '', TYPE_AREA_CONFIGURATION, ['prompt' => 'Chọn loại cấu hình', 'class' => 'form-control type-area-configuration']) ?>
                    </div>
                    <div class="mb-10">
                        <label>Giá trị</label>
                        <input type="text" class="form-control value-area-configuration" name="AreaConfiguration[value]" value="">
                    </div>
                    <div class="form-group uk-clearfix">
                        <div class="pull-right">
                            <button class="btn btn-primary btn-save-area-configuration" type="submit" data-action="">
                                Lưu
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<<JS
    $(document).ready(function () {
        $(document).on('click', '.btn-add-area-configuration', function(){
            $('#areaConfigurationModal').find('.btn-save-area-configuration').attr('data-action','create');
            $('.form-area-configuration')[0].reset();
        })

        $(document).on('click', '.btn-update-area-configuration', function(){
            $('.form-area-configuration')[0].reset();
            var modal = $('#areaConfigurationModal');
            var areaConfigurationId = $(this).data('id');
            $.ajax({
                url: '/area-configuration/load-area-configuration',
                type: 'GET',
                data: {id: areaConfigurationId},
                success: function (data) {
                    let json = JSON.parse(data)
                    $('.type-area-configuration').val(json.type)
                    $('.id-area-configuration').val(json.id)
                    $('.value-area-configuration').val(json.value)
                    modal.find('.btn-save-area-configuration').attr('data-action','update');
                }
            });

            return false;
        })

        $(document).on('click', '.btn-save-area-configuration', function(){
            let action = $(this).attr('data-action');
            var form = $('.form-area-configuration');
            var type = form.find('.type-area-configuration').val();
            var value = form.find('.value-area-configuration').val();
            if (!type || !value) {
                alert('Vui lòng điền đầy đủ thông tin');
                return false;
            }
            var formData = form.serialize();

            $.ajax({
                url: '/area-configuration/save-area-configuration',
                type: 'POST',
                data: formData,
                success: function (data) {
                    if (data) {
                        $('#areaConfigurationModal').modal('hide');
                        location.reload();
                    }
                }
            });
            return false;
        })

        $('.delete-areaConfiguration').on('click', function (e) {
            e.preventDefault();
            var areaConfigurationId = $(this).data('id');
            $.ajax({
                url: '/area-configuration/delete-area-configuration',
                type: 'POST',
                data: {id: areaConfigurationId},
                success: function (data) {
                    if (data) {
                        location.reload();
                    }
                }
            });
        });
    });
JS;
$this->registerJs($script);
?>
