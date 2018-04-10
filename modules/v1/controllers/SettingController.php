<?php
namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\components\AccessRule;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\Exception;
use app\models\SettingMaster;
use app\models\User;

/**
 * Class Setting
 * Controller file is used to perform all the operations regarding settings.
 * - Fetching.
 */
class SettingController extends ActiveController
{

    public $modelClass = 'app\models\SettingMaster';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['delete'], $actions['create'], $actions['update']);
        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className()
            ]
        ];
        
        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => [
                    'get'
                ],
                'view' => [
                    'get'
                ],
                'create' => [
                    'post'
                ],
                'update' => [
                    'put'
                ],
                'delete' => [
                    'delete'
                ]
            ]
        ];
        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => [
                    '*'
                ],
                'Access-Control-Request-Method' => [
                    'GET',
                    'POST',
                    'PUT',
                    'DELETE',
                    'OPTIONS'
                ],
                'Access-Control-Request-Headers' => [
                    '*'
                ],
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 86400,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => []
                // 'Access-Control-Request-Headers' => ['Origin', 'X-Requested-With', 'Content-Type', 'accept', 'Authorization'],
            ]
        ];
        
        $behaviors['authenticator']['except'] = [
            'options'
        ];
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className()
            ],
            'only' => [
                'index',
                'view',
                'create',
                'update',
                'delete'
            ], // only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index',
                        'view',
                        'create',
                        'update',
                        'delete'
                    ],
                    'roles' => [
                        USER::ROLE_SUPER_ADMIN,
                        USER::ROLE_ADMIN
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => [
                        'index'
                    ],
                    'roles' => [
                        USER::ROLE_CLIENT_USER,
                        USER::ROLE_COMPANY_USER
                    ]
                ]
            ]
        ];
        
        return $behaviors;
    }

    /**
     * Returns List of available settings
     *
     * @method actionIndex
     * @return [type] [description]
     */
    public function actionIndex()
    {
        try {
            $response = Yii::$app->response;
            $response->setStatusCode(200);
            return SettingMaster::getList();
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, Json::encode($exception->getMessage()));
        }
    }

    /**
     * Update Setting value
     *
     * @method actionIndex
     * @return [type] [description]
     */
    public function actionUpdate($id)
    {
        try {
            $response = Yii::$app->response;
            $request = Yii::$app->request;
            $response->setStatusCode(200);
            $params = $request->getBodyParams();
            if (isset($params['setting_revised_value'])) {
                $setting = $this->findModel($id);
                $setting->setting_value = $params['setting_revised_value'];
                if ($setting->setting_field_type == 'date') {
                    $date = new \DateTime($setting->setting_value);
                    $setting->setting_value = $date->format('m/d/Y');
                }
                if (! ($setting->validate() && $setting->save())) {
                    throw new HttpException(422, Json::encode($setting->errors));
                }
            }
            return true;
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, Json::encode($exception->getMessage()));
        }
    }

    /**
     * Finds the model and returns model
     *
     * @method findModel
     * @param [type] $id
     *            Primary key of Settigns
     * @return [type] string/ActiveRecord
     */
    protected function findModel($id)
    {
        if (($model = SettingMaster::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
