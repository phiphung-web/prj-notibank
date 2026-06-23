<?php

namespace app\services;

use app\helpers\MyStringHelper;
use app\models\Bid;
use app\models\GroupZalo;
use app\models\Trip;
use app\models\TripGroup;
use app\models\TripReturn;
use app\models\Driver;
use app\models\Admin;
use Yii;
use yii\db\Query;

/**
 * Class TripService
 *
 * This class handles the trip-related operations.
 */
class TripService
{
    protected $systemConfiguration;
    public $sendMessageZnsService;


    public function __construct()
    {
        $this->systemConfiguration = new SystemConfigurationService();
        $this->sendMessageZnsService = new SendMessageZnsService();
    }

    public function checkTripStatus($model)
    {
        if ($model->status != 'DONE') {
            throw new \Exception('Lịch này chưa nhận');
        }
    }

    public function findBid($id)
    {
        $bid = Bid::findOne(['status' => STATUS_BID_SUCCESS, 'trip_id' => $id]);
        if (!$bid) {
            throw new \Exception('Có lỗi xảy ra');
        }

        return $bid;
    }

    public function getTripsStartingIn20Minutes()
    {
        $systemConfiguration = $this->systemConfiguration->getAllConfiguration();
        $currentTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600);
        $endTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600 + 1200);
        $query = new Query();
        $trips = $query->select(['trip.id', 'trip.pickup_time', 'trip.pickup_address', 'trip.destination_address', 'trip.status', 'trip.customer_name', 'trip.customer_phone', 'message_zns.code', 'message_zns.template_id', 'driver.username'])
            ->from('trip')
            ->andWhere(['between', 'pickup_time', $currentTime, $endTime])
            ->andWhere(['trip.status' => 'DONE'])
            // ->leftJoin('message_zns', 'trip.id = message_zns.trip_id AND message_zns.template_id = ' . $systemConfiguration['zalo_template_notify'])
            ->leftJoin('notification_logs', 'trip.id = notification_logs.trip_id AND notification_logs.template_id = ' . $systemConfiguration['zalo_template_notify'])
            ->innerJoin('bid', 'bid.status = "SUCCESS" AND bid.trip_id = trip.id')
            ->innerJoin('driver', 'bid.driver_id = driver.id')
            ->all();

        return $trips;
    }

    public function getTripDebtByAgencyId($agencyId = 0)
    {
        return Trip::find()->where([
            'trip.agency_debt' => 0,
            'trip.status' => [STATUS_TRIP_DONE, STATUS_TRIP_COMPLETE],
            'agency_id' => $agencyId,
        ])->innerJoin('agency', 'trip.agency_id = agency.id AND agency.status = 1')->all();
    }

    public function processZaloSeller($modelTripGroup, $model)
    {
        if ($modelTripGroup->zalo_seller_id > 0) {
            $modelTripGroup->price = str_replace('.', '', $modelTripGroup->price);

            $modelTripGroup->save();
            $this->sendMessageZnsService->sendMessageDriverZns([
                'customer_phone' => $model->customer_phone,
                'tracking_id' => $model->id,
                'driver_name' => $modelTripGroup->driver_name,
                'phone_number' => $modelTripGroup->driver_phone,
                'license_plates' => ' ',
                'vehicle_type' => isset(TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car]) ? TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car] : ' ',
                'customer_name' => $model->customer_name,
            ]);
        }

        return $modelTripGroup;
    }

    public function updateDebtTrip($trip, $request)
    {
        $status = $request['status_trip'];
        if (in_array('driver_debt_admin', $status)) {
            $this->updateDriverDebtAdmin($trip);
        }
        if (in_array('admin_debt_driver', $status)) {
            $this->updateAdminDebtDriver($trip);
        }
        if (in_array('customer_debt_admin', $status)) {
            $this->updateCustomerDebtAdmin($trip);
        }
        if (in_array('admin_debt_agency', $status)) {
            $this->updateDebtAgency($trip);
        }
        if (in_array('agency_debt_admin', $status)) {
            $this->updateDebtAgency($trip);
        }
    }

    public function updateDriverDebtAdmin($trip)
    {
        $command = Yii::$app->db->createCommand('UPDATE trip SET is_collect_money = 1, driver_debt = 0 WHERE id = ' . $trip->id);
        $command->execute();
    }

    public function updateAdminDebtDriver($trip)
    {
        $query = '';
        if (isset($trip->tripGroup->type) && $trip->tripGroup->type == 2 && $trip->tripGroup->price > 0) {
            $query = 'UPDATE trip SET is_collect_money = 1, driver_debt = 0 WHERE id = ' . $trip->id;
        } else {
            $query = 'UPDATE trip SET is_collect_money = 0, driver_debt = 0 WHERE id = ' . $trip->id;
        }
        $command = Yii::$app->db->createCommand($query);
        $command->execute();
    }

    public function updateCustomerDebtAdmin($trip)
    {
        $command = Yii::$app->db->createCommand('UPDATE trip SET is_collect_money = 0, collected_money = 0 WHERE id = ' . $trip->id);
        $command->execute();
    }

    public function updateDebtAgency($trip)
    {
        $money_debt_agency = str_replace('.', '', $_POST['money_debt_agency']);
        $command = Yii::$app->db->createCommand('UPDATE trip SET agency_debt = 0, money_debt_agency = ' . $money_debt_agency . ' WHERE id = ' . $trip->id);
        $command->execute();
    }

    /**
     * Store Data Trip Model
     * @param Trip $model
     * @param TripGroup $modelTripGroup
     * @return Trip
     */
    public function storeDataTrip(Trip $model, TripGroup $modelTripGroup, $method = ''): Trip
    {
        $model->trip_group_id = $modelTripGroup->id;
        $model->price_bid = str_replace('.', '', $model->price_bid);
        $model->money_debt_agency = str_replace('.', '', $model->money_debt_agency);
        $model->money_customer_deposit = str_replace('.', '', $model->money_customer_deposit);
        $model->price_customer = str_replace('.', '', $model->price_customer);
        if ($modelTripGroup->zalo_seller_id > 0 && $modelTripGroup->group_zalo_id > 0) {
            $model->price_bid = (string) $this->calculatorPrice($model, $modelTripGroup);
            if ($model->status != STATUS_TRIP_PENDING) {
                $model->status = STATUS_TRIP_COMPLETE;
            }
        }

        if ($model->status != STATUS_TRIP_PENDING) {
            if ($model->status == STATUS_TRIP_EXPIRE && $model->pickup_time > date('Y-m-d H:i:s') || $model->status == STATUS_BID_PENDING) {
                $model->status = STATUS_TRIP_OPEN;
            }
        }

        if ($method == 'create') {
            $model->status = STATUS_TRIP_PENDING;
            $model->call_driver = CALL_DRIVER_NOT_CONFIRMED;
        }

        if ($model->source_trip == SOURCE_TRIP_TYPE_DRIVER) {
            $model->driver_debt = 0;
        }

        if ($model->status == STATUS_TRIP_CANCEL) {
            $model->status = STATUS_TRIP_OPEN;
        }
        if ($model->service == SERVICE_LOADING) {
            $model->is_collect_money = false;
        }

        // Determine driver debt settlement and collection flags based on conditions
        $model = $this->update_driver_debt($model, $modelTripGroup, [], 'create');

        return $model;
    }

    /**
     * Update driver debt settlement and collection flags for a trip based on certain conditions.
     *
     * @param Trip $model The trip model to update.
     * @param TripGroup $modelTripGroup The associated trip group model.
     * @return Trip The updated trip model with driver debt flags modified as per conditions.
     */
    public function update_driver_debt(Trip $model, TripGroup $modelTripGroup, $dataOld = [], $module = '')
    {
        // trạng thái thu tiền khách
        if ($model->is_collect_money == 1) {
            $model->collected_money = 1;
        } else {
            $model->collected_money = 0;
        }

        // trạng thái nợ của tài xế 0: nợ, 1: không nợ
        if ($model->is_collect_money == 1) {
            if (!isset($modelTripGroup->type) || $modelTripGroup->type == 0) {
                $model->driver_debt = 1;
            } else {
                $model->driver_debt = 0;
            }
        } else {
            $model->driver_debt = 0;
        }

        // Xử lý nợ của đại lý khi cập nhật nguồn
        if ((isset($model['source_trip'], $dataOld) && is_array($dataOld) && count($dataOld) && $model['source_trip'] == SOURCE_TRIP_TYPE_AGENCY)) {
            if ($model['source_trip'] != $dataOld['source_trip'] || $model['agency_id'] != $dataOld['agency_id']) {
                $model->agency_debt = 0;
            }
        }

        if ($module == 'create' && isset($model['agency_id']) && $model['agency_id'] > 0) {
            $model->agency_debt = 0;
        }

        return $model;
    }

    /**
     * Calculate the adjusted price for a trip based on the Zalo parameters. - Calculate price bid
     *
     * @param Trip $trip The Trip model instance.
     * @param TripGroup $zalo The TripGroup model instance representing Zalo settings.
     * @return float The calculated adjusted price.
     */
    public function calculatorPrice(Trip $trip, TripGroup $zalo)
    {
        $zaloPrice = !empty($zalo->price) ? $zalo->price : 0;
        $price = 0;

        if ($zalo->group_zalo_id > 0) {
            // Kiểm tra loại Zalo và tính giá điều chỉnh cho phù hợp
            if ($zalo->type == ZALO_TYPE_DRIVER) {
                $price = $zaloPrice;
            }
        }

        return $price;
    }

    public function validateTrip($params = []): bool
    {
        $check = true;
        $price = (int) str_replace('.', '', $params['model']->price_customer);
        if ($params['model']->source_trip == SOURCE_TRIP_TYPE_AGENCY) {
            if (empty($params['model']->agency_id)) {
                $params['model']->addError('agency_id', 'Vui lòng chọn nguồn đại lý');
                $check = false;
            }
        }

        if ($params['model']->sell_start_time > $params['model']->pickup_time) {
            $params['model']->addError('sell_start_time', 'Vui lòng chọn thời gian bán trước thời gian đi');
            $check = false;
        }

        if (isset($params['tripReturn']->money) && $params['tripReturn']->money > $price - $params['bid']['price']) {
            $params['tripReturn']->addError('money', 'Vui lòng nhập số tiền hoàn nhỏ hơn ' . ($price - $params['bid']['price']));
            $check = false;
        }
        if (empty($params['modelTripGroup']->group_zalo_id) || empty($params['modelTripGroup']->zalo_seller_id)) {
            if (isset($params['model']->price_bid) && empty($params['model']->price_bid)) {
                $params['model']->addError('price_bid', 'Vui lòng nhập giá bán cho lái xe.');
                $check = false;
            }
            $price_bid = (int) str_replace('.', '', $params['model']->price_bid);
            if ($price_bid > $price) {
                $params['model']->addError('price_bid', 'Giá bán cho lái xe không được lớn hơn giá báo khách.');
                $check = false;
            }
        }

        if (isset($price) && empty($price)) {
            $params['model']->addError('price_customer', 'Vui lòng nhập giá báo khách.');
            $check = false;
        }

        if (isset($params['model']->customer_phone) && !preg_match('/^(?:\+)?[0-9]+$/', $params['model']->customer_phone)) {
            $params['model']->addError('customer_phone', 'Định dạng số điện thoại không hợp lệ.');
            $check = false;
        }

        return $check;
    }

    public function getTripsStartingInXMinutes($minute = 0, $check = '')
    {
        $startTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600);
        $endTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600 + $minute * 60);
        $query = new Query();
        $condition = ($check == 'GOLD' ? ['send_gold' => 0] : ['send_vip' => 0]);
        $trips = $query->select(['trip.*', 'DATE_SUB(trip.sell_start_time, INTERVAL ' . $minute . ' MINUTE) as new_time'])
            ->from('trip')
            ->andWhere(['>=', 'sell_start_time', $startTime])
            ->andWhere(['<', 'sell_start_time', $endTime])
            ->andWhere(['trip.status' => 'OPEN'])
            ->andWhere($condition)
            ->all();

        return $trips;
    }

    public function getTripNormal($minute = 0)
    {
        $startTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600 - $minute * 60);
        $endTime = gmdate('Y-m-d H:i:s', time() + 7 * 3600);
        $query = new Query();
        $condition = ['send_vip' => 0];
        $trips = $query->select(['trip.*', 'trip.sell_start_time as new_time'])
            ->from('trip')
            ->andWhere(['>=', 'sell_start_time', $startTime])
            ->andWhere(['<', 'sell_start_time', $endTime])
            ->andWhere(['trip.status' => 'OPEN'])
            ->andWhere($condition)
            ->all();

        return $trips;
    }
    /**
     * Process trip return logic - handles refund and status changes
     * @param Trip $model The trip model
     * @param TripGroup $modelTripGroup The trip group model
     * @param Bid $bid The bid model
     * @param array $params Additional parameters including tripReturn data
     * @return array Result with success status and any errors
     */
    public function processTripReturn($model, $modelTripGroup, $bid, $params = [])
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $tripReturn = $params['tripReturn'] ?? null;
            $refund = $params['refund'] ?? false;
            if ($modelTripGroup->zalo_seller_id > 0) {
                $modelTripGroup->price = str_replace('.', '', $modelTripGroup->price);
                if (!$modelTripGroup->save()) {
                    throw new \Exception('Lỗi khi lưu thông tin TripGroup: ' . implode(', ', $modelTripGroup->getFirstErrors()));
                }

                $this->sendMessageZnsService->sendMessageDriverZns([
                    'customer_phone' => $model->customer_phone,
                    'tracking_id' => $model->id,
                    'driver_name' => $modelTripGroup->driver_name,
                    'pickup_time' => $model->pickup_time,
                    'phone_number' => $modelTripGroup->driver_phone,
                    'license_plates' => $modelTripGroup->license_plates,
                    'vehicle_type' => isset(TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car]) ? TYPE_OF_CAR_LIST[$modelTripGroup->type_of_car] : 'Không rõ',
                    "customer_name" => $model->customer_name,
                ]);
                $model->status = STATUS_TRIP_COMPLETE;
                $model->trip_group_id = $modelTripGroup->id;
            } else {
                $model->status = STATUS_TRIP_OPEN;
                $model->trip_group_id = 0;
            }
            $model->price_bid = str_replace('.', '', $model->price_bid);
            $model->price_customer = str_replace('.', '', $model->price_customer);
            $model->money_customer_deposit = str_replace('.', '', $model->money_customer_deposit);
            $model->call_driver = 0;
            $model = $this->update_driver_debt($model, $modelTripGroup, [], 'create');

            if ($modelTripGroup->zalo_seller_id > 0) {
                $model->price_bid = (string) $this->calculatorPrice($model, $modelTripGroup);
            }

            // Mark bid as refunded and track driver balance
            $driver = \app\models\Driver::findOne($bid->driver_id);
            if ($driver) {
                $bid->money_before = $driver->money;
            }
            $bid->status = STATUS_BID_REFUND;

            if ($tripReturn && $refund) {
                if ($driver) {
                    $tripReturn->bid_id = $bid->id;
                    $tripReturn->trip_id = $model->id;
                    $tripReturn->driver_id = $driver->id;
                    $tripReturn->money_before = $driver->money;
                    if ($tripReturn->money !== 0) {
                        $tripReturn->money_after = $driver->money + $tripReturn->money;
                        $driver->money = $tripReturn->money_after;

                        if (empty($tripReturn->note)) {
                            $tripReturn->note = 'Hệ thống tự động hoàn tiền do trả lịch hoặc cập nhật giá.';
                        }

                        if (!$driver->save()) {
                            throw new \Exception('Lỗi khi cập nhật số dư lái xe: ' . implode(', ', $driver->getFirstErrors()));
                        }

                        // Send refund notification
                        $currentAdmin = Yii::$app->user->identity instanceof \app\models\Admin ? Yii::$app->user->identity : null;
                        $notificationService = new \app\services\NotificationService();
                        $response = $notificationService->sendNotificationByUsername(
                            $driver,
                            $currentAdmin,
                            $model,
                            'Hoàn tiền',
                            'Bạn được hoàn ' . MyStringHelper::convertIntegerToPrice($tripReturn->money) . 'đ từ hệ thống, vui lòng kiểm tra lại số dư.',
                            null,
                            []
                        );
                        Yii::info(['refund_push_response' => $response], __METHOD__);
                    } else {
                        $tripReturn->money_after = $driver->money;
                    }


                    // Update bid money_after to reflect the final driver balance after refund
                    $bid->money_after = $driver->money;

                    if (!$tripReturn->save()) {
                        throw new \Exception('Lỗi khi lưu bản ghi hoàn tiền: ' . implode(', ', $tripReturn->getFirstErrors()));
                    }
                }
            }

            if ($modelTripGroup->zalo_seller_id > 0 && $model->trip_group_id > 0) {
                $bidService = new \app\services\BidService();
                $bid = $bidService->createBidTripZalo($model, $bid);
            } else {
                if (!$bid->save()) {
                    throw new \Exception('Lỗi khi cập nhật trạng thái Bid: ' . implode(', ', $bid->getFirstErrors()));
                }
            }

            $SystemConfiguration = new \app\models\SystemConfiguration();
            $zalo_template_2 = $SystemConfiguration->find()->where(['keyword' => 'zalo_template_2'])->one();
            if ($zalo_template_2) {
                $template_id = $zalo_template_2['content'];
                $message_zns = \app\models\MessageZns::find()->where(['trip_id' => $model->id, 'template_id' => $template_id])->one();
                if ($message_zns) {
                    $message_zns->delete();
                }
            }

            $transaction->commit();
            return [
                'success' => true,
                'model' => $model,
                'bid' => $bid,
                'modelTripGroup' => $modelTripGroup
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            \app\helpers\MyHelper::sendErrorToTelegramBot('TripService - processTripReturn() - ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getUserList()
    {
        return \yii\helpers\ArrayHelper::map(
            \app\models\Admin::find()->orderBy('username')->all(),
            'id',
            'username'
        );
    }
    public function buildTripUpdateContent(
        $oldPickupTime,
        $newPickupTime,
        $oldPriceCustomer,
        $newPriceCustomer,
        $oldPriceBid,
        $newPriceBid
    ) {
        $messages = [];
        $deltaDriver = ($oldPriceCustomer - $oldPriceBid) - ($newPriceCustomer - $newPriceBid);
        if ($deltaDriver < 0) {
            $messages[] = sprintf(
                'Bạn đã bị trừ %sđ do hệ thống thay đổi giá chuyến.',
                number_format(abs($deltaDriver))
            );
        } else if ($deltaDriver > 0) {
            $messages[] = sprintf(
                'Bạn đã được hoàn %sđ do hệ thống thay đổi giá chuyến.',
                number_format($deltaDriver)
            );
        } else {
            $messages[] = sprintf(
                'Thông tin chuyến đã được cập nhật.',
            );
        }
        $changes = [];
        if ($oldPriceCustomer != $newPriceCustomer) {
            $changes[] = sprintf(
                'Giá khách: %sđ ⮕ %sđ',
                number_format($oldPriceCustomer),
                number_format($newPriceCustomer)
            );
        }
        if ($oldPriceBid != $newPriceBid) {
            $changes[] = sprintf(
                'Giá lái: %sđ ⮕ %sđ',
                number_format($oldPriceBid),
                number_format($newPriceBid)
            );
        }
        if (($oldTs = strtotime($oldPickupTime)) !== (int) $newPickupTime) {
            $changes[] = sprintf(
                'Giờ đón %s ⮕ %s',
                date('d/m/Y H:i', $oldTs),
                date('d/m/Y H:i', $newPickupTime)
            );
        }
        if (!empty($changes)) {
            $messages[] = '' . implode(', ', $changes) . '. Vui lòng kiểm tra lại chuyến đi.';
        }
        return implode(' ', $messages);
    }

    /**
     * Update pickup time, customer price and bid price after trip has a successful bid.
     * Adjust driver balance if trip is collect-money, save models, log and notify driver.
     *
     * @param Trip $trip
     * @param Bid $bid
     * @param int $priceCustomer
     * @param int $priceBid
     * @param int $pickupTimestamp
     * @param Admin $admin
     * @return array
     */
    public function updateBidPrice(Trip $trip, Bid $bid, int $priceCustomer, int $priceBid, int $pickupTimestamp, Admin $admin): array
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $oldPriceCustomer = (int) $trip->price_customer;
            $oldPriceBid = (int) $trip->price_bid;
            $oldPickupTime = $trip->pickup_time;
            $adjustAmount = 0;

            $driver = Driver::findOne($bid->driver_id);
            if (!$driver instanceof Driver) {
                throw new \Exception('Không tìm thấy tài xế');
            }

            // Track driver balance before any changes
            $bid->money_before = $driver->money;

            // Adjust driver balance if trip is collect-money
            if ($trip->is_collect_money) {
                $oldDiff = max(0, $oldPriceCustomer - $oldPriceBid);
                $newDiff = max(0, $priceCustomer - $priceBid);
                $adjustAmount = $oldDiff - $newDiff;
                $driver->money = $driver->money + $adjustAmount;
                if (!$driver->save()) {
                    throw new \Exception('Không cập nhật được số dư tài xế');
                }
            }

            // Track driver balance after changes
            $bid->money_after = $driver->money;

            $trip->pickup_time = date('Y-m-d H:i:s', $pickupTimestamp);
            $trip->price_customer = (string) str_replace('.', '', $priceCustomer);
            $trip->price_bid = (string) str_replace('.', '', $priceBid);
            $trip->type_of_car = (string) $trip->type_of_car;
            $bid->price = $priceBid;
            $bid->price_customer = $priceCustomer;

            if (!$trip->save()) {
                Yii::error(['update_bid_price_trip_errors' => $trip->getErrors()], 'application');
                throw new \Exception('Không thể lưu chuyến xe: ' . json_encode($trip->getFirstErrors(), JSON_UNESCAPED_UNICODE));
            }

            if (!$bid->save()) {
                Yii::error(['update_bid_price_bid_errors' => $bid->getErrors()], 'application');
                throw new \Exception('Không thể lưu thông tin bid: ' . json_encode($bid->getFirstErrors(), JSON_UNESCAPED_UNICODE));
            }

            $content = $this->buildTripUpdateContent(
                $oldPickupTime,
                $pickupTimestamp,
                $oldPriceCustomer,
                $priceCustomer,
                $oldPriceBid,
                $priceBid
            );
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => $content,
                'action' => 'update',
            ]);

            $transaction->commit();

            $title = 'Cập nhật chuyến #' . $trip->id;

            $notificationService = new \app\services\NotificationService();
            $notificationService->sendNotificationByUsername($driver, $admin, $trip, $title, $content, '', ['trip_id' => $trip->id]);

            return ['success' => true, 'message' => 'Cập nhật thành công'];
        } catch (\Throwable $e) {
            $transaction->rollBack();

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}