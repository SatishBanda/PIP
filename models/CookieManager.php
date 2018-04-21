<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%cookie_manager}}".
 *
 * @property integer $cookie_id
 * @property integer $user_id
 * @property string $cookie_key
 * @property string $cookie_value
 * @property string $user_agent
 *
 * @property User $user
 */
class CookieManager extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cookie_manager}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'cookie_key', 'cookie_value', 'user_agent'], 'required'],
            [['user_id'], 'integer'],
            [['cookie_key'], 'string', 'max' => 50],
            [['cookie_value', 'user_agent'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cookie_id' => 'Cookie ID',
            'user_id' => 'User ID',
            'cookie_key' => 'Cookie Key',
            'cookie_value' => 'Cookie Value',
            'user_agent' => 'User Agent',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }
}
