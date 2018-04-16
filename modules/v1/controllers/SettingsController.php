<?php


namespace app\modules\v1\controllers;

use app\components\AccessRule;
use app\components\MailComponent;
use app\filters\auth\HttpBearerAuth;
use app\models\SettingMaster;
use app\models\User;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\rest\ActiveController;
use Yii;
use yii\web\HttpException;

class SettingsController extends ActiveController
{
    public $modelClass = 'app\models\SettingMaster';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post'],
                'delete-candidate' => ['delete'],
            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];


        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'ruleConfig' => [
                'class' => AccessRule::className(),
            ],
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    // 'roles' => [USER::ROLE_SUPER_ADMIN, USER::ROLE_ADMIN],
                ]
            ],
        ];

        return $behaviors;
    }

    /**
     *
     */
    public function actionGetSettings()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        try {
            $settings = SettingMaster::findOne(1);
            /*$categoriesNames = [];
            $settingsCategories = array_filter(ArrayHelper::getColumn($settings, function ($element) use (&$categoriesNames) {
                if (!in_array($element['setting_category_name'], $categoriesNames)) {
                    $ele['name'] = $element['setting_category_name'];
                    $ele['catame'] = str_replace(' ', '_', $element['setting_category_name']);
                    array_push($categoriesNames, $element['setting_category_name']);
                    return $ele;
                }
            }
            ));

            $settings = ArrayHelper::index($settings, null, ['setting_category_name', 'setting_sub_category_name']);
*/
            $return['status'] = true;
            $return['settings'] = $settings;
            $response->setStatusCode(200);
            return $return;
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, $exception->getMessage());
        }
    }

    /**
     *
     */
    public function actionSaveSettings()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        try {
            $postData = $request->getBodyParams();
            $data['SettingMaster'] = $postData;
            $settings = SettingMaster::findOne(1);
            $settings->load($data);
            $settings->save();
            $return['status'] = true;
            $response->setStatusCode(200);
            return $return;
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, $exception->getMessage());
        }
    }
}