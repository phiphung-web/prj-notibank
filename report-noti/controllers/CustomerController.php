<?php

namespace app\controllers;

use app\models\Customer;
use app\models\SearchTrip;
use app\services\CustomerService;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Request;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    protected $customerService;

    public function init()
    {
        parent::init();
        $this->customerService = new CustomerService();
    }

    /**
     * Lists all Customer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Customer();
        if (isset($_GET['Customer']['vip']) && $_GET['Customer']['vip']) {
            $dataProvider = $model->searchVip(Yii::$app->request->queryParams);
        } else {
            $dataProvider = $model->search(Yii::$app->request->queryParams);
        }

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Customer model.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $searchTrip = new SearchTrip();
        $customer = $this->findModel($id);

        return $this->render('view', [
            'model' => $customer,
            'tripList' => $searchTrip->customerSearchHistoryTrip($customer)->getModels(),
        ]);
    }

    /**
     * Creates a new Customer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    /*public function actionCreate()
    {
        $model = new Customer();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }*/

    /**
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Customer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Customer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Customer::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionDbCustomer()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return $this->customerService->getStatisticCustomer();
    }
}
