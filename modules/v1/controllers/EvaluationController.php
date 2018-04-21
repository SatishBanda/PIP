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
use app\components\ResourceComponent;
use app\filters\auth\HttpBearerAuth;
use app\models\CandidateEmails;
use app\models\CandidateEvaluations;
use app\models\CandidateQuestionsRating;
use app\models\EvaluationQuestions;
use app\models\EvaluationQuestionsSubCategories;
use app\models\SettingMaster;
use Mpdf\Tag\A;
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
                'save-evaluations' => ['post'],
                'send-final-email' => ['post'],
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
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'get-candidate-evaluation', 'save-evaluations', 'send-final-email'],
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
            $evaluation = CandidateEvaluations::find()->joinWith(['questions'])->where([CandidateEvaluations::tableName() . '.user_id' => $candidateId, 'status' => 1])->asArray()->one();
            $questionsRating = [];
            if ($evaluation) {
                $questions = $evaluation['questions'];
                $questionsRating = ArrayHelper::map($questions, 'question_id', 'rating_value');
            }
            // if ($evaluationType == 'start') {
            $questions = EvaluationQuestions::find()->asArray()->all();
            $categoryBasedGroups = ArrayHelper::index($questions, null, ['category_id', 'sub_category_id']);
            $categoryId = 1;
            $finalArray = [];
            foreach ($categoryBasedGroups as $category) {
                foreach ($category as $key => $items) {
                    ${'subcategories_' . $categoryId} = ArrayHelper::getColumn($items, function ($element) use ($categoryId, $questionsRating) {
                        $array['id'] = $element['question_id'];
                        $array['questionText'] = $element['question_text'];
                        $array['questionValue'] = isset($questionsRating[$element['question_id']]) ? $questionsRating[$element['question_id']] : 0;
                        return $array;
                    });
                    $finalArray['subcategories_' . $categoryId] = ${'subcategories_' . $categoryId};
                    $categoryId++;
                }
            }
            $result['questions'] = $finalArray;
            $result['status'] = true;
            return $result;
            // }
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, $exception->getMessage());
        }
    }

    /**
     * @return mixed
     * @throws HttpException
     */
    public function actionSaveEvaluations()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        try {
            $postParams = $request->getBodyParams();
            $candidateId = $postParams['candidateId'];
            $questions = $postParams['questions'];
            $step = $postParams['step'];
            $submitType = isset($postParams['type']) ? $postParams['type'] : '';
            $result = [];
            if ($questions) {
                $transaction = Yii::$app->db->beginTransaction();
                try {

                    $evaluationId = $this->saveEvaluationMain($candidateId, $step, $submitType);
                    $this->saveEvaluationQuestions($candidateId, $evaluationId, $questions, $step);

                    if (in_array($submitType, ['finish', 'email'])) {
                        $validationStatusResponse = $this->validateEvaluationQuestion($candidateId, $evaluationId);

                        if ($validationStatusResponse['status']) {
                            $this->saveEvaluationMain($candidateId, $step, $submitType, true);
                            $result['status'] = true;
                        } else {
                            $result['validationFailed'] = true;
                            $result['failedTabs'] = $validationStatusResponse['failedTabs'];
                            $result['errorMessage'] = $validationStatusResponse['failedErrorMessage'];
                            $result['failedSubTabs'] = $validationStatusResponse['failedSubTabs'];
                            $result['status'] = false;
                        }
                    } else {
                        $result['status'] = true;
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                    $result['status'] = false;
                    $result['message'] = $e->getMessage();
                }
                $response->setStatusCode(200);
                $transaction->commit();
                return $result;
            }
        } catch (Exception $exception) {
            $response->setStatusCode(422);
            throw new HttpException(422, $exception->getMessage());
        }
    }

    /**
     * @param $candidateId
     */
    public function saveEvaluationMain($candidateId, $step, $submitType, $finalSave = false)
    {
        $evaluation = CandidateEvaluations::find()->where(['user_id' => $candidateId, 'status' => 1])->one();
        if (!$evaluation) {
            $evaluation = new CandidateEvaluations();
            $evaluation->user_id = $candidateId;
        }
        $evaluation->status = 1;
        $evaluation->evaluator_id = Yii::$app->user->identity->id;
        if ($evaluation->status != 2) {
            $evaluation->save();
        }
        if ($finalSave) {
            $evaluation->status = 2;
            $evaluation->save();
        }
        return $evaluation->evaluation_id;
    }

    /**
     * @param $evaluationId
     * @param $questions
     * @param $step
     */
    public function saveEvaluationQuestions($candidateId, $evaluationId, $questions, $step)
    {
        $questionIds = ArrayHelper::getColumn($questions, 'id');

        if ($questionIds) {
            CandidateQuestionsRating::deleteAll(['user_id' => $candidateId, 'evaluation_id' => $evaluationId, 'question_id' => $questionIds]);
        }

        $rows = [];
        $date = date('Y-m-d H:i:s');
        $userId = Yii::$app->user->identity->id;
        foreach ($questions as $question) {
            $rows[] = [$candidateId, $evaluationId, $question['id'], $question['questionValue'], $date, $userId, $date, $userId];
        }
        $columns = ['user_id', 'evaluation_id', 'question_id', 'rating_value', 'created_at', 'created_by', 'updated_at', 'updated_by'];

        Yii::$app->db->createCommand()->batchInsert(CandidateQuestionsRating::tableName(), $columns, $rows)->execute();
    }

    /**
     * @param $candidateId
     * @param $evaluationId
     */
    public function validateEvaluationQuestion($candidateId, $evaluationId)
    {
        $result['status'] = false;

        $ratings = CandidateQuestionsRating::find()->where(['user_id' => $candidateId, 'evaluation_id' => $evaluationId])->asArray()->all();
        if (!$ratings) {
            $result['allTabsFailed'] = true;
            $result['failedErrorMessage'] = "Please answer all the questions in following sections : Introduction,Questions,Recommendations,Feedback";
            return $result;
        }

        $ratings = ArrayHelper::map($ratings, 'question_id', 'rating_value');
        $ratings = array_filter($ratings, function ($element) {
            if ($element != 0) {
                return true;
            }
            return false;
        });


        $questions = EvaluationQuestions::find()->asArray()->all();

        $questions = ArrayHelper::index($questions, null, 'sub_category_id');

        $questionsCount = [];
        foreach ($questions as $key => $question) {
            $questionsCount[$key] = count($question);
        }

        $evalQuestions = EvaluationQuestions::find()->select('sub_category_id')
            ->where(['question_id' => array_keys($ratings)])
            ->asArray()->all();

        $evalQuestions = ArrayHelper::index($evalQuestions, null, 'sub_category_id');
        $evalQuestionsCount = [];
        foreach ($evalQuestions as $key => $question) {
            $evalQuestionsCount[$key] = count($question);
        }

        $subcategories = array_keys(array_diff_assoc($questionsCount, $evalQuestionsCount));

        if (!$subcategories) {
            $result['status'] = true;
            return $result;
        }

        $categories = EvaluationQuestionsSubCategories::find()->joinWith(['categories'])->where(['sub_category_id' => $subcategories])->asArray()->all();

        $subcategoryMap = ArrayHelper::getColumn($categories, function ($element) {
            $ele = [];
            $ele['sub'] = $element['sub_category_id'];
            $ele['main'] = $element['category_id'];
            return $ele;
        });

        $categoryNames = array_unique(ArrayHelper::getColumn($categories, 'categories.category_name'));
        $categoryNames = implode(',', $categoryNames);
        $result['failedTabs'] = $subcategories;
        $result['failedErrorMessage'] = "Please answer all the questions in following sections : " . $categoryNames;
        $result['failedSubTabs'] = $subcategoryMap;
        return $result;
    }

    public function actionSendFinalEmail()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        try {
            $postInfo = $request->getBodyParams();
            $return = [];
            $data['CandidateEmails'] = $postInfo['email'];
            $email = new CandidateEmails();
            $email->load($data);
            $email->user_id = $postInfo['candidateId'];
            $email->send_to = $postInfo['email']['to'];
            $email->body = $postInfo['email']['message'];
            if ($email->save()) {
                $emailStatus = MailComponent::sendMailToCandidate($email);
                if (!$emailStatus) {
                    $return['status'] = false;
                } else {
                    $return['status'] = true;
                }
            } else {
                $return['status'] = false;
            }
            $response->setStatusCode(200);
            return $return;
        } catch (Exception $e) {
            $response->setStatusCode(422);
            throw new HttpException(422, $e->getMessage());
        }

    }
}