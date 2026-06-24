<?php

/**
 * App Constants - Standardized Naming Convention: UPPER_SNAKE_CASE
 * Grouped by feature/module, with clear section comments.
 */

// ==================== ZNS Config ====================
const APP_ID_ZNS = '3087230659670011144';

const ZNS_SECRET_KEY = 'NV2iw66L6I6JlN3EWCZ5';
const URL_SEND_ZNS = 'https://business.openapi.zalo.me/message/template';
const URL_REFRESH_TOKEN_ZNS = 'https://oauth.zaloapp.com/v4/oa/access_token';
// ==================== Promotion =====================
const PROMOTION_PERCENT = '10%';
const PRICE_MAX = 1000000000;
// ==================== Zalo Types ====================
const ZALO_TYPE_POINT = 0;
const ZALO_TYPE_DRIVER = 1;
const ZALO_TYPE_POINT_DRIVER = 2;
// ==================== Trip Status ===================
const STATUS_TRIP_ALL = 'ALL';
const STATUS_TRIP_OPEN = 'OPEN';
const STATUS_TRIP_CREATE = 'CREATE';
const STATUS_TRIP_DONE = 'DONE';
const STATUS_TRIP_EXPIRE = 'EXPIRE';
const STATUS_TRIP_COMPLETE = 'COMPLETE';
const STATUS_TRIP_CANCEL = 'CANCEL';
const STATUS_TRIP_PENDING = 'PENDING';

const STATUS_TRIP = [
    STATUS_TRIP_OPEN => 'Đang bán',
    STATUS_TRIP_EXPIRE => 'Hết hạn',
    STATUS_TRIP_CREATE => 'Đang hẹn giờ mở bán',
    STATUS_TRIP_DONE => 'Đã điều',
    STATUS_TRIP_CANCEL => 'Đã hủy',
    STATUS_TRIP_COMPLETE => 'Đã hoàn thành',
];

// ==================== Booking Status =================
const STATUS_BOOKING_CREATE = 'CREATE';
const STATUS_BOOKING_WAITING = 'WAITING';
const STATUS_BOOKING_CONFIRM = 'CONFIRM';
const STATUS_BOOKING_REJECT = 'REJECT';
// ==================== Bid Status ====================
const STATUS_BID_SUCCESS = 'SUCCESS';
const STATUS_BID_INVALID = 'INVALID';
const STATUS_BID_PENDING = 'PENDING';
const STATUS_BID_REFUND = 'REFUND';
const STATUS_BID_PUTBACK = 'PUTBACK';
// ==================== Area Config ===================
const TIME_AREA_CONFIGURATION = 'time';
const SCHEDULE_AREA_CONFIGURATION = 'schedule';

const TYPE_AREA_CONFIGURATION = [
    TIME_AREA_CONFIGURATION => 'Khung giờ',
    SCHEDULE_AREA_CONFIGURATION => 'Lịch trình',
];

// =============== Pay Transaction Status =============
const PAY_REPORT = 0;
const PAY_SMS = 1;

const PAY_TYPE = [
    PAY_REPORT => 'Web Report',
    PAY_SMS => 'SMS Banking',
];

const CHOOSE_REASON = 'Chọn lý do';
const ADD_TYPE_REJECT = 'Lý do khác';
const STATUS_PAY_TRANSACTION_SMS_SUCCESS = 1;
const STATUS_PAY_TRANSACTION_SMS_FAILED = 0;

const STATUS_PAY_TRANSACTION_SMS = [
    STATUS_PAY_TRANSACTION_SMS_FAILED => 'Thất bại',
    STATUS_PAY_TRANSACTION_SMS_SUCCESS => 'Thành công',
];

// ==================== Bank List =====================
const TPBANK = 1;
const BIDV_BANK = 2;
const MB_BANK = 3;
const VP_BANK = 4;
const MB_ONLINE_OTP_BANK = 8;

const BANK_LIST = [
    TPBANK => 'TP Bank',
    BIDV_BANK => 'BIDV',
    MB_BANK => 'MB Bank',
    VP_BANK => 'VP Bank',
    MB_ONLINE_OTP_BANK => 'MB Online OTP',
];

// ==================== Role ==========================
const DAI_LY_ROLE = 'DAI_LY_ROLE';
// ============= Call Back Request Status =============
const REQUEST_CALL_BACK_ALL = -1;
const REQUEST_CALL_BACK_WAITING = 0;
const REQUEST_CALL_BACK_CONFIRM = 1;
const REQUEST_CALL_BACK_CANCEL = 2;

const STATUS_REQUEST_CALL_BACK = [
    REQUEST_CALL_BACK_WAITING => 'Chờ xử lí',
    REQUEST_CALL_BACK_ALL => 'Tất cả',
    REQUEST_CALL_BACK_CONFIRM => 'Đã Xác nhận',
    REQUEST_CALL_BACK_CANCEL => 'Hủy bỏ',
];

// ==================== Customer Gender ===============
const GENDER = [
    0 => 'Nam',
    1 => 'Nữ',
];

// ==================== Log Actions ===================
const ACTION_LIST = [
    'login' => 'Đăng nhập',
    'logout' => 'Đăng xuất',
    'read' => 'Xem',
    'create' => 'Tạo mới',
    'update' => 'Sửa',
    'delete' => 'Xóa',
    'cancel' => 'Hủy bỏ',
    'accept' => 'Duyệt',
    'search' => 'Tìm kiếm',
    'price' => 'Tìm giá',
];

// ==================== Debt Types ====================
const DEBT_SWITCHBOARD = 1;
const DEBT_DRIVER = 2;
const DEBT_CUSTOMERS = 3;
// ==================== Trip Status List ==============
const TRIP_STATUS_CREATE = 'CREATE';
const TRIP_STATUS_WAITING = 'WAITING';
const TRIP_STATUS_REJECT = 'REJECT';
const TRIP_STATUS_CONFIRM = 'CONFIRM';

const TRIP_STATUS_LIST = [
    TRIP_STATUS_CREATE => 'Chưa xử lý',
    TRIP_STATUS_WAITING => 'Lịch chờ',
    TRIP_STATUS_REJECT => 'Hủy lịch đặt',
];

const CALL_DRIVER_NOT_CONFIRMED = 0;
const CALL_DRIVER_CONFIRMED = 1;

const TRIP_STATUS_LIST_FULL = [
    TRIP_STATUS_CONFIRM => 'Xác nhận',
    TRIP_STATUS_CREATE => 'Chưa xử lý',
    TRIP_STATUS_WAITING => 'Lịch chờ',
    TRIP_STATUS_REJECT => 'Hủy lịch đặt',
];

// ==================== Agency/Module =================
const ADMIN_DEBT_AGENCY = 0;
const AGENCY_DEBT_ADMIN = 1;
const AGENCY_STATUS_ACTIVE = 1;
const AGENCY_STATUS_NOT_ACTIVE = 0;

const AGENCY_STATUS = [
    AGENCY_STATUS_ACTIVE => 'Đã kích hoạt',
    AGENCY_STATUS_NOT_ACTIVE => 'Chưa kích hoạt',
];

const TYPE_RANK_TOTAL_TRIP = 0;
const TYPE_RANK_TOTAL_MONEY = 1;
const MODULE_ADMIN = 0;
const MODULE_CUSTOMER = 1;
const MODULE_DRIVER = 2;
const NORMAL_RANK_DRIVER = 'NORMAL';
const VIP_RANK_DRIVER = 'VIP';

const RANK_DRIVER_LIST = [
    NORMAL_RANK_DRIVER => 'Bình thường',
    VIP_RANK_DRIVER => 'Hạng VIP',
    'GOLD' => 'Hạng vàng',
    'AGENCY' => 'Đại lý',
    'BLACKLIST' => 'Danh sách đen',
];

// ==================== Certificate Types =============
const CERTIFICATE_TYPE_LIST = [
    1 => 'Bằng lái xe hạng A1',
    2 => 'Bằng lái xe hạng A2',
    3 => 'Bằng lái xe hạng A3',
    4 => 'Bằng lái xe hạng A4',
    5 => 'Bằng lái xe ôtô hạng B1',
    6 => 'Bằng lái xe ô tô hạng B2',
    7 => 'Bằng lái xe hạng C',
    8 => 'Bằng lái xe hạng D',
    9 => 'Bằng lái xe hạng E',
    10 => 'Bằng lái xe hạng F',
];

// ==================== English Level =================
const ENGLISH_LIST = [
    0 => 'Không biết tiếng Anh',
    1 => 'Biết một chút',
    2 => 'Có thể trao đổi',
    3 => 'Trao đổi thành thạo',
    4 => 'Như người bản địa',
];

// ==================== Status List ===================
const STATUS_LIST = [
    0 => 'Người mới',
    1 => 'Đang hoạt động',
    2 => 'Khóa',
];

// ========== Customer Service Status/Properties =======
const STATUS_CUSTOMER_SERVICE_NO_PROCESS = 0;
const STATUS_CUSTOMER_SERVICE_SUCCESS = 1;
const STATUS_CUSTOMER_SERVICE_ERROR = 2;
const STATUS_CUSTOMER_SERVICE_MEMBER = 3;
const STATUS_CUSTOMER_SERVICE_NO_CALL = 4;
const STATUS_CUSTOMER_SERVICE_2_WAY_BOOKING = 5;
const STATUS_CUSTOMER_SERVICE_ZALO = 6;
const STATUS_CUSTOMER_SERVICE_BOOK = 7;
const STATUS_CUSTOMER_SERVICE_BOOK_AGAIN = 8;
const STATUS_CUSTOMER_SERVICE_FOREIGNER = 9;
const STATUS_CUSTOMER_SERVICE_CANCEL = 10;
const STATUS_CUSTOMER_SERVICE_NO_CHECK = 99;
// ========== Car Types by Weight (tons) ==============
const CAR_TYPE_750KG = 1;
const CAR_TYPE_1250KG = 2;
const CAR_TYPE_1500KG = 3;
const CAR_TYPE_1900KG = 4;
const CAR_TYPE_2500KG = 5;
const CAR_TYPE_3500KG = 6;
const CAR_TYPE_5000KG = 7;
const CAR_TYPE_7000KG = 8;
const CAR_TYPE_10000KG = 9;
const CAR_TYPE_OVER_10000KG = 10;
const CAR_TYPE_500KG = 11;
const CAR_TYPE_1000KG = 12;
const CAR_TYPE_VAN_500KG= 13;
const CAR_TYPE_VAN_750KG = 14;
const CAR_TYPE_VAN_1000KG = 15;
// ========== Trip Source Types =======================
const SOURCE_TRIP_TYPE_DRIVER = 1;
const SOURCE_TRIP_TYPE_FB = 2;
const SOURCE_TRIP_TYPE_WEB = 3;
const SOURCE_TRIP_TYPE_ZALO = 4;
const SOURCE_TRIP_TYPE_AGENCY = 5;
const SOURCE_TRIP_TYPE_MAIL_1 = 6;
const SOURCE_TRIP_TYPE_CALL_1 = 7;
const SOURCE_TRIP_TYPE_CUSTOMER = 8;
const SOURCE_TRIP_TYPE_CUSTOMER_ROLLBACK = 9;
const SOURCE_TRIP_TYPE_CALL_2 = 10;
const SOURCE_TRIP_TYPE_MAIL_2 = 11;
const SOURCE_TRIP_TYPE_ZALO_1 = 12;
const SOURCE_TRIP_TYPE_FB_1 = 13;
const SOURCE_TRIP_TYPE_CALL_DIRECTLY = 14;
const SOURCE_TRIP_TYPE_TIKTOK = 15;
const SOURCE_TRIP_TYPE_COGOVI = 16;
const SOURCE_TRIP_TYPE_ZALO_CSKH = 17;
const SOURCE_TRIP_TYPE_ZALO_MR = 18;
const SOURCE_TRIP_TYPE_ZALO_XEVIP = 19;
const SOURCE_TRIP_TYPE_ZALO_KETOAN = 20;
// ========== Round Trip Types ========================
const ROUND_TRIP_INNER = 1;  // Nội thành
const ROUND_TRIP_PROVINCE = 2;  // Liên tỉnh
// ========== Driver Status ===========================
const STATUS_DRIVER_NORMAL = 0;
const STATUS_DRIVER_BAN = 1;
const STATUS_DRIVER_BAN_WAIT_REVIEW = 2;
// ========== Customer Properties =====================
const CUSTOMER_PROPERTY_NEW = 0;
const CUSTOMER_PROPERTY_OLD = 1;
const CUSTOMER_PROPERTY_RETURN = 2;
const CUSTOMER_PROPERTY_RETURN_CSKH = 3;
const CUSTOMER_PROPERTY_AGENCY = 4;
// ========== Voucher Types ===========================
const VOUCHER_VND_TYPE = 0;
const VOUCHER_PERCENT_TYPE = 1;
// ========== Driver Filter Types =====================
const DRIVER_FILTER_ALL = 0;
const DRIVER_FILTER_NEW_THIS_WEEK = 1;
const DRIVER_FILTER_NEW_THIS_MONTH = 2;
// ========== Customer Service Times ==================
const CUSTOMER_SERVICE_TIMES_ALL = -1;
const CUSTOMER_SERVICE_TIMES_1 = 1;
const CUSTOMER_SERVICE_TIMES_2 = 2;
const CUSTOMER_SERVICE_TIMES_SUCCESS = 3;
// ========== Booking Payment Methods ================
const BOOKING_PAYMENT_METHOD_BID = 0;
const BOOKING_PAYMENT_METHOD_BANK = 1;
// ========== Miscellaneous ==========================
const TIME_CALL_DRIVER = 40;
// ========== Log Action Types =======================
const ACTION_CREATE_LOG = 'create';
const ACTION_UPDATE_LOG = 'update';
const ACTION_SEARCH_LOG = 'search';
const ACTION_PRICE_LOG = 'price';
// ========== Source Key Types =======================
const SOURCE_KEY_FACEBOOK = 0;
const SOURCE_KEY_TIKTOK = 1;
const SOURCE_KEY_ZALO = 2;
const SOURCE_KEY_GOOGLE = 3;
const SOURCE_KEY_ORGANIC = 4;
const SOURCE_KEY_EMPLOYEE = 5;
// ========== Bank Account Types =====================
const BANK_OF_DUNG = 1;
const BANK_OF_HUY = 2;
// ========== License Plate Types ====================
const LICENSE_TYPE_WHITE = 1;
const LICENSE_TYPE_YELLOW = 0;
// ========== Car Engine Types =======================
const CAR_TYPE_ELECTRIC = 1;
const CAR_TYPE_GASOLINE = 0;
// ========== Notification Types =====================
const NOTIFY_TRIP_STARTING = 1;
const NOTIFY_MESSAGE_DRIVER = 2;
const NOTIFY_PAY_TRANSACTION_DRIVER = 3;
// ========== Transaction Flags ======================
const TRANSACTION_FLAG_WARNING = 1;
const TRANSACTION_FLAG_DANGER = 2;
// ========== Notification Pay Type ==================
const NOTIFICATION_PAY_TYPE = 1;
// ========== Service Types ==========================
const SERVICE_PACKAGE = 1;
const SERVICE_LOADING = 2;
const SERVICE_TRUCK = 3;
const SERVICE_MERGE = 4;
// ========== Driver Sub Types =========================
const DRIVER_TYPE_NORMAL = 0;
const DRIVER_TYPE_SUB = 1;
