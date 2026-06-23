<?php

namespace app\controllers;

use app\component\SystemConfigbie;
use app\models\SystemConfiguration;
use app\services\SystemConfigurationService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * SystemConfigurationController implements the CRUD actions for SystemConfiguration model.
 */
class SystemConfigurationController extends BaseController
{

    private SystemConfigurationService $systemConfigurationService;

    public function init()
    {
        parent::init();
        $this->systemConfigurationService = new SystemConfigurationService();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['actionCreate'],
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
     * Create or update system configuration settings.
     *
     * @return mixed The rendered view for creating/updating system configuration settings.
     * @throws Exception When encountering errors during the process.
     */
    public function actionCreate()
    {
        // Create an instance of the SystemConfigbie class
        $systemConfigbie = new SystemConfigbie();
        // Fetch the system configuration settings
        $systemList = $systemConfigbie->system();
        $request = \Yii::$app->request;

        if ($request->isPost) {
            $postData = $request->post();
            $config = $postData['config'] ?? [];

            if (! empty($config)) {
                $configData = [];

                foreach ($config as $key => $value) {
                    // Handle special case for driver_accept_car_types nested array
                    if ($key === 'driver_accept_car_types' && is_array($value)) {
                        $configData[] = [
                            'keyword' => $key,
                            'content' => json_encode($value),
                        ];
                    } elseif (is_array($value) && count($value)) {
                        $configData[] = [
                            'keyword' => $key,
                            'content' => implode(',', $value),
                        ];
                    } else {
                        $configData[] = [
                            'keyword' => $key,
                            'content' => $value,
                        ];
                    }
                }

                $transaction = \Yii::$app->db->beginTransaction();

                try {
                    // Delete all existing system configuration settings
                    SystemConfiguration::deleteAll();
                    // Batch insert the new configuration data
                    \Yii::$app->db->createCommand()->batchInsert('system_configuration', ['keyword', 'content'], $configData)->execute();
                    $transaction->commit();
                    // Log the user's action
                    Yii::$app->userLogger->logUserAction([
                        'created_on' => date('Y-m-d H:i:s'),
                        'user_id' => Yii::$app->user->id,
                        'user_name' => Yii::$app->user->identity->username,
                        'message' => Yii::t('app', 'system_configuration_update', [
                            'action' => ACTION_LIST['update'],
                        ]),
                        'action' => 'update',
                    ]);

                    return $this->redirect(['create']);
                } catch (\Exception $e) {
                    $transaction->rollBack();

                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();

                    throw $e;
                }
            }
        }

        $temp = $this->systemConfigurationService->getAllConfiguration();

        return $this->render('create', [
            'temp' => $temp,
            'systemList' => $systemList,
        ]);
    }

    /**
     * Send API get refresh_token
     * @param Trip $trip
     * @return mixed
     */
    public function actionRefreshToken()
    {
        $systemRefreshToken = SystemConfiguration::find()->where(['keyword' => 'zalo_refresh_token'])->one();
        $data = [
            'refresh_token' => $systemRefreshToken->content,
            'app_id' => APP_ID_ZNS,
            'grant_type' => 'refresh_token',
        ];

        $json_data = http_build_query($data);
        $curl = curl_init(URL_REFRESH_TOKEN_ZNS);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'secret_key: ' . ZNS_SECRET_KEY,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response, true);

        if (isset($response['access_token']) && $response['refresh_token']) {
            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $systemRefreshToken->content = $response['refresh_token'];
                $systemRefreshToken->save();

                $systemAccessToken = SystemConfiguration::find()->where(['keyword' => 'zalo_access_token'])->one();
                $systemAccessToken->content = $response['access_token'];
                $systemAccessToken->save();
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();

                throw $e;
            } catch (\Throwable $e) {
                $transaction->rollBack();

                throw $e;
            }
        }

        return json_encode($response);
    }
}
