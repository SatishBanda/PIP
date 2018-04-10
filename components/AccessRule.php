<?php
/**
 * 
 * @author Vinod Kumar Ravuri
 *
 */

namespace app\components;


use yii\web\HttpException;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

class AccessRule extends \yii\filters\AccessRule {

	/**
	 * @inheritdoc
	 */
	protected function matchRole($user)
	{
		if (empty($this->roles)) {
			return true;
		}
		foreach ($this->roles as $role) {
			if ($role === '?') {
				if ($user->getIsGuest()) {
					return true;
					//return $this->redirect(\Yii::$app->urlManager->createUrl(['site/login']));
				}
			} elseif ($role === '@') {
				if (!$user->getIsGuest()) {
					return true;
				}
				// Check if the user is logged in, and the roles match
			} elseif (!$user->getIsGuest() && $role === $user->identity->user_type) {
				if($role == 1){
					return true;
				}elseif ($role == 2){
					/* 	$requestedUrl = \Yii::$app->controller->module->id // only necessary if you're using modules
					 . '/' . \Yii::$app->controller->id
					 . '/' .\Yii::$app->controller->action->id
					 . '/'; */
					$requestedUrl = \Yii::$app->request->url;
					
					$availableAdminPermissions = ArrayHelper::getColumn($user->identity->adminUserPermissions, 'permission_id');
					
					$requiredRoutePermissions = \Yii::$app->params['routePermissions'];
					
					if($requiredRoutePermissions){
						foreach ($requiredRoutePermissions as $perm_id => $routes){
							foreach ($routes as $route){
								if (strpos($requestedUrl, $route) !== false){
									if (!in_array($perm_id, $availableAdminPermissions)){
										throw new HttpException(401,Json::encode('You do not have permission to access this content.'));
										//return false;
									}
								}
							}
						}
					}
				}
				return true;
			}
		}
		return false;
	}
}