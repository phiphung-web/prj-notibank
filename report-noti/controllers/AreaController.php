<?php

namespace app\controllers;

use app\models\Area;
use app\models\AreaRelationship;
use app\models\SearchArea;
use app\services\AreaService;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class AreaController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    protected $areaService;

    public function __construct($id, $module, AreaService $areaService, $config = [])
    {
        $this->areaService = $areaService;
        parent::__construct($id, $module, $config);
    }

    public function actionIndex()
    {
        $model = new SearchArea();
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGetLocation()
    {
        $post = Yii::$app->request->post()['param'];
        $query = new Query();
        $query->select($post['select'])
            ->from($post['table'])
            ->where($post['where'])
            ->orderBy('name asc');
        $rows = $query->all();
        $html = '<option value="0">' . $post['text'] . '</option>';
        if (isset($rows) && is_array($rows) && count($rows)) {
            foreach ($rows as $val) {
                $html .= '<option value="' . $val['id'] . '">' . $val['name'] . '</option>';
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        return ['html' => $html];
    }

    public function actionCreate()
    {
        $model = new Area();
        $areaClone = $this->areaService->getPriceListClone(Yii::$app->request->get());
        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $priceList = $this->areaService->priceListArea(Yii::$app->request->post('area_relationship'));
                $model->price_list = json_encode($priceList);
                $area = $this->areaService->save($model);
                if ($area->id > 0) {
                    AreaRelationship::deleteAll(['area_id' => $area->id]);
                    $areaRelationship = $this->areaService->storeAreaRelationship($area, $priceList);
                    Yii::$app->db->createCommand()->batchInsert('area_relationship', ['street', 'area_id', 'districtid', 'provinceid', 'type_of_car', 'time', 'schedule', 'price', 'roundtrip_price', 'description', 'address'], $areaRelationship)->execute();
                }
                $transaction->commit();

                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'area_cd', [
                        'id' => $model->id,
                        'area_name' => $model->area_name,
                        'action' => ACTION_LIST['create'],
                    ]),
                    'action' => 'create',
                ]);
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }

            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
            'areaClone' => $areaClone,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->areaService->getById($id);

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $priceList = $this->areaService->priceListArea(Yii::$app->request->post('area_relationship'));
                $model->price_list = json_encode($priceList);
                $area = $this->areaService->save($model);
                if ($area->id > 0) {
                    AreaRelationship::deleteAll(['area_id' => $area->id]);
                    $areaRelationship = $this->areaService->storeAreaRelationship($area, $priceList);
                    Yii::$app->db->createCommand()->batchInsert('area_relationship', ['street', 'area_id', 'districtid', 'provinceid', 'type_of_car', 'time', 'schedule', 'price', 'roundtrip_price', 'description', 'address'], $areaRelationship)->execute();
                }
                $transaction->commit();
                Yii::$app->userLogger->logUserAction([
                    'created_on' => date('Y-m-d H:i:s'),
                    'user_id' => Yii::$app->user->id,
                    'user_name' => Yii::$app->user->identity->username,
                    'message' => Yii::t('app', 'area_update', [
                        'id' => $model->id,
                        'area_name' => $model->area_name,
                    ]),
                    'action' => 'update',
                ]);
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }

            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete()
    {
        $getData = Yii::$app->request->get();

        if (isset($getData['id'])) {
            $area = Area::findOne($getData['id']);
            $area_id = $area->id;
            $area_name = $area->area_name;
            if ($area) {
                $area->delete();
                AreaRelationship::deleteAll(['area_id' => $getData['id']]);
            }
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'area_cd', [
                    'id' => $area_id,
                    'area_name' => $area_name,
                    'action' => ACTION_LIST['delete'],
                ]),
                'action' => 'delete',
            ]);
        }

        return $this->redirect(['index']);
    }
}
