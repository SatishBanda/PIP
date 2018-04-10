<?php
return  [
			[
				'exportInterval' => 1,
				'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
                'logVars' => ['GET', 'POST'],
				'prefix' => function($message) {
            		// There is no user when a console command is run
					try {
						$appUser = \Yii::$app->user;
					} catch (\Exception $e) {
						$appUser = Null;
					}
					if ($appUser && ! \Yii::$app->user->isGuest){
						$prefix = 'user='.\Yii::$app->user->identity->email . PHP_EOL;
					}else{
						$prefix = 'user=Guest'. PHP_EOL;
					}
					$prefix .= 'Log Time:'.date('m/d/Y h:i:s A') . PHP_EOL;
					// Try to get requested url and method
					try {
						$request = \Yii::$app->request;
						$prefix .= 'Requested URL: ' . $request->getAbsoluteUrl(). PHP_EOL;
						$prefix .= 'Request method: ' . $request->getMethod() . PHP_EOL;
						$prefix .= 'User IP: ' . $request->getUserIP() . PHP_EOL;
						$prefix .= 'User Host: ' . $request->getUserHost() . PHP_EOL;
					} catch (\Exception $e) {
						$prefix .= 'Requested URL: not available';
					}
            
					return PHP_EOL . $prefix;
            	},
            ],
            [
				'class' => 'app\components\CustomDbTarget',
            	'levels' => ['error', 'warning'],
            	'logVars' => [null],
                'prefix' => function($message) {
                	// There is no user when a console command is run
					$prefix = '{';
                	try {
                			$appUser = \Yii::$app->user;
                	} catch (\Exception $e) {
                			$appUser = Null;
                	}
                	if ($appUser && ! \Yii::$app->user->isGuest){
                		$prefix .= '"user":"'.\Yii::$app->user->identity->email.'",';
                	}else{
                		$prefix .= '"user":"Guest",';
                	}
                	$prefix .= '"Log Time":"'.date('m/d/Y h:i:s A').'",';
                	// Try to get requested url and method
                	
                	try {
                		$request = \Yii::$app->request;
                		$prefix .= '"Requested URL":"' . $request->getAbsoluteUrl().'",';
                		$requestedUrl = $request->getAbsoluteUrl();
                		$prefix .= '"Request method":"' . $request->getMethod().'"';
                	} catch (\Exception $e) {
                		$prefix .= '"Requested URL": "Not available",';
                		$prefix .= '"Request method": "Not available"';
                		$requestedUrl = 'NA';
                	}
                	$prefix .= '}';
                	return $requestedUrl;
                },
			],
];