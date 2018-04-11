<?php
/**
 * Created by PhpStorm.
 * User: satish
 * Date: 4/10/2018
 * Time: 9:36 PM
 */

namespace app\modules\v1\controllers;

use app\components\AccessRule;
use app\components\MailComponent;
use app\filters\auth\HttpBearerAuth;
use app\models\User;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Json;
use yii\rest\ActiveController;
use Yii;
use yii\web\HttpException;

class CandidateController extends ActiveController
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
     * @return mixed
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionCreateCandidate()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        try {
            $transaction = Yii::$app->db->beginTransaction();
            $userModel = new User();
            $params = $request->getBodyParams();
            $data['User'] = $params;
            $userModel->user_type = User::ROLE_CANDIDATE;
            $userModel->generateAuthKey();
            $userModel->generatePasswordResetToken();
            $this->createOrUpdateUserInformation($userModel, $data);
            $response->setStatusCode(200);
            MailComponent::sendPasswordSetTokenMail($userModel);
            $result['userInformation'] = [
                "user_id" => $userModel->user_id,
                "username" => $userModel->username,
                "mobile" => $userModel->mobile,
            ];
            $transaction->commit();
            $result['message'] = "Admin User Created successfully";
            return $result;
        } catch (Exception $exception) {
            $transaction->rollBack();
            $response->setStatusCode(422);
            throw new HttpException(422, $exception->getMessage());
        }
    }

    /**
     * @param User $userModel
     * @param $data
     * @return bool
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function createOrUpdateUserInformation($userModel, $data)
    {
        if ($userModel->load($data)) {
            if ($userModel->isNewRecord) {
                $userModel->is_active = 0;
                $userModel->is_verified = 0;
                $userModel->generatePasswordResetToken();
            }
            try {
                $transaction = Yii::$app->db->beginTransaction();
                if ($userModel->validate() && $userModel->save()) {
                    $transaction->commit();
                    return true;
                }
                $transaction->rollBack();
                throw new Exception(Json::encode($userModel->getErrors()), 1);
            } catch (Exception $exception) {
                $transaction->rollBack();
                throw new Exception($exception->getMessage(), 1);
            }
        }
        throw new Exception(Json::encode("Error in loading User Model"), 1);
    }
}