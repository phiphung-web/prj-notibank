<?php
/* @var $debtType */
?>
<div class="box-body">
  <div class="trip-driver-search">
    <form class="row filter-trip-driver">
      <div class="col-lg-4">
        <label>Bộ lọc:</label>

        <select name="filter-time" class="js-filter_time form-control" aria-label="">
          <option value="pickup_time DESC">Thời gian chuyến xe chạy từ cao đến thấp</option>
          <option value="pickup_time ASC">Thời gian chuyến xe chạy từ thấp đến cao</option>
          <option value="created_on ASC">Thời gian tạo chuyến xe từ thấp đến cao</option>
          <option value="created_on DESC">Thời gian tạo chuyến xe từ cao đến thấp</option>
        </select>
      </div>

      <div class="col-lg-4 mt-mb-2">
        <label>Từ khóa:</label>
        <input type="text" id="searchbooking-keyword" class="form-control js-filter_keyword" name="SearchBooking[filter_keyword]" aria-invalid="false" aria-label="">

        <input class="debt-type" type="hidden" name="SearchBooking[debt_type]" value="<?= $debtType ?>" readonly aria-label="">
      </div>
    </form>
  </div>
</div>