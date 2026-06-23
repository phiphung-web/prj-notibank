<?php

return [
    // AdminController
    'admin_cud' => '{action} tài khoản có ID là "{id}" và Username là "{username}"',
    'change_pass' => 'Đổi mật khẩu của ID là "{id}" và Username là "{username}"',
    'login' => 'Người dùng đăng nhập',
    'logout' => 'Người dùng đăng xuất',
    // AgencyController
    'agency_cud' => '{action} đại lý có ID là "{id}" tên là "{name}"',
    // AreaConfigurationController
    'area_configuration_action' => '{action} cấu hình khu vực chung có ID là "{id}" loại "{type}" có giá trị là "{value}"',
    // AreaController
    'area_cd' => '{action} khu vực "{area_name}" có ID là "{id}"',
    'area_update' => 'Cập nhật thông tin khu vực "{area_name}" có ID là "{id}"',
    // DriverController
    'driver_cuda' => '{action} tài xế có ID: "{id}" - Tên: "{name}" - SDT: "{phone}"',
    'driver_update_status' => 'Khóa tài xế có ID: "{id}" - Tên: "{name}" - SDT: "{phone}"',
    'driver_register' => 'Đăng ký tài xế có ID: "{id}" - Tên: "{name}" ',
    'driver_action_driver_sub' => '{action} tài xế nhiều xe cho ID: "{id}" - Tên: "{name}" ',
    // MessageController
    'message_cd' => '{action} thông báo (ID: {id}): {title}',
    // PayController
    'pay_create' => 'Nạp {money} đ cho tài xế ID: "{id}"',
    // RoleController
    'driver_role_cud' => '{action} quyền "{name}" cho tài xế',
    // StatisticController
    'booking_create' => '{action} lịch đặt xe có ID là "{id}" đi từ "{pickup_address}" đến "{destination_address}" vào lúc "{pickup_time}"',
    'booking_update_status' => '{action} lịch đặt xe có ID là "{id}" trạng thái "{status}"',
    'booking_cancel' => '{action} lịch đặt xe có ID là "{id}"',
    // SystemConfigurationController
    'system_configuration_update' => 'Cập nhật cấu hình hệ thống.',
    // TripController
    'trip_create' => 'Tạo chuyến xe ({sold}) có ID là "{id}", SDT khách: "{phone}"',
    'trip_action' => '{action} chuyến xe có ID là "{id}", SDT khách: "{phone}"',
    'trip_transfer' => 'Chuyển nhóm Zalo bán cho chuyến xe có ID là "{id}", SDT khách: "{phone}"',
    'trip_copy' => 'Copy chuyến xe có ID là "{id}", SDT khách: "{phone}"',
    'trip_add_manual' => 'Điều xe cho chuyến xe có ID là "{id}", SDT khách: "{phone}"',
    'trip_putback_return' => 'Trả lịch {type} cho chuyến xe có ID là "{id}", SDT khách: "{phone}"',
    'trip_update_display_money' => '{display_money} chuyến xe có ID là "{id}", SDT khách: "{phone}"',
    'trip_update_debt_driver' => '{debt_driver} tài xế chuyến xe có ID là "{id}"',
    'trip_update_debt_agency' => '{debt_agency} đại lý {agency} thành công với số tiền là "{price}đ"',
    'trip_update_pass_booking' => 'Tổng đài đã thanh toán cho lái xe {driver} thành công với số tiền là "{price}đ". Tổng số tiền của lái xe là {total_price}đ',
    // ZaloCatalogueController
    'zalo_catalogue_cud' => '{action} nhóm zalo "{name}"',
    // ZaloController
    'zalo_cud' => '{action} zalo "{name}"',
    // ZaloSellerController
    'zalo_seller_cud' => '{action} người bán zalo "{name}"',
    // CustomerService
    'customer_service_action' => 'Cập nhật phản hồi cho chuyến xe có ID là "{id}"',
    // FormulaController
    'formula_update' => 'Cập nhật thông tin bảng giá',
    'formula_updated_successfully' => 'Cập nhật bảng giá thành công',
    'increase_price_update' => 'Cập nhật thông tin tăng giá tự động',
    'increase_price_updated_successfully' => 'Cập nhật tăng giá tự động thành công',
    'config_auto_sale_update' => 'Cập nhật cấu hình bán tự động',
    'config_auto_sale_updated_successfully' => 'Cập nhật cấu hình bán tự động thành công',
    // Calculation Formula Messages
    'system_error' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.',
    'no_data_provided' => 'Không có dữ liệu được cung cấp.',
    'invalid_data_provided' => 'Dữ liệu cung cấp không hợp lệ.',
    'invalid_type_of_car_data' => 'Dữ liệu loại xe không hợp lệ.',
    'invalid_numeric_value' => 'Giá trị số không hợp lệ cho trường "{field}".',
    'invalid_time_format' => 'Định dạng thời gian không hợp lệ cho trường "{field}".',
    'required_field' => 'Trường "{field}" là bắt buộc.',
    'km_start_must_be_greater_than_or_equal_to_zero' => 'Số km bắt đầu phải lớn hơn hoặc bằng 0.',
    'km_end_must_be_greater_than_or_equal_to_zero' => 'Số km kết thúc phải lớn hơn hoặc bằng 0.',
    'km_end_must_be_greater_than_km_start' => 'Số km kết thúc phải lớn hơn số km bắt đầu.',
    'price_must_be_greater_than_or_equal_to_zero' => 'Giá phải lớn hơn hoặc bằng 0.',
    'price_value_too_large' => 'Giá trị giá quá lớn.',
    'no_parameters_provided' => 'Không có tham số được cung cấp.',
    'no_calculation_formula_found' => 'Không tìm thấy công thức tính giá phù hợp.',
    'invalid_parameters_provided' => 'Tham số cung cấp không hợp lệ.',
    'required_field_missing' => 'Thiếu trường bắt buộc: "{field}".',
    'invalid_distance_value' => 'Giá trị khoảng cách không hợp lệ.',
    'invalid_type_of_car' => 'Loại xe không hợp lệ.',
    'schedule_required' => 'Lịch trình là bắt buộc.',
    'invalid_time_values' => 'Giá trị thời gian không hợp lệ.',
    'invalid_trip_parameters' => 'Tham số chuyến đi không hợp lệ.',
    'required_trip_field_missing' => 'Thiếu trường bắt buộc cho chuyến đi: "{field}".',
    // Api
    'address_start' => 'Khách hàng đại lý đã tìm kiếm điểm đi địa điểm bắt đầu với từ khoá là: {keyword}',
    'address_end' => 'Khách hàng đại lý đã tìm kiếm điểm đi địa điểm kết thúc với từ khoá là: {keyword}',
    'api_find_price_1' => 'Khách hàng đại lý đã tìm kiếm giá: <br> - Điểm đi: {address_start} <br> - Điểm đến: {address_end} <br> - Lịch trình: {schedule} <br> - Thời gian đi: {pickup_time} <br> - Loại xe: {type_of_car}',
    'api_find_price_2' => 'Khách hàng đại lý đã tìm kiếm giá: <br> - Điểm đi: {address_start} <br> - Điểm đến: {address_end} <br> - Lịch trình: {schedule} <br> - Thời gian đi: {pickup_time}',
];
