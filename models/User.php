<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Firebase\JWT\JWT;
use yii\web\Request as WebRequest;

/**
 * This is the model class for table "{{%users}}".
 *
 * @property integer $user_id
 * @property string $username
 * @property string $mobile
 * @property integer $user_type
 * @property string $auth_key
 * @property integer $access_token_expired_at
 * @property string $password
 * @property string $password_reset_token
 * @property string $password_requested_at
 * @property integer $is_active
 * @property integer $is_verified
 * @property integer $is_delete
 * @property string $last_logged_at
 * @property string $last_login_ip
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property AdminUserPermissions[] $adminUserPermissions
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface {

    const ROLE_SUPER_ADMIN = 1;
    const ROLE_CANDIDATE = 2;
    const ROLE_STAFF_USER = 3;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /** @var  string to store JSON web token */
    public $access_token;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%users}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['username', 'mobile', 'user_type', 'auth_key'], 'required'],
            [['username'], 'unique', 'message' => 'A user with this email already exists, please enter a different email id', 'targetClass' => self::className(), 'filter' => ['=', 'is_delete', 0]],
            [['user_type', 'is_active', 'is_verified', 'is_delete', 'created_by', 'updated_by','user_id'], 'integer'],
            [['first_name','last_name','password_requested_at', 'last_logged_at', 'created_at', 'updated_at'], 'safe'],
          //  ['last_login_ip', 'ip'],
            ['is_active', 'validateStatus', 'on' => 'update'],
            [['username', 'auth_key', 'password', 'mobile', 'password_reset_token'], 'string', 'max' => 255],
        ];
    }

    public function validateStatus() {
        if ($this->is_active && !$this->is_verified) {
            $this->addError('is_active', 'User is not verified, Status cannot be changed to active');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'user_id' => 'User ID',
            'Username' => 'Username',
            'mobile' => 'Mobile',
            'user_type' => 'User Type',
            'auth_key' => 'Auth Key',
            'password' => 'Password',
            'password_reset_token' => 'Password Reset Token',
            'password_requested_at' => 'Password Requested At',
            'is_active' => 'Is Active',
            'is_verified' => 'Is Verified',
            'is_delete' => 'Is Delete',
            'last_logged_at' => 'Last Logged At',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /** @inheritdoc */
    public function behaviors() {
        // TimestampBehavior also provides a method named touch() that allows you to assign the current timestamp to the specified attribute(s) and save them to the database. For example,
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s')
            ]
        ];
    }

    /**
     * Validate Username
     *
     * @param $attribute
     * @param $params
     */
    public function validateEmail($attribute, $params) {
        // get post type - POST or PUT
        $request = Yii::$app->request;

        // if POST, mode is create
        if ($request->isPost) {
            // check username is already taken

            $existingUser = User::find()
                    ->where(['username' => $this->$attribute])
                    ->count();

            if ($existingUser > 0) {
                $this->addError($attribute, Yii::t('app', 'The Username has already been taken.'));
            }
        } elseif ($request->isPut) {
            // get current user
            $user = User::findIdentityWithoutValidation($this->id);

            if ($user == null) {
                $this->addError($attribute, Yii::t('app', 'The system cannot find requested user.'));
            } else {
                // check username is already taken except own username
                $existingUser = User::find()
                        ->where(['=', 'username', $this->$attribute])
                        ->andWhere(['!=', 'id', $this->id])
                        ->count();
                if ($existingUser > 0) {
                    $this->addError($attribute, Yii::t('app', 'The username has already been taken.'));
                }
            }
        } else {
            // unknown request
            $this->addError($attribute, Yii::t('app', 'Unknown request'));
        }
    }

    /**
     * Logins user by given JWT encoded string. If string is correctly decoded
     * - array (token) must contain 'jti' param - the id of existing user
     * @param  string $accessToken access token to decode
     * @return mixed|null          User model or null if there's no user
     * @throws \yii\web\ForbiddenHttpException if anything went wrong
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        $secret = static::getSecretKey();
        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [static::getAlgo()]);
        } catch (\Exception $e) {
            return false;
        }
        static::$decodedToken = (array) $decoded;
        // If there's no jti param - exception
        if (!isset(static::$decodedToken['jti'])) {
            return false;
        }
        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = static::$decodedToken['jti'];
        return static::findByJTI($id);
    }

    /**
     * Finds User model using static method findOne
     * Override this method in model if you need to complicate id-management
     * @param  string $id if of user to search
     * @return mixed       User model
     */
    public static function findByJTI($id) {
        /** @var User $user */
        $user = static::find()->where([
                            '=', 'user_id', $id
                        ])
                        ->andWhere([
                            '=', 'is_active', self::STATUS_ACTIVE
                        ])
                        ->andWhere([
                            '>', 'access_token_expired_at', new Expression('NOW()')
                        ])->one();
        if ($user !== null && ($user->getIsVerified() == true)) {
            return null;
        }
        return $user;
    }

    /**
     * Finds user by username
     *
     * @param string $usernamet
     * @param array $roles
     * @return static|null
     */
    public static function findByUsernameWithRoles($username) {
        /** @var User $user */
        $user = static::find()->where([
                    'username' => $username,
                    'is_active' => self::STATUS_ACTIVE,
                ])->one();

        if ($user !== null && ($user->getIsVerified() == true)) {
            return null;
        }

        return $user;
    }

    /**
     * @return bool Whether the user is blocked or not.
     */
    public function getIsVerified() {
        return $this->is_verified != 1;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
                    'password_reset_token' => $token,
                        //'is_active' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId() {
       // return $this->getPrimaryKey();
        return $this->user_id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey() {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id) {
        $user = static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
        if ($user !== null &&
                ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)) {
            return null;
        }
        return $user;
    }

    public static function findIdentityWithoutValidation($id) {
        $user = static::findOne(['id' => $id]);

        return $user;
    }

    /**
     * Generate access token
     *  This function will be called every on request to refresh access token.
     *
     * @param bool $forceRegenerate whether regenerate access token even if not expired
     *
     * @return bool whether the access token is generated or not
     */
    public function generateAccessTokenAfterUpdatingClientInfo($forceRegenerate = false) {
        // update client login, ip
        $this->last_login_ip = Yii::$app->request->userIP;
        $this->last_logged_at = new Expression('NOW()');

        // check time is expired or not
        if ($forceRegenerate == true || $this->access_token_expired_at == null || (time() > strtotime($this->access_token_expired_at))) {
            // generate access token
            $this->generateAccessToken();
        }
        $this->save(false);
        return true;
    }

    /**
     * Store cookie details for remember me and return it to front end
     *
     * @param string $userAgent
     * @return NULL[]|string[]
     */
    public function generateRememberMeCookieDetails($userAgent) {
        $cookie = CookieManager::find()->where([
                    'user_id' => $this->user_id
                ])->one();

        if (empty($cookie)) {
            $cookie = new CookieManager();
            $cookie->user_id = $this->user_id;
        }

        $cookie->cookie_key = "rememberMe";
        $cookie->cookie_value = Yii::$app->security->generateRandomString() . '_' . time();
        $cookie->user_agent = $userAgent;

        $cookie->save();

        return [
            'cookie_key' => $cookie->cookie_key,
            'cookie_value' => $cookie->cookie_value
        ];
    }

    public function generateAccessToken() {
        // generate access token
        // $this->access_token = Yii::$app->security->generateRandomString();
        $tokens = $this->getJWT();
        $this->access_token = $tokens[0];   // Token
        $this->access_token_expired_at = date("Y-m-d H:i:s", $tokens[1]['exp']); // Expire
    }

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @return array encoded JWT
     */
    public function getJWT() {
        // Collect all the data
        $secret = static::getSecretKey();
        $currentTime = time();
        $expire = $currentTime + Yii::$app->params['jwt.access_token_expired_at'];
        $request = Yii::$app->request;
        $hostInfo = '';
        // There is also a \yii\console\Request that doesn't have this property
        if ($request instanceof WebRequest) {
            $hostInfo = $request->hostInfo;
        }

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([
            'iat' => $currentTime, // Issued at: timestamp of token issuing.
            'iss' => $hostInfo, // Issuer: A string containing the name or identifier of the issuer application. Can be a domain name and can be used to discard tokens from other applications.
            'aud' => $hostInfo,
            'nbf' => $currentTime, // Not Before: Timestamp of when the token should start being considered valid. Should be equal to or greater than iat. In this case, the token will begin to be valid 10 seconds
            'exp' => $expire, // Expire: Timestamp of when the token should cease to be valid. Should be greater than iat and nbf. In this case, the token will expire 60 seconds after being issued.
            'data' => [
                'username' => $this->username,
                //'roleLabel'    =>  $this->getRoleLabel(),
                'lastLoginAt' => $this->last_logged_at,
            ]
                ], static::getHeaderToken());
        // Set up id
        $token['jti'] = $this->getJTI();    // JSON Token ID: A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
        return [JWT::encode($token, $secret, static::getAlgo()), $token];
    }

    /*
     * JWT Related Functions
     */

    /**
     * Store JWT token header items.
     * @var array
     */
    protected static $decodedToken;

    protected static function getSecretKey() {
        return Yii::$app->params['jwtSecretCode'];
    }

    // And this one if you wish
    protected static function getHeaderToken() {
        return [];
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo() {
        return 'HS256';
    }

    /**
     * Returns some 'id' to encode to token. By default is current model id.
     * If you override this method, be sure that findByJTI is updated too
     * @return integer any unique integer identifier of user
     */
    public function getJTI() {
        return $this->getId();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUserDetails() {
        return $this->hasOne(AdminUserDetails::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUserPermissions() {
        return $this->hasMany(AdminUserPermissions::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyUsers() {
        return $this->hasMany(CompanyUsers::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages() {
        return $this->hasMany(Messages::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts() {
        return $this->hasMany(Products::className(), ['account_manager' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCookieManagers() {
        return $this->hasMany(CookieManager::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserClients() {
        return $this->hasMany(ClientPurchaseUser::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientUserDetails() {
        return $this->hasOne(ClientPurchaseUser::className(), ['user_id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyUserDetails() {
        return $this->hasOne(CompanyUsers::className(), ['user_id' => 'user_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserFullName() {        
        $name = 'NA';
     
        switch ($this->user_type){
            case self::ROLE_ADMIN:
            case self::ROLE_SUPER_ADMIN: {
                $model = $this->adminUserDetails;
                if(!empty($model)){
                    $name = $model->first_name. ' ' .$model->last_name;
                }
            }
            break;
            case self::ROLE_CLIENT_USER: {
                $model = $this->clientUserDetails;
                if(!empty($model)){
                    $name = $model->purchaser_first_name. ' ' .$model->purchaser_last_name;
                }
            }
            break;
            case self::ROLE_COMPANY_USER: {
                $model = $this->companyUserDetails;
                if(!empty($model)){
                    $name = $model->first_name. ' ' .$model->last_name;
                }
            }
            break;
            default:
                return $name;
        }
        
        return $name;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public static function getUserFullNameThroughID($id) {        
        $name = 'NA';
     
        $user = self::findOne($id);
        
        switch ($user->user_type){
            case self::ROLE_ADMIN:
            case self::ROLE_SUPER_ADMIN: {
                $model = $user->adminUserDetails;
                if(!empty($model)){
                    $name = $model->first_name. ' ' .$model->last_name;
                }
            }
            break;
            case self::ROLE_CLIENT_USER: {
                $model = $user->clientUserDetails;
                if(!empty($model)){
                    $name = $model->purchaser_first_name. ' ' .$model->purchaser_last_name;
                }
            }
            break;
            case self::ROLE_COMPANY_USER: {
                $model = $user->companyUserDetails;
                if(!empty($model)){
                    $name = $model->first_name. ' ' .$model->last_name;
                }
            }
            break;
            default:
                return $name;
        }
        
        return $name;
    }

    public function getEvaluationHistory()
    {
        return $this->hasMany(CandidateEvaluations::className(), ['user_id' => 'user_id']);
    }
}
