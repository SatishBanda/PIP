<?php
    namespace app\models;

    use yii\base\Model;
    use yii\base\InvalidParamException;
    use app\models\User;

    /**
     * Password reset form
     */
    class PasswordResetForm extends Model
    {
        public $token;
        public $newPassword;
        public $retypePassword;

        /**
         * @var \app\models\User
         */
        private $_user;


        /**
         * @inheritdoc
         */
        public function rules()
        {
            return [
                ['token', 'required'],
                ['token', 'validatePasswordResetToken'],

            	['newPassword', 'required'],
            	['newPassword', 'string', 'min' => 6],

            	['retypePassword', 'required'],
            	['retypePassword', 'string', 'min' => 6],
            	['retypePassword', 'compare', 'compareAttribute'=>'newPassword'],

            ];
        }

        /**
         * Validates the password reset token.
         * This method serves as the inline validation for password reset token.
         *
         * @param string $attribute the attribute currently being validated
         * @param array $params the additional name-value pairs given in the rule
         */
        public function validatePasswordResetToken($attribute, $params)
        {
            $this->_user = User::findByPasswordResetToken($this->$attribute);

            if (!$this->_user) {
                $this->addError($attribute, 'Incorrect password reset token.');
            }
        }

        /**
         * Resets password.
         *
         * @return bool if password was reset.
         */
        public function resetPassword()
        {
            $user = $this->_user;
            $user->setPassword($this->newPassword);
            $user->is_verified = 1;
            $user->removePasswordResetToken();

            return $user->save(false);
        }
    }
