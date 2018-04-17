<?php
/**
 * Created by PhpStorm.
 * User: satish
 * Date: 4/10/2018
 * Time: 9:36 PM
 */

namespace app\modules\v1\controllers;

use app\components\AccessRule;
use app\filters\auth\HttpBearerAuth;
use app\models\EvaluationQuestions;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\rest\ActiveController;
use Yii;
use yii\web\HttpException;

class EvaluationController extends ActiveController
{
    public $modelClass = 'app\models\User';

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
                'get-candidate-evaluation' => ['post'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete',], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'get-candidate-evaluation'],
                    // 'roles' => [USER::ROLE_SUPER_ADMIN, USER::ROLE_ADMIN],
                ]
            ],
        ];

        return $behaviors;
    }

    /*
     *
     */
    public function actionGetCandidateEvaluation()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        try {
            $postParams = $request->getBodyParams();
            $candidateId = $postParams['candidateId'];
            $evaluationType = $postParams['evaluationType'];
            if ($evaluationType == 'start') {
                $questions = EvaluationQuestions::find()->asArray()->all();
                $categoryBasedGroups = ArrayHelper::index($questions, null, ['category_id', 'sub_category_id']);
                $categoryId = 1;
                $finalArray = [];
                foreach ($categoryBasedGroups as $category) {
                    foreach ( $category as $key =>$items) {
                        ${'subcategories_' . $categoryId} = ArrayHelper::getColumn($items,function($element){
                            $array['id'] = $element['question_id'];
                            $array['questionText'] = $element['question_text'];
                            return $array;
                        });
                        $finalArray['subcategories_' . $categoryId] = ${'subcategories_' . $categoryId};
                        $categoryId++;
                    }
                }
                $result['questions'] = $finalArray;
                $result['status'] = true;
                return $result;
            }
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, $exception->getMessage());
        }
    }

}