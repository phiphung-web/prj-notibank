<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\helpers\ResponseHelper;
use app\models\Admin;
use app\models\debt\TripAgency;
use app\models\Revenue;
use app\models\SearchRevAgency;
use app\services\RevenueService;
use ErrorException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\web\Response;

class RevenueController extends BaseController
{
    private $revenueService;

    public function init()
    {
        parent::init();
        $this->revenueService = new RevenueService();
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays revenue information for a specific time range.
     *
     * @return string The rendered view for revenue information.
     */
    public function actionIndex()
    {
        $searchModel = new Revenue();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');
        $dataProvider = $searchModel->searchRev(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $models,
            'totalRevenue' => $this->revenueService->calculateTotals($models),
            'timeRange' => $this->revenueService->getTimeRanges(),
        ]);
    }

    public function actionDetailDate()
    {
        try {
            $searchModel = new Revenue();
            $data = $searchModel->searchDetailByTime(Yii::$app->request->queryParams);

            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', $data);
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('PayTransactionController - actionMinusSystem() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    /**
     * Displays revenue information for payments made within the current month.
     *
     * @return string The rendered view for revenue information.
     */
    public function actionPay()
    {
        $searchModel = new Revenue();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');

        $dataProvider = $searchModel->searchPay(Yii::$app->request->queryParams);

        return $this->render('pay', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays Zalo group statistics for the current month.
     *
     * @return string The rendered view for Zalo group statistics.
     */
    public function actionZalo()
    {
        $searchModel = new \app\models\SearchGroupZalo([
            'pickupTimeRange' => date('Y-m-01') . ' - ' . date('Y-m-t'),
        ]);
        $zalo = $searchModel->search_statistic(Yii::$app->request->queryParams);

        return $this->render('index_zalo', [
            'searchModel' => $searchModel,
            'dataProvider' => $zalo,
        ]);
    }

    public function actionZaloExport()
    {
        $searchModel = new \app\models\SearchGroupZalo([
            'pickupTimeRange' => date('Y-m-01') . ' - ' . date('Y-m-t'),
        ]);
        $zalo = $searchModel->search_statistic(Yii::$app->request->queryParams);
        $zalo->pagination->pageSize = 0;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Nguồn Zalo bán');
        $sheet->setCellValue('B1', 'Nhóm Zalo');
        $sheet->setCellValue('C1', 'Chú thích');
        $sheet->setCellValue('D1', 'Tiền cước');
        $sheet->setCellValue('E1', 'Điểm');

        $row = 2;
        foreach ($zalo->getModels() as $model) {
            $sheet->setCellValue('A' . $row, $model->name);
            $sheet->setCellValue('B' . $row, $model->group_zalo_catalogue_name);
            $sheet->setCellValue('C' . $row, $model->note);
            $sheet->setCellValue('D' . $row, $model->money);
            $sheet->setCellValue('E' . $row, $model->point);

            $row++;
        }
        $filename = 'zalo_export_' . date('Y-m-d') . '.xlsx';
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function actionUserStatistic()
    {
        $searchModel = new Revenue();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');

        $dataProvider = $searchModel->searchUserStatistic(Yii::$app->request->queryParams);

        return $this->render('user_statistic', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // rev agency
    public function actionAgency()
    {
        $admin = Admin::findOne(Yii::$app->user->identity->id);
        $modelSearchRevAgency = new SearchRevAgency();
        $modelSearchRevAgency->id = (! empty($admin->agency_id) ? $admin->agency_id : 0);
        $dataProvider = $modelSearchRevAgency->search(Yii::$app->request->queryParams);
        $agencyList = $this->revenueService->convertListAgency($dataProvider->getModels());

        return $this->render('agency/index', [
            'searchModel' => $modelSearchRevAgency,
            'agencyList' => $agencyList,
            'dataProvider' => $dataProvider,
            'admin' => $admin,
        ]);
    }

    public function actionAgencyDetail()
    {
        try {
            $searchModel = new TripAgency();
            $id = Yii::$app->request->get('id');
            $time = Yii::$app->request->get('time');

            return $this->renderPartial('agency/detail', ['tripList' => $searchModel->searchTripDebt($id, $time), 'agency_id' => $id]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    // rev booking
    public function actionBooking()
    {
        $searchModel = new Revenue();
        $searchModel->createTimeRange = date('Y-m-01') . ' - ' . date('Y-m-d');
        $reasonReject = $searchModel->reasonReject();
        $revenueList = $searchModel->searchRevBooking(Yii::$app->request->queryParams);
        $statisticSchedule = $searchModel->searchRevSchedule(Yii::$app->request->queryParams);
        $statisticRoom = $searchModel->searchRevGroupZalo(Yii::$app->request->queryParams);
        $statisticCustomerRollback = $searchModel->searchRevCustomerRollback(Yii::$app->request->queryParams);

        return $this->render('booking/index', [
            'total' => $this->revenueService->initializeTotalArray(),
            'totalSource' => $this->revenueService->initializeTotalSourceArray(),
            'totalMailSource' => $this->revenueService->initializeMailSource(),
            'searchModel' => $searchModel,
            'revenueList' => $revenueList,
            'statisticSchedule' => $statisticSchedule,
            'statisticCustomerRollback' => $statisticCustomerRollback,
            'statisticRoom' => $statisticRoom,
            'reasonReject' => $reasonReject,
        ]);
    }

    public function actionGetDataStatus()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params = Yii::$app->request->queryParams;
            list($dateStart, $dateEnd) = explode(' - ', $params['range']);

            return [
                'data' => $this->revenueService->getDataStatus($dateStart, $dateEnd),
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('RevenueController - actionGetDataStatus() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionGetRevenueAndExpenditureData()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params = Yii::$app->request->queryParams;
            list($dateStart, $dateEnd) = explode(' - ', $params['range']);

            return [
                'data' => $this->revenueService->getRevenueAndExpenditureData($dateStart, $dateEnd),
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('RevenueController - actionGetRevenueAndExpenditureData() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionGetDataSource()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params = Yii::$app->request->queryParams;
            list($dateStart, $dateEnd) = explode(' - ', $params['range']);

            return [
                'data' => $this->revenueService->getDataSource($dateStart, $dateEnd),
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('RevenueController - actionGetDataSource() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionUpdatePrice()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $params = Yii::$app->request->post();

            return [
                'status' => Yii::$app->db->createCommand()
                    ->update('summary_report', ['spend_price' => str_replace('.', '', $params['price'])], ['dt_date' => $params['date']])
                    ->execute(),
                'price' => str_replace('.', '', $params['price']),
            ];
        } catch (ErrorException $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('RevenueController - actionUpdatePrice() - ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
        }
    }

    public function actionDriverNews()
    {
        return $this->render('driver_news', [
            'dataProvider' => $this->revenueService->getNewDriverFirstDeposit(Yii::$app->request->queryParams),
        ]);
    }
}
