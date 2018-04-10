<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\db\Expression;
use app\models\Test;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";
    }
    
    
    public function actionInsert(){
    	
  //  	$model =  new Test();
    	
//     	for($i=0;$i<=100000;$i++){
//     	$model->type = 1;
//     	$model->created_by = 1;
//     	$model->isNewRecord = true;
//     	$model->id = null;
//     	$model->save();
//     	}
    	
    	$sql = "INSERT INTO `tbl_aca_test`(`id`, `type`, `created_date`, `created_by`) VALUES";
    	
    	for($i=0;$i<=100000;$i++){
    		$sql .= " (null,'1','2008-11-11','1'),";

		}
		$sql = rtrim($sql,',');
		//echo $sql;
		\Yii::$app->db->createCommand($sql)->execute();
		
		echo "Model saved Successfully";
		
    	
//     	if($model->save()){
    		
//     	}else{
//     		print_r($model->errors);die();
//     		echo "Model save failed";
//     	}
    }
}
