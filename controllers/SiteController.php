<?php

    namespace app\controllers;

    use Yii;
    use yii\base\Object;
    use yii\web\Controller;
    use yii\web\Response;

    class SiteController extends Controller
    {

        public function actionPing()
        {
            $response = new Response();
            $response->statusCode = 200;
            $response->data = Yii::t('app','pong');

            return $response;
        }


        public function actionError() {

            $response = new Response();
            $response->statusCode = 400;
            $response->data = json_encode([
                "name"      => "Bad Request",
                "message"   => Yii::t('app', 'The system could not process your request. Please check and try again.'),
                "code"      => 0,
                "status"    => 400,
                "type"      => "yii\\web\\BadRequestHttpException"
            ]);

            return $response;
        }
        
        public function actionMaintenance(){
         $this->layout =false;
         $url = Yii::$app->params['frontendURL'];
         $handle = curl_init($url);
         curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);

         // Get the HTML or whatever is linked in $url. 
         $response = curl_exec($handle);

         // Check for 404 (file not found). 
         $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        
        
         curl_close($handle);
         
         if($httpCode == 200) {     
          return $this->redirect($url);
         }
         
         return $this->render('maintenance');
        }


    }
