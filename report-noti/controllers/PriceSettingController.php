<?php

namespace app\controllers;

use app\models\PriceSetting;
use app\services\PriceSettingService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PriceSettingController extends Controller
{
    private $priceSettingService;

    public function __construct($id, $module, $config = [])
    {
        $this->priceSettingService = new PriceSettingService();
        parent::__construct($id, $module, $config);
    }

    /**
     * Trang danh sách
     */
    public function actionIndex()
    {
        $agencyId = Yii::$app->request->get('agency_id', null);
        $sort = Yii::$app->request->get('sort', 'id');
        $dataProvider = $this->priceSettingService->getPaginatedData(20, $agencyId, $sort);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Trang xem chi tiết
     */
    public function actionView($id)
    {
        $model = $this->priceSettingService->getById($id);
        if (! $model) {
            throw new NotFoundHttpException('Không tìm thấy bản ghi');
        }

        return $this->render('view', ['model' => $model]);
    }

    /**
     * Trang thêm mới
     */
    public function actionCreate()
    {
        $model = new PriceSetting();
        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post('PriceSetting');
            if ($this->priceSettingService->processAndSave($model, $postData)) {
                Yii::$app->session->setFlash('success', 'Thêm mới thành công!');

                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Đã xảy ra lỗi khi lưu dữ liệu.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Trang cập nhật
     */
    public function actionUpdate($id)
    {
        $model = $this->priceSettingService->getById($id);
        if (! $model) {
            throw new NotFoundHttpException('Không tìm thấy bản ghi');
        }

        if ($model->load(Yii::$app->request->post())) {
            $postData = Yii::$app->request->post('PriceSetting');
            if ($this->priceSettingService->processAndSave($model, $postData)) {
                Yii::$app->session->setFlash('success', 'Cập nhật thành công!');

                return $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('error', 'Đã xảy ra lỗi khi lưu dữ liệu.');
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Xóa bản ghi
     */
    public function actionDelete($id)
    {
        $this->priceSettingService->delete($id);

        return $this->redirect(['index']);
    }
}
