<?php

namespace app\component;

class SystemConfigbie
{
    public function system()
    {
        $data['recharge'] = [
            'label' => 'Thông tin chuyển tiền',
            'description' => 'Cài đặt thông tin và lời nhắn chuyển tiền.',
            'value' => [
                'template_success' => ['type' => 'text', 'label' => 'Template chuyển tiền thành công'],
                'template_failed' => ['type' => 'text', 'label' => 'Template chuyển tiền thát bại'],
                'promotion' => ['type' => 'textarea', 'label' => 'Khuyến mãi'],
                'time_start' => ['type' => 'text', 'label' => 'Thời gian bắt đầu khuyến mãi'],
                'time_end' => ['type' => 'text', 'label' => 'Thời gian kết thúc khuyến mãi'],
            ],
        ];
        $data['auto'] = [
            'label' => 'Cấu hình tự động bán lịch',
            'description' => 'Cài đặt thông tin và cấu hình tự động bán lịch trên hệ thống.',
            'value' => [
                'time_sell_soon' => ['type' => 'text', 'label' => 'Thời gian bán sớm (HH:ii)'],
                'minute_sell_soon' => ['type' => 'text', 'label' => 'Số phút bước nhảy bán sớm (HH:ii)'],
                'money_sell_soon' => ['type' => 'text', 'label' => 'Số tiền theo bước nhảy bán sớm'],
                'time_sell_late_1' => ['type' => 'text', 'label' => 'Thời gian bán muộn lượt 1 (HH:ii)'],
                'money_sell_late_1' => ['type' => 'text', 'label' => 'Số tiền bán muộn lượt 1 theo phút'],
                'time_sell_late_2' => ['type' => 'text', 'label' => 'Thời gian bán muộn lượt 2 (HH:ii)'],
                'money_sell_late_2' => ['type' => 'text', 'label' => 'Số tiền bán muộn lượt 2 theo phút'],
                'time_sell_late_free' => ['type' => 'text', 'label' => 'Thời gian bán muộn lượt free (HH:ii)'],
                'time_accept_sell_soon' => ['type' => 'text', 'label' => 'Khoảng thời gian chấp nhận bán sớm (HH:ii)'],
                'schedule_accept' => ['type' => 'checkbox', 'label' => 'Lịch trình cho phép bán sớm', 'array' => SCHEDULE_LIST_TRIP],
                'type_of_car_accept' => ['type' => 'checkbox', 'label' => 'Loại xe cho phép bán sớm', 'array' => TYPE_OF_CAR_LIST],
            ],
        ];
        $data['zalo'] = [
            'label' => 'Cấu hình gửi mail Zalo',
            'description' => 'Cài đặt cấu hình gửi mail Zalo.',
            'value' => [
                'template_1' => ['type' => 'text', 'label' => 'Template zalo gửi mail báo tạo chuyến thành công tới KH'],
                'template_1_voucher' => ['type' => 'text', 'label' => 'Template zalo gửi mail cùng voucher báo tạo chuyến thành công tới KH (327896 - Không dùng thì để rỗng)'],
                'template_2' => ['type' => 'text', 'label' => 'Zalo template 2'],
                'template_3' => ['type' => 'text', 'label' => 'Zalo template 3'],
                'template_4' => ['type' => 'text', 'label' => 'Zalo template không gửi giá đại lý'],
                'template_notify' => ['type' => 'text', 'label' => 'Template Zalo thông báo tài xế sắp tới giờ đón khách'],
                'template_driver_sub' => ['type' => 'text', 'label' => 'Template Zalo yêu cầu tạo tài xế phụ'],
                'template_send_otp' => ['type' => 'text', 'label' => 'Template gửi OTP'],
                'access_token' => ['type' => 'textarea', 'label' => 'Zalo access_token'],
                'refresh_token' => ['type' => 'textarea', 'label' => 'Zalo refresh_token'],
            ],
        ];
        $data['driver'] = [
            'label' => 'Cấu hình thông tin lái xe',
            'description' => 'Cài đặt thông tin và điều kiện của lái xe.',
            'value' => [
                'year' => ['type' => 'text', 'label' => 'Đời xe hưởng chế độ VIP (>= số năm)'],
                'trip_count' => ['type' => 'text', 'label' => 'Số lịch được hưởng chế độ VIP (>= số lịch)'],
                'point' => ['type' => 'text', 'label' => 'Điểm trung bình CSKH được hưởng chế độ VIP (>= điểm)'],
                'rank_VIP' => ['type' => 'text', 'label' => 'Thời gian lái xe VIP có thể bid trước (x phút)'],
                'rank_GOLD' => ['type' => 'text', 'label' => 'Thời gian lái xe GOLD có thể bid trước (x phút)'],
                'rank_AGENCY' => ['type' => 'text', 'label' => 'Thời gian lái xe Đại lý có thể bid trong khoảng (x phút)'],
                'rank_BLACKLIST' => ['type' => 'text', 'label' => 'Thời gian lái xe Blacklist có thể bid trong khoảng (x phút)'],
            ],
        ];
        $data['reason'] = [
            'label' => 'Lí do từ chối',
            'description' => 'Cài đặt các lý do từ chối booking.',
            'value' => [
                'reject' => [
                    'type' => 'textarea',
                    'label' => 'Lý do từ chối (Phân tách lý do từ chối bằng dấu "|")',
                    'placeholder' => 'Ví dụ: Không nghe|Đắt|Sai số|Máy bận|Phân vân',
                ],
                'lock' => [
                    'type' => 'textarea',
                    'label' => 'Lý do khóa tài xế (Phân tách lý do từ chối bằng dấu "|")',
                    'placeholder' => 'Ví dụ: Thái độ không tốt|Láo',
                ],
            ],
        ];
        $data['point'] = [
            'label' => 'Phản hồi khách hàng',
            'description' => 'Cài đặt các điểm theo từng phản hồi.',
            'value' => [
                'default' => [
                    'type' => 'textarea',
                    'label' => 'Điểm mặc định theo đời xe (Phân tách điểm bằng cách xuống dòng và giữa phản hồi + điểm là dấu "=")',
                ],
                'feedback' => [
                    'type' => 'textarea',
                    'label' => 'Phản hồi khách hàng (Phân tách phản hồi bằng cách xuống dòng và giữa phản hồi + điểm là dấu "=")',
                ],
                'vip' => [
                    'type' => 'text',
                    'label' => 'Mốc điểm VIP',
                ],
            ],
        ];
        $data['call'] = [
            'label' => 'Cấu hình Call Block',
            'description' => 'Cài đặt thông tin và cấu hình call block.',
            'value' => [
                'phone_web_1' => ['type' => 'text', 'label' => 'Số điện thoại Web 1(Phân tách số điện thoại là dấu "|")'],
                'phone_web_2' => ['type' => 'text', 'label' => 'Số điện thoại Web 2(Phân tách số điện thoại là dấu "|")'],
            ],
        ];
        $data['other'] = [
            'label' => 'Cấu hình khác',
            'description' => 'Cài đặt thông tin và cấu hình khác trên hệ thống.',
            'value' => [
                'price_vat' => ['type' => 'text', 'label' => 'Tiền thuế VAT (%)'],
                'revenue_time' => ['type' => 'textarea', 'label' => 'Khoảng thời gian (Phân tách bằng cách xuống dòng)'],
            ],
        ];
        $data['Taxi'] = [
            'label' => 'Cấu hình APP & IOS',
            'description' => 'Cài đặt cấu hình gửi mail Zalo.',
            'value' => [
                'version' => ['type' => 'text', 'label' => 'Version Code'],
                'android_url' => ['type' => 'text', 'label' => 'Url store Android'],
                'ios_url' => ['type' => 'text', 'label' => 'Url store Ios'],
            ],
        ];

        return $data;
    }
}
