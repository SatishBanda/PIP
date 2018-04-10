<?php

/**
 * Author: Naveen Rayuni
 */

namespace app\components;

use Yii;
use yii\db\Connection;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\VarDumper;
use yii\db\Expression;
/**
 * DbTarget stores log messages in a database table.
 *
 * The database connection is specified by [[db]].
 */
class CustomDbTarget extends yii\log\Target
{
	/**
	 * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
	 * After the DbTarget object is created, if you want to change this property, you should only assign it
	 * with a DB connection object.
	 * Starting from version 2.0.2, this can also be a configuration array for creating the object.
	 */
	public $db = 'db';
	/**
	 * @var string name of the DB table to store cache content. Defaults to "log".
	 */
	public $logTable = '{{%log}}';
	
	
	/**
	 * Initializes the DbTarget component.
	 * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
	 * @throws InvalidConfigException if [[db]] is invalid.
	 */
	public function init()
	{
		parent::init();
		$this->db = Instance::ensure($this->db, Connection::className());
	}
	
	/**
	 * Stores log messages to DB.
	 */
	public function export()
	{
		$tableName = $this->db->quoteTableName($this->logTable);
		$sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]], [[description]])
		VALUES (:level, :category, :log_time, :prefix, :message, :description)";
		$command = $this->db->createCommand($sql);
		foreach ($this->messages as $message) {
			list($text, $level, $category, $timestamp) = $message;
			if (!is_string($text)) {
				// exceptions may not be serializable if in the call stack somewhere is a Closure
				if ($text instanceof \Throwable || $text instanceof \Exception) {
					$text = (string) $text;
				} else {
					$text = VarDumper::export($text);
				}
			}
			
			$exceptionName= explode("'", $text)['1'];
			$trimMessage= explode("'", $text)['3'];
			$expression = new Expression('NOW()');
			$now = (new \yii\db\Query)->select($expression)->scalar();  // SELECT NOW();
			$now = date('m/d/Y h:i:s A', strtotime($now));
			if($exceptionName && $trimMessage){			
				$command->bindValues([
						':level' => $level,
						':category' => $exceptionName,
						':log_time' => $now, //date('m/d/Y h:i:s A'),
						':prefix' => $this->getMessagePrefix($message),
						':message' => $trimMessage,
				    	':description' => $text,
				])->execute();
			}
		}
	}
}
