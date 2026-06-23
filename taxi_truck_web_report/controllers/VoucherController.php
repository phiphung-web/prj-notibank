<?php

namespace app\controllers;

use app\models\Voucher;
use app\services\VoucherService;
use Yii;
use yii\data\ActiveDataProvider;

class VoucherController extends \yii\web\Controller
{
    public $voucherService;

    public function init()
    {
        parent::init();
        $this->voucherService = new VoucherService();
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Voucher::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Voucher();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Voucher::findOne($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        $model = Voucher::findOne($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Voucher::findOne($id);
        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionSearchByCode($code)
    {
        $voucher = $this->voucherService->searchByCodeAndNotUsed($code);

        if ($voucher !== null) {
            return $this->asJson($voucher);
        } else {
            return $this->asJson(['error' => 'Không tìm thấy voucher với mã ' . $code]);
        }
    }
}
