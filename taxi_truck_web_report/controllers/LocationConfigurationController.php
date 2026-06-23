<?php

namespace app\controllers;

use app\helpers\MyHelper;
use app\helpers\ResponseHelper;
use app\models\Location;
use app\services\LocationConfigurationService;
use Yii;
use yii\base\Exception;

/**
 * TripController implements the CRUD actions for Trip model.
 */
class LocationConfigurationController extends BaseController
{
    protected $locationConfigurationService;

    public function init()
    {
        parent::init();
        $this->locationConfigurationService = new LocationConfigurationService();
    }

    public function actionSearch()
    {
        try {
            $html = '';

            // Lấy địa chỉ từ DB
            $searchModel = new Location();
            $keyword = Yii::$app->request->queryParams;
            $dataProvider = $searchModel->getListLocation(isset($keyword['Location']) ? $keyword['Location'] : $keyword);
            if ($dataProvider) {
                foreach ($dataProvider as $value) {
                    $html .= $this->locationConfigurationService->renderListLocation($value);
                }
            }

            // Lấy địa chỉ từ API
            // $queryParam = [
            //   'q' => Yii::$app->request->get('keyword') . ' Hà Nội'
            // ];
            // $httpClient = new \yii\httpclient\Client();
            // $response = $httpClient->createRequest()
            //   ->setMethod('GET')
            //   ->setUrl(API_SEARCH_ADDRESS . 'search')
            //   ->setData($queryParam)
            //   ->send();

            // if ($response->isOk) {
            //   $result = $response->getData();
            //   if (isset($result) && is_array($result) && count($result)) {
            //     foreach ($result as $value) {
            //       $html .= $this->locationConfigurationService->renderListLocation($value);
            //     }
            //   }
            // }
            return ResponseHelper::renderResponse(200, 'Lấy dữ liệu thành công!', ['html' => $html]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('LocationConfigurationController - actionSearch() - ' . $e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        try {
            $searchModel = new Location();
            $keyword = Yii::$app->request->queryParams;
            $dataProvider = $searchModel->getListLocation(isset($keyword['Location']) ? $keyword['Location'] : '');

            return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
      ]);
        } catch (Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('LocationConfigurationController - actionIndex() - ' . $e->getMessage());
        }
    }

    public function actionCreate()
    {
        $model = new Location();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $model->latitude = (float) Yii::$app->request->post()['latitude'];
            $model->longitude = (float) Yii::$app->request->post()['longitude'];
            $model->display_name = Yii::$app->request->post()['display_name'];
            if ($model->save()) {
                $transaction->commit();

                return json_encode(['status' => 'success', 'message' => 'Thêm thành công.']);
            } else {
                $transaction->rollBack();

                return json_encode(['status' => 'error', 'message' => 'Thêm thất bại.']);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('LocationConfigurationController - actionCreate() - ' . $e->getMessage());
        }
    }

    /**
     * @return false|string
     */
    public function actionUpdate()
    {
        try {
            $dataToUpdate = Yii::$app->request->post('data');
            foreach ($dataToUpdate as $data) {
                $id = $data['id'];
                $model = Location::findOne($id);
                $model->latitude = $data['latitude'];
                $model->longitude = $data['longitude'];
                $model->display_name = $data['display_name'];
                $model->save();
            }

            return json_encode(['status' => 'success', 'message' => 'Cập nhật thành công.']);
        } catch (\Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('LocationConfigurationController - actionUpdate() - ' . $e->getMessage());
        }
    }

    public function actionDelete()
    {
        try {
            $id = Yii::$app->request->post('id');
            if ($id !== null) {
                $location = Location::findOne($id);
                if ($location !== null) {
                    if ($location->delete()) {
                        return json_encode(['status' => 'success', 'message' => 'Xóa thành công.']);
                    } else {
                        return json_encode(['status' => 'error', 'message' => 'Xóa thất bại.']);
                    }
                }
            }

            return $this->redirect(['index']);
        } catch (\Exception $e) {
            Yii::error('Error occurred: ' . $e->getMessage(), 'application');
            MyHelper::sendErrorToTelegramBot('LocationConfigurationController - actionDelete() - ' . $e->getMessage());
        }
    }
}
