<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\models\CalculationFormula;
use app\models\ConfigAutoSale;
use app\models\IncreasePrice;
use app\services\CalculationFormulaService;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Yii;

/**
 * TripController implements the CRUD actions for Trip model.
 */
class CalculationFormulaController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public $calculationFormulaService;

    public function init()
    {
        parent::init();
        $this->calculationFormulaService = new CalculationFormulaService();
    }

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
                'actions' => [],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        try {
            $searchModel = new CalculationFormula();
            $dataProvider = $searchModel->getListCalculationFormulas();

            return $this->render('index', [
                'dataProvider' => $dataProvider,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }

        return $this->render('index');
    }

    /**
     * @return false|string
     */
    public function actionUpdate()
    {
        $dataToUpdate = Yii::$app->request->post('CalculationFormula');
        CalculationFormula::deleteAll();
        $calculationFormula = $this->storeCalculationFormula($dataToUpdate);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            Yii::$app->db->createCommand()->batchInsert('calculation_formula', ['type_of_car', 'schedule', 'km_start', 'km_end', 'price', 'price_by_km', 'surcharge', 'price_wait', 'overnight_fee', 'description'], $calculationFormula)->execute();

            $transaction->commit();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'formula_update'),
                'action' => 'update',
            ]);

            return $this->redirect(Yii::$app->request->referrer);
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    public function actionFindPrice()
    {
        $params = Yii::$app->request->get();

        return json_encode(['price' => $this->calculationFormulaService->calculateNormal($params)]);
    }

    public function storeCalculationFormula($data)
    {
        $calculationFormular = [];
        if (isset($data['type_of_car']) && is_array($data['type_of_car']) && count($data['type_of_car'])) {
            foreach ($data['type_of_car'] as $key => $value) {
                foreach ($data as $keyData => $valueData) {
                    if ($keyData == 'price' || $keyData == 'price_by_km' || $keyData == 'km_end' || $keyData == 'km_start' || $keyData == 'surcharge' || $keyData == 'price_wait' || $keyData == 'overnight_fee') {
                        $calculationFormular[$key][$keyData] = (int) str_replace('.', '', $data[$keyData][$key]);
                    } elseif ($keyData == 'time_start' || $keyData == 'time_end') {
                        $calculationFormular[$key][$keyData] = date('H:i', strtotime($data[$keyData][$key]));
                    } else {
                        $calculationFormular[$key][$keyData] = $data[$keyData][$key];
                    }
                }


            $kmStart = (float)($calculationFormular[$key]['km_start'] ?? 0);
            $kmEnd   = (float)($calculationFormular[$key]['km_end']   ?? 0);
            $T       = CalculationFormula::SCHEDULE_THRESHOLD_KM;

            if ($kmEnd == 0) {
                $schedule = ($kmStart > $T) ? 2 : 1;
            } else {
                $schedule = (max($kmStart, $kmEnd) > $T) ? 2 : 1;
            }
            $calculationFormular[$key]['schedule'] = $schedule;

            if (!isset($calculationFormular[$key]['description']) || $calculationFormular[$key]['description'] === null) {
                $calculationFormular[$key]['description'] = '';
            }
        }
    }

    // --- Normalize & build sequential array with correct column order for batchInsert ---
    $rows = [];
    foreach ($calculationFormular as $row) {
        foreach (['km_start','km_end','price','price_by_km','surcharge','price_wait','overnight_fee'] as $numField) {
            if (!isset($row[$numField]) || $row[$numField] === '' || $row[$numField] === null) {
                $row[$numField] = CalculationFormula::DEFAULT_KM;
            }
            $row[$numField] = (int)$row[$numField];
        }

        $rows[] = [
            (int)($row['type_of_car']    ?? CalculationFormula::DEFAULT_TYPE_OF_CAR),
            (int)($row['schedule']       ?? CalculationFormula::SCHEDULE_URBAN),
            (int)($row['km_start']       ?? CalculationFormula::DEFAULT_KM),
            (int)($row['km_end']         ?? CalculationFormula::DEFAULT_KM),
            (int)($row['price']          ?? CalculationFormula::DEFAULT_PRICE),
            (int)($row['price_by_km']    ?? CalculationFormula::DEFAULT_PRICE_BY_KM),
            (int)($row['surcharge']      ?? CalculationFormula::DEFAULT_SURCHARGE),
            (int)($row['price_wait']     ?? CalculationFormula::DEFAULT_PRICE_WAIT),
            (int)($row['overnight_fee']  ?? CalculationFormula::DEFAULT_OVERNIGHT_FEE),
            (string)($row['description'] ?? CalculationFormula::DEFAULT_DESCRIPTION),
        ];
    }

    return $rows;
    }

    public function actionAutoIncreasePrice()
    {
        try {
            $dataProvider = IncreasePrice::find()->all();

            return $this->render('increase_price', [
                'dataProvider' => $dataProvider,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }

        return $this->render('increase_price');
    }

    public function storeIncreasePrice($data)
    {
        $increasePrice = [];
        if (isset($data['type_of_car']) && is_array($data['type_of_car']) && count($data['type_of_car'])) {
            foreach ($data['type_of_car'] as $key => $value) {
                foreach ($data as $keyData => $valueData) {
                    if ($keyData == 'minute_before' || $keyData == 'price_increase') {
                        $increasePrice[$key][$keyData] = (int) str_replace('.', '', $data[$keyData][$key]);
                    } else {
                        $increasePrice[$key][$keyData] = $data[$keyData][$key];
                    }
                }
            }
        }

        return $increasePrice;
    }

    public function actionUpdateIncreasePrice()
    {
        $dataToUpdate = Yii::$app->request->post('IncreasePrice');
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            IncreasePrice::deleteAll();
            $increasePrice = $this->storeIncreasePrice($dataToUpdate);
            Yii::$app->db->createCommand()->batchInsert('increase_price', ['type_of_car', 'minute_before', 'price_increase'], $increasePrice)->execute();
            $transaction->commit();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'increase_price_update'),
                'action' => 'update',
            ]);

            return $this->redirect(Yii::$app->request->referrer);
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    public function actionConfigAutoSale()
    {
        try {
            $dataProvider = ConfigAutoSale::find()->all();

            return $this->render('config_auto_sale', [
                'dataProvider' => $dataProvider,
            ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot($e->getMessage());
        }

        return $this->render('config_auto_sale');
    }

    public function storeConfigAutoSale($data)
    {
        $configAutoSale = [];
        if (isset($data['type_of_car']) && is_array($data['type_of_car']) && count($data['type_of_car'])) {
            foreach ($data['type_of_car'] as $key => $value) {
                foreach ($data as $keyData => $valueData) {
                    if ($keyData == 'schedule' || $keyData == 'price') {
                        $configAutoSale[$key][$keyData] = (int) str_replace('.', '', $data[$keyData][$key]);
                    } else {
                        $configAutoSale[$key][$keyData] = $data[$keyData][$key];
                    }
                }
            }
        }

        return $configAutoSale;
    }

    public function actionUpdateConfigAutoSale()
    {
        $dataToUpdate = Yii::$app->request->post('ConfigAutoSale');
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            ConfigAutoSale::deleteAll();
            $configAutoSale = $this->storeConfigAutoSale($dataToUpdate);
            Yii::$app->db->createCommand()->batchInsert('config_auto_sale', ['type_of_car', 'from_time', 'to_time', 'schedule', 'price'], $configAutoSale)->execute();
            $transaction->commit();
            Yii::$app->userLogger->logUserAction([
                'created_on' => date('Y-m-d H:i:s'),
                'user_id' => Yii::$app->user->id,
                'user_name' => Yii::$app->user->identity->username,
                'message' => Yii::t('app', 'config_auto_sale_update'),
                'action' => 'update',
            ]);

            return $this->redirect(Yii::$app->request->referrer);
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }
}
