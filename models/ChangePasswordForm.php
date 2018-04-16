<?php
namespace app\models;

use yii\base\Model;
use app\models\User;

/**
 * Signup form
 */
class ChangePasswordForm extends Model
{
    public $username;
    public $current_password;
    public $newPassword;
    public $retypePassword;

    private $_user = false;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'email'],
            ['username', 'string', 'max' => 255],
        	['username', 'exist',
                'targetClass' => '\app\models\User',
                //'filter' => ['is_active' => User::STATUS_YES],
                'message' => 'There is no user with such email.'
            ],
        		
            ['current_password', 'required'],
            ['current_password', 'string'],
        //	['current_password', 'passwordCriteria'],

        	['newPassword', 'required'],
        	['newPassword', 'string', 'min' => 6],
        	
        	['retypePassword', 'required'],
        	['retypePassword', 'string', 'min' => 6],
        	['retypePassword', 'compare', 'compareAttribute'=>'newPassword'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function changePassword()
    {
    	if ($this->validate() && $this->passwordCriteria() && !$this->hasErrors()) {
    		$user = $this->getUser();
    			$user->setPassword($this->newPassword);
    			$user->save(false);
    			return true;
    	}
    	return false;
    }
    
    public function passwordCriteria()
    {
    		$user = $this->getUser();
    	
    		if (!$user || !$user->validatePassword($this->current_password)) {
    			$this->addError('current_password', 'Incorrect password.');
    		}else {
    			return true;
    		}
    	return false;
    }
    
    /**
     * Finds user by [[email]]
     *
     * @return User|null
     */
    public function getUser()
    {
    	if ($this->_user === false) {
    		$this->_user = User::findByUsernameWithRoles($this->username);
    	}
    
    	return $this->_user;
    }
}
