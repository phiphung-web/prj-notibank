<?php

// Chức năng chăm sóc khách hàng
define('STATUS_CUSTOMER_SERVICE_LIST', [
    STATUS_CUSTOMER_SERVICE_NO_PROCESS => 'Chưa xử lý',
    STATUS_CUSTOMER_SERVICE_NO_CHECK => 'Không xử lý',
    STATUS_CUSTOMER_SERVICE_SUCCESS => 'Đã xử lý',
    STATUS_CUSTOMER_SERVICE_ERROR => 'Xảy ra vấn đề',
    STATUS_CUSTOMER_SERVICE_MEMBER => 'Khách thường xuyên đi',
    STATUS_CUSTOMER_SERVICE_NO_CALL => 'Khách không nghe máy',
    STATUS_CUSTOMER_SERVICE_2_WAY_BOOKING => 'Khách đã book 2 chiều',
    STATUS_CUSTOMER_SERVICE_ZALO => 'Chăm sóc qua Zalo',
    STATUS_CUSTOMER_SERVICE_BOOK => 'Khách đặt hộ',
    STATUS_CUSTOMER_SERVICE_BOOK_AGAIN => 'Khách book lại',
    STATUS_CUSTOMER_SERVICE_FOREIGNER => 'Khách nước ngoài',
    STATUS_CUSTOMER_SERVICE_CANCEL => 'Khách hủy',
]);

// Danh sách loại CSKH
define('TYPE_CUSTOMER_SERVICE_LIST', [
    1 => 'Hài lòng với dịch vụ',
    2 => 'Không hài lòng với lái xe',
    3 => 'Góp ý về dịch vụ',
    4 => 'Phản hồi về thời gian chờ đợi',
    5 => 'Phản hồi về tương tác',
]);

// Danh sách loại xe
define('TYPE_OF_CAR_LIST', [
    CAR_TYPE_500KG => 'Xe 0.5 tấn',
    CAR_TYPE_750KG => 'Xe 0.75 tấn',
    CAR_TYPE_1000KG => 'Xe 1 tấn',
    CAR_TYPE_1250KG => 'Xe 1.25 tấn',
    CAR_TYPE_1500KG => 'Xe 1.5 tấn',
    CAR_TYPE_1900KG => 'Xe 1.9 tấn',
    CAR_TYPE_2500KG => 'Xe 2.5 tấn',
    CAR_TYPE_3500KG => 'Xe 3.5 tấn',
    CAR_TYPE_5000KG => 'Xe 5 tấn',
    CAR_TYPE_7000KG => 'Xe 7 tấn',
    CAR_TYPE_10000KG => 'Xe 10 tấn',
    CAR_TYPE_OVER_10000KG => 'Xe trên 10 tấn',
    CAR_TYPE_VAN_500KG => 'Xe Van 0.5 tấn',
    CAR_TYPE_VAN_750KG => 'Xe Van 0.75 tấn',
    CAR_TYPE_VAN_1000KG => 'Xe Van 1 tấn',
]);

define('TYPE_OF_CAR_DESCRIPTION_LIST', [
    CAR_TYPE_500KG => [
        'length' => '1,8m',
        'width' => '1,2m',
        'height' => '1,2m',
    ],
    CAR_TYPE_750KG => [
        'length' => '1,5m',
        'width' => '1,4m',
        'height' => '1,4m',
    ],
    CAR_TYPE_1000KG => [
        'length' => '3m',
        'width' => '1,4m',
        'height' => '1,3m',
    ],
    CAR_TYPE_1250KG => [
        'length' => '3,1m',
        'width' => '1,6m',
        'height' => '1,6m',
    ],
    CAR_TYPE_1500KG => [
        'length' => '3,2m',
        'width' => '1,6m',
        'height' => '1,7m',
    ],
    CAR_TYPE_1900KG => [
        'length' => '3,5m',
        'width' => '1,65m',
        'height' => '1,7m',
    ],
    CAR_TYPE_2500KG => [
        'length' => '3,5m',
        'width' => '1,65m',
        'height' => '1,8m',
    ],
    CAR_TYPE_3500KG => [
        'length' => '4,2m',
        'width' => '1,75m',
        'height' => '1,75m',
    ],
    CAR_TYPE_VAN_500KG => [
        'length' => '1.7m',
        'width'  => '1.3m',
        'height' => '1.2m',
    ],
    CAR_TYPE_VAN_750KG => [
        'length' => '2.1m',
        'width'  => '1.4m',
        'height' => '1.4m',
    ],
    CAR_TYPE_VAN_1000KG => [
        'length' => '2.8m',
        'width'  => '1.5m',
        'height' => '1.6m',
    ],
]);

// Danh sách nguồn nhận lịch
define('SOURCE_TRIP_TYPE_LIST', [
    // SOURCE_TRIP_TYPE_MAIL_1 => 'Mail',
    SOURCE_TRIP_TYPE_CALL_1 => 'DĐ Thu An',
    // SOURCE_TRIP_TYPE_MAIL_2 => 'Web 2 - Mail',
    // SOURCE_TRIP_TYPE_CALL_2 => 'Web 2 - Call',
    SOURCE_TRIP_TYPE_FB => 'FB Thu An',
    // SOURCE_TRIP_TYPE_FB_1 => 'Facebook 1',
    SOURCE_TRIP_TYPE_ZALO => 'Zalo cá nhân',
    // SOURCE_TRIP_TYPE_ZALO_1 => 'Zalo Năm',
    SOURCE_TRIP_TYPE_ZALO_CSKH => 'Zalo Thu An',
    // SOURCE_TRIP_TYPE_ZALO_MR => 'Zalo Mr',
    // SOURCE_TRIP_TYPE_ZALO_XEVIP => 'Zalo Xevip',
    // SOURCE_TRIP_TYPE_ZALO_KETOAN => 'Zalo kế toán',
    SOURCE_TRIP_TYPE_CALL_DIRECTLY => 'Call hotline',
    SOURCE_TRIP_TYPE_DRIVER => 'Lái xe',
    SOURCE_TRIP_TYPE_AGENCY => 'Đại lý',
    // SOURCE_TRIP_TYPE_TIKTOK => 'Tiktok',
    // SOURCE_TRIP_TYPE_COGOVI => 'Cogovi'
]);

// Danh sách nguồn nhận lịch
define('SOURCE_MAIL_LIST', [
    SOURCE_KEY_FACEBOOK => 'Facebook',
    SOURCE_KEY_TIKTOK => 'Tiktok',
    SOURCE_KEY_ZALO => 'Zalo',
    SOURCE_KEY_GOOGLE => 'Google',
    SOURCE_KEY_ORGANIC => 'Organic',
    SOURCE_KEY_EMPLOYEE => 'Nhân viên',
]);

// Danh sách trạng thái booking
define('STATUS_BOOKING', [
    STATUS_BOOKING_CREATE => 'Chưa xử lý',
    STATUS_BOOKING_CONFIRM => 'Đã xác nhận',
    STATUS_BOOKING_REJECT => 'Đã từ chối',
    STATUS_BOOKING_WAITING => 'Đang chờ',
]);

define('SCHEDULE_LIST_TRIP', [
    ROUND_TRIP_INNER => 'Nội thành',
    ROUND_TRIP_PROVINCE => 'Liên tỉnh',
]);

// Lịch trình gọi điện
define('SCHEDULE_LIST', [
    0 => 'Chiều đi',
    1 => 'Chiều về',
]);

// Danh sách trạng thái tài xế
define('STATUS_DRIVER_BAN_LIST', [
    STATUS_DRIVER_NORMAL => 'Tài xế bình thường',
    STATUS_DRIVER_BAN => 'Tài xế có nhiều xe',
    STATUS_DRIVER_BAN_WAIT_REVIEW => 'Chờ xét duyệt tài xế nhiều xe',
]);

// Danh sách nguồn nhận lịch
define('CUSTOMER_PROPERTY_LIST', [
    CUSTOMER_PROPERTY_NEW => 'Khách mới',
    CUSTOMER_PROPERTY_OLD => 'Khách cũ',
    CUSTOMER_PROPERTY_RETURN => 'Khách CSKH',
    CUSTOMER_PROPERTY_RETURN_CSKH => 'Khách doanh nghiệp',
    CUSTOMER_PROPERTY_AGENCY => 'Khách đại lý',
]);

// Lịch trình gọi điện
define('VOUCHER_TYPE_LIST', [
    VOUCHER_VND_TYPE => 'Chiều đi',
    VOUCHER_PERCENT_TYPE => 'Chiều về',
]);

// Số lần chăm sóc
define('TIMES_LIST', [0 => '0 lần', 1 => '1 lần', 2 => '2 lần', 3 => 'Hoàn thành']);

// Phương thức thanh toán cho lái xe khi bán lịch
define('BOOKING_PAYMENT_METHOD_LIST', [
    BOOKING_PAYMENT_METHOD_BID => 'Nạp bid',
    BOOKING_PAYMENT_METHOD_BANK => 'Chuyển khoản',
]);

// Danh sách phân loại tài khoản
define('BANK_OF_LIST', [
    BANK_OF_DUNG => 'Dũng',
    BANK_OF_HUY => 'Huy',
]);

// Danh sách loại biển kiểm soát
define('LICENSE_TYPE_LIST', [
    LICENSE_TYPE_YELLOW => 'Biển vàng',
    LICENSE_TYPE_WHITE => 'Biển trắng',
]);

// Danh sách loại xe
define('CAR_TYPE_LIST', [
    CAR_TYPE_GASOLINE => 'Xe xăng',
    CAR_TYPE_ELECTRIC => 'Xe điện',
]);

// Danh sách loại nạp tiền
define('NOTIFY_TYPE_LIST', [
    NOTIFY_TRIP_STARTING => 'Sắp đến giờ đi của chuyến',
    NOTIFY_MESSAGE_DRIVER => 'Thông báo mới cho lái xe',
    NOTIFY_PAY_TRANSACTION_DRIVER => 'Thông báo nạp tiền của lái xe',
]);

// Danh sách dịch vụ
define('SERVICE_LIST', [
    SERVICE_PACKAGE => 'Trọn gói',
    SERVICE_LOADING => 'Xe + bốc xếp',
    SERVICE_TRUCK => 'Xe tải',
    SERVICE_MERGE => 'Xe ghép',
]);
