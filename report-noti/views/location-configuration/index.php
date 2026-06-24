<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Cấu hình vị trí';
?>
<style>
    .w-50{
        width: 50%;
    }
    #overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .location-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        justify-content: center;
        align-items: center;
        width: 30%;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        z-index: 1;
    }

    .popup-content {
        background: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 15px;   
    }

    .changed-row {
        font-weight: bold;
    }
</style>
<div class="box box-green">
    <div class="box-header with-border">
        <h3 class="box-title">Filter</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>
    <div class="box-body">
      <?php echo $this->render('_search', [
        'model' => $searchModel,
      ]) ?>
    </div>
</div>
<div class="box-header with-border " style="display: flex; align-items:center; justify-content: space-between">
    <h3 class="box-title" style="width: 100%">Danh sách vị trí</span></h3>
    <div class="d-flex">
        <button class="btn btn-success" style="margin-right: 16px" id="btnOpenPopup">Thêm mới</button>
        <button class="btn btn-primary" id="btnUpdate">Cập nhật</button>
    </div>
</div>
<div class="location-configuration-form row">
  <?php $form = ActiveForm::begin(); ?>
    <div class="col-md-12">
      <div class="box box-success">
        <div class="box-body">
          <table id="tablelocation" class="table table-striped table-bordered " width="100%" cellspacing="0" style="background: #fff;">
            <thead>
            <tr>
                <th>STT</th>
                <th>Vĩ độ (latitude)</th>
                <th>Kinh độ (longitude)</th>
                <th class="w-50">Địa điểm</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (isset($dataProvider) && is_array($dataProvider) && count($dataProvider)) {
                $i = 1;
                foreach ($dataProvider as $key => $value) {
                    ?>
                <tr role="row" data-id="<?php echo $value['id']; ?>">
                  <td><?php echo $i;
                    $i++; ?></td>
                  <td><?php echo Html::textInput('latitude', $value['latitude'], ['class' => 'form-control change-input']); ?></td>
                  <td><?php echo Html::textInput('longitude', $value['longitude'], ['class' => 'form-control change-input']); ?></td>
                  <td><?php echo Html::textInput('display_name', $value['display_name'], ['class' => 'form-control change-input']); ?></td>
                  <td><button class="btn btn-danger btn-delete-location"><i class="fa fa-trash-o" aria-hidden="true"></i></button>      
                  </td>
                </tr>
              <?php
                }
            } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php ActiveForm::end(); ?>
    <div id="overlay"></div>
    <div id="locationPopup" class="location-popup">
        <div class="popup-content">
            <h2>Thêm mới vị trí</h2>
            <div class="form-group">
                <label for="latitude">Vĩ độ (Latitude):</label>
                <input type="text" id="latitude" class="form-control">
            </div>

            <div class="form-group">
                <label for="longitude">Kinh độ (Longitude):</label>
                <input type="text" id="longitude" class="form-control">
            </div>

            <div class="form-group">
                <label for="display_name">Tên hiển thị:</label>
                <input type="text" id="display_name" class="form-control">
            </div>

            <div class="button-group">
                <button class="btn btn-save-location btn-primary" id="btnSaveLocation">Lưu</button>
                <button class="btn btn-back" id="btnBack">Quay lại</button>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<< JS
    $("#btnOpenPopup").click(function () {
        $("#overlay, #locationPopup").fadeIn();
    });

    $("#btnBack").click(function () {
        $("#overlay, #locationPopup").fadeOut();
    });
    
    $("#btnSaveLocation").click(function () {
        let latitude = $("#latitude").val();
        let longitude = $("#longitude").val();
        let displayName = $("#display_name").val();

        $.ajax({
            url: '/location-configuration/create',
            type: 'POST',
            data: { latitude: latitude, longitude: longitude, display_name: displayName },
            success: function (response) {
                let data = JSON.parse(response);
                if (data.status === 'success') {
                    alert(data.message);
                } else {
                    alert(data.message);
                }
                location.reload();
            },
            error: function (error) {
                alert(error.responseText);
            }
        });

        $("#overlay, #locationPopup").fadeOut();
    });
    
    $(".btn-delete-location").click(function () {
    let id = $(this).closest('tr').data('id');
    let confirmDelete = confirm("Bạn có chắc muốn xóa không?");
    if (!confirmDelete) {
        return;
    }
        $.ajax({
            url: '/location-configuration/delete',
            type: 'POST',
            data: {id: id,  _method: 'DELETE'},
            success: function (response) {
            let data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
            } else {
                alert(data.message);
            }
            },
            error: function (error) {
                alert("Xóa thất bại");
            }
        });
    });
    $("#btnUpdate").click(function () {
    let rows = $(".change-input").closest('tr');
    let dataToUpdate = [];

    rows.each(function () {
        let row = $(this);
        let id = row.data("id");
        let latitude = row.find('[name="latitude"]').val();
        let longitude = row.find('[name="longitude"]').val();
        let display_name = row.find('[name="display_name"]').val();

        dataToUpdate.push({
            id: id,
            latitude: latitude,
            longitude: longitude,
            display_name: display_name,
        });
    });
    
    $.ajax({
        url: '/location-configuration/update',
        type: 'post',
        data: {data: dataToUpdate},
        success: function (response) {
            let data = JSON.parse(response);
            if (data.status === 'success') {
                alert(data.message);
            } else {
                alert("Cập nhật thất bại");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            alert("Cập nhật thất bại");
        }
    });

    return false;
});
    $(".change-input").change(function () {
    let row = $(this).closest("tr");
    row.addClass("changed-row");
    });

    $(".change-input").keydown(function () {
        let row = $(this).closest("tr");
        row.removeClass("changed-row");
    });
JS;
$this->registerJs($script);
?>