<?php

use app\helpers\MyHelper;

// Lấy danh sách quyền
$role = (isset(Yii::$app->controller->roleCurrentUser) ? Yii::$app->controller->roleCurrentUser : []);
$permissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->id);
$permissions_array = [];
foreach ($permissions as $key => $permission_item) {
    $permissions_array[] = $permission_item->name;
}

?>
<aside class="main-sidebar">
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
                <p>
                    <?php echo Yii::$app->user->identity->username ?>
                </p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        <?php
        $menu_has_route = [];

        // Xác nhận tài xế nhận chuyến
        if (MyHelper::check_user_can(['/trip/*', '/trip/index'], $permissions_array)) {
            $menu_has_route['trip-create'] = [
                'label' => 'Thêm mới lịch',
                'icon' => 'fas fa-plus',
                'url' => ['/trip/create'],
                'options' => ['class' => 'bg-primary'],
            ];
        }

        if (isset($role['DAI_LY_ROLE'])) {
            $menu_has_route['call-agency'] = [
                'label' => 'Tư vấn chuyến xe',
                'icon' => 'fas fa-plus',
                'url' => ['/call-agency'],
                'options' => ['class' => 'bg-primary sidebar-primary'],
            ];
        }

        // lịch xe
        if (MyHelper::check_user_can(['/trip/*', '/trip/index'], $permissions_array)) {
            $menu_has_route['trip'] = [
                'label' => 'Lịch xe',
                'icon' => 'fas fa-car',
                'url' => ['/trip'],
            ];
        }
        // lịch booking
        if (MyHelper::check_user_can(['/statistic/*', '/statistic/index'], $permissions_array)) {
            $menu_has_route['booking-zalo'] = [
                'label' => 'Lịch Booking ' . (! isset($role['DAI_LY_ROLE']) ? '<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="number-booking-create">0</span>' : ''),
                'icon' => 'th-list',
                'url' => ['/statistic'],
            ];

            if (! isset($role['DAI_LY_ROLE'])) {
                $menu_has_route['booking-waiting'] = [
                    'label' => 'Lịch chờ <span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="number-booking-waiting">0</span>',
                    'icon' => 'th-list',
                    'url' => ['/statistic?SearchBooking%5Bstatus%5D=WAITING'],
                ];
            }
        }

        // yêu cầu liên hệ
        // if (MyHelper::check_user_can(['/request-call-back/*', '/request-call-back/index'], $permissions_array)) {
        //     $menu_has_route['request-call-back'] = [
        //         'label' => 'Yêu cầu liên hệ <span class="wrap-icon-left"><img src="/img/icon-pro.png" alt=""> <span id="countNumberPhoneWaiting">0</span>',
        //         'icon' => 'th-list',
        //         'url' => ['/request-call-back'],
        //     ];
        // }

        // Thống kê đại lý
        if (isset($role['DAI_LY_ROLE'])) {
            $menu_has_route['revenue-agency'] = [
                'label' => 'Thống kê đại lý',
                'icon' => 'bar-chart',
                'url' => ['/revenue/agency'],
            ];
        }

        // Xác nhận tài xế nhận chuyến
        if (MyHelper::check_user_can(['/call-driver/*', '/call-driver/index'], $permissions_array)) {
            $menu_has_route['call-drive'] = [
                'label' => 'Gọi tài xế',
                'icon' => 'fas fa-phone',
                'url' => ['/call-driver/index'],
            ];
        }

        // chăm sóc khách hàng
        if (MyHelper::check_user_can(['/customer-service/*', '/customer-service/index'], $permissions_array)) {
            $menu_has_route['customer-service'] = [
                'label' => 'Chăm sóc khách hàng',
                'icon' => 'fas fa-phone',
                'url' => '#',
                'items' => [
                    [
                        'label' => 'Khách mới <span class="wrap-icon-left"><img src="/img/icon-pro.png" alt=""> <span id="number-customer-service-new">0</span>',
                        'icon' => 'volume-control-phone',
                        'url' => ['/customer-service/index'],
                    ],
                    [
                        'label' => 'Khách quay đầu <span class="wrap-icon-left"><img src="/img/icon-pro.png" alt=""> <span id="number-customer-service-rollback">0</span>',
                        'icon' => 'volume-control-phone',
                        'url' => ['/customer-service/customer-rollback'],
                    ],
                    [
                        'label' => 'Khách VIP <span class="wrap-icon-left"><img src="/img/icon-pro.png" alt=""> <span id="number-customer-service-vip">0</span>',
                        'icon' => 'diamond',
                        'url' => ['/customer-service/customer-vip'],
                    ],
                ],
            ];
        }


        // công nợ
        if (MyHelper::check_user_can(['/trip-driver/*', '/trip-driver/index'], $permissions_array)) {
            $menu_has_route['trip-driver'] = [
                'label' => 'Công nợ',
                'icon' => 'fas fa-money',
                'url' => '#',
                'items' => [
                    [
                        'label' => 'Tổng đài nợ tài xế<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="countTripDriverSettlement">0</span>',
                        'icon' => 'money',
                        'url' => ['/trip-driver/driver-debt-settlement'],
                    ],
                    [
                        'label' => 'Thanh toán bán lịch<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="countPassTrip">0</span>',
                        'icon' => 'money',
                        'url' => ['/trip-driver/pass-trip'],
                    ],
                    [
                        'label' => 'Tài xế nợ tổng đài<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="countTripDriverCollection">0</span>',
                        'icon' => 'money',
                        'url' => ['/trip-driver/driver-debt-collection'],
                    ],
                    [
                        'label' => 'Khách nợ tổng đài<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="countTripDebtCustomers">0</span>',
                        'icon' => 'money',
                        'url' => ['/trip-driver/debt-customers'],
                    ],
                    [
                        'label' => 'Tổng đài nợ đại lý<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="number-admin-debt-agency">0</span>',
                        'icon' => 'money',
                        'url' => ['/trip-driver/admin-debt-agency'],
                    ],
                    [
                        'label' => 'Đại lý nợ tổng đài<span class="wrap-icon-left"><img src="/img/icon-pro.png"> <span id="number-agency-debt-admin">0</span>',
                        'icon' => 'money',
                        'url' => ['/trip-driver/agency-debt-admin'],
                    ],
                ],
            ];
        }

        // lái xe
        if (MyHelper::check_user_can(['/driver/*', '/driver/index'], $permissions_array)) {
            $menu_has_route['driver'] = [
                'label' => 'Lái xe',
                'icon' => 'fas fa-user',
                'url' => '#',
                'items' => [
                    ['label' => 'Danh sách lái xe', 'icon' => 'th-list', 'url' => ['/driver']],
                    ['label' => 'Duyệt tài xế có nhiều xe <span class="wrap-icon-left"><img src="/img/icon-pro.png"><span id="number-driver-sub">0</span>', 'icon' => 'car', 'url' => ['/driver/register-driver-sub']],
                    ['label' => 'Duyệt tài khoản lái xe <span class="wrap-icon-left"><img src="/img/icon-pro.png"><span id="number-driver-register">0</span>', 'icon' => 'user-plus', 'url' => ['/driver/register']],
                    ['label' => 'Danh sách vị trí lái xe <span class="wrap-icon-left">', 'icon' => 'map-marker', 'url' => ['/driver/get-driver-location']],
                    ['label' => 'Lịch sử lái xe <span class="wrap-icon-left">', 'icon' => 'history', 'url' => ['/driver/history-driver']],
                ],
            ];
        }

        // khách hàng
        if (MyHelper::check_user_can(['/customer/*', '/customer/index'], $permissions_array)) {
            $menu_has_route['customer'] = ['label' => 'Khách Hàng', 'icon' => 'th-list', 'url' => ['/customer']];
        }

        // lịch sử nạp tiền
        if (MyHelper::check_user_can(['/pay/*', '/pay/index'], $permissions_array)) {
            $menu_has_route['history-pay'] = [
                'label' => 'Lịch sử nạp tiền',
                'icon' => 'history',
                'url' => '#',
                'items' => [
                    ['label' => 'Nạp tiền', 'icon' => 'file-code-o', 'url' => ['/pay/create']],
                    ['label' => 'Danh sách nạp tiền Web', 'icon' => 'th-list', 'url' => ['/pay']],
                    ['label' => 'Danh sách nạp tiền SMS', 'icon' => 'usd', 'url' => ['/pay/list-sms']],
                    ['label' => 'Danh sách thu chi', 'icon' => 'usd', 'url' => ['/pay/list-payment']],
                ],
            ];
        }

        // revenue
        if (MyHelper::check_user_can(['/revenue/*', '/revenue/index', '/revenue/driver-news'], $permissions_array)) {
            $menu_has_route['revenue'] = [
                'label' => 'Báo cáo tổng hợp',
                'icon' => 'bar-chart',
                'url' => '#',
                'items' => [
                    [
                        'label' => 'Doanh thu theo ngày',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue'],
                    ],
                    [
                        'label' => 'Thống kê booking',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue/booking'],
                    ],
                    [
                        'label' => 'Doanh thu nạp tiền',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue/pay'],
                    ],
                    [
                        'label' => 'Thống kê tổng đài viên',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue/user-statistic'],
                    ],
                    [
                        'label' => 'Thống kê lái xe mới',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue/driver-news'],
                    ],
                    [
                        'label' => 'Thống kê nguồn bán',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue/zalo'],
                    ],
                    [
                        'label' => 'Thống kê đại lý',
                        'icon' => 'file-code-o',
                        'url' => ['/revenue/agency'],
                    ],
                ],
            ];

            // marketing
            if (MyHelper::check_user_can(['/marketing/*', '/marketing/index'], $permissions_array)) {
                $menu_has_route['marketing'] = [
                    'label' => 'Báo cáo marketing',
                    'icon' => 'line-chart',
                    'url' => '#',
                    'items' => [
                        [
                            'label' => 'Lịch xe đặt',
                            'icon' => 'car',
                            'url' => ['/marketing'],
                        ],
                        [
                            'label' => 'Yêu cầu gọi lại',
                            'icon' => 'phone',
                            'url' => ['/marketing/callback'],
                        ],
                    ],
                ];
            }
        }
        // thông báo
        if (MyHelper::check_user_can(['/message/*', '/message/index'], $permissions_array)) {
            $menu_has_route['alert-setting'] = [
                'label' => 'Thông Báo',
                'icon' => 'fas fa-bell',
                'url' => ['/message'],
                'items' => [
                    [
                        'label' => 'Danh sách',
                        'url' => ['/message'],
                    ],
                    [
                        'label' => 'Gửi cho lái xe',
                        'url' => ['/message/create'],
                    ],
                ],
            ];
        }

        // cài đặt admin
        if (MyHelper::check_user_can(['/admin/*', '/admin/index'], $permissions_array)) {
            $menu_has_route['admin-setting'] = [
                'label' => 'Cài đặt admin',
                'icon' => 'gear',
                'url' => '#',
                'items' => [
                    [
                        'label' => 'Quản lý tài khoản',
                        'icon' => 'users',
                        'url' => ['/admin'],
                    ],
                ],
            ];

            if (MyHelper::check_user_can(['/agency/*', '/agency/index'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Đại lý',
                    'icon' => 'group',
                    'url' => ['/agency'],
                ];
            }

            if (MyHelper::check_user_can(['/zalo/*', '/zalo/index'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Bên thứ 3',
                    'icon' => 'object-group',
                    'url' => ['/zalo'],
                    'items' => [
                        [
                            'label' => 'Nguồn',
                            'url' => ['/zalo'],
                        ],
                        [
                            'label' => 'Nhóm nguồn',
                            'url' => ['/zalo-catalogue'],
                        ],
                        [
                            'label' => 'Người bán',
                            'url' => ['/zalo-seller'],
                        ],
                    ],
                ];
            }

            if (MyHelper::check_user_can(['/calculation-formula/*', '/price-setting/*', '/calculation-formula/create'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Cấu hình giá',
                    'icon' => 'money',
                    'url' => ['/calculation-formula/index'],
                    'items' => [
                        [
                            'label' => 'Cấu hình bảng giá',
                            'url' => ['/calculation-formula/index'],
                        ],
                        // [
                        //     'label' => 'Cấu hình tự tăng giá',
                        //     'url' => ['/calculation-formula/auto-increase-price'],
                        // ],
                        // [
                        //     'label' => 'Cấu hình tự động bán',
                        //     'url' => ['/calculation-formula/config-auto-sale'],
                        // ],
                        [
                            'label' => 'Cấu hình giá theo event',
                            'url' => ['/price-setting/index'],
                        ],
                    ],
                ];
            }

            if (MyHelper::check_user_can(['/location-configuration/*', '/location-configuration/index'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Cấu hình vị trí',
                    'icon' => 'location-arrow',
                    'url' => ['/location-configuration/index'],
                ];
            }

            if (MyHelper::check_user_can(['/system-configuration/*', '/system-configuration/create'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Cấu hình hệ thống',
                    'icon' => 'gear',
                    'url' => ['/system-configuration/create'],
                ];
            }

            if (MyHelper::check_user_can(['/area/*', '/area/index'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Cấu hình khu vực',
                    'icon' => 'map-o',
                    'url' => ['/area'],
                    'items' => [
                        [
                            'label' => 'Cấu hình chung khu vực',
                            'url' => ['/area-configuration/index'],
                        ],
                        [
                            'label' => 'Quản lý khu vực',
                            'url' => ['/area/index'],
                        ],
                    ],
                ];
            }

            if (MyHelper::check_user_can(['/log/*', '/log/index'], $permissions_array)) {
                $menu_has_route['admin-setting']['items'][] = [
                    'label' => 'Nhật ký hoạt động',
                    'icon' => 'sticky-note',
                    'url' => ['/log'],
                    'items' => [
                        [
                            'label' => 'Nhật ký admin',
                            'url' => ['/log'],
                        ],
                        [
                            'label' => 'Nhật ký đại lý',
                            'url' => ['/log/agency'],
                        ],
                    ],
                ];
            }
        }
        ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu'],
                'items' => $menu_has_route,
                'encodeLabels' => false,
            ]
        ) ?>
    </section>
</aside>
