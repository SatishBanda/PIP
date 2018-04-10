<?php
namespace app\models;

use yii\base\Model;
use app\models\User;
use app\components\MailComponent;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $username;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'email'],
            ['username', 'exist',
                'targetClass' => '\app\models\User',
            	'targetAttribute' => 'username',
                'filter' => ['is_active' => User::STATUS_ACTIVE],
                'message' => 'No user is registered with this email.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendPasswordResetEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'is_active' => User::STATUS_ACTIVE,
            'username' => $this->username,
        ]);

        if (!$user) {
            return false;
        }
        
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save(false)) {
                return false;
            }
        }
        
        return MailComponent::sendPasswordResetTokenMail($user);

    }
}
