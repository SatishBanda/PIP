<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%setting_master}}".
 *
 * @property integer $setting_id
 * @property string $setting_name
 * @property string $setting_value
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property User $updatedBy
 */
class SettingMaster extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%settings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['twilio_phone_number', 'account_id','account_auth_token','send_grid_api','default_email_for_outgoing','alerts_notification_days'], 'required'],
        ];
    }

  /**
   *  Returns the settings list
   * [getList description]
   * @method getList
   * @return [type]  [description]
   */
    public static function getList($asArray = true)
    {
      return self::find()->where(['setting_status'=>1])->asArray($asArray)->all();
    }
}
