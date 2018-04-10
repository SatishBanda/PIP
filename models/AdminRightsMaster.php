<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "{{%admin_rights_master}}".
 *
 * @property integer $permission_id
 * @property string $permission_name
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property Users $updatedBy
 * @property AdminUserPermissions[] $adminUserPermissions
 */
class AdminRightsMaster extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_rights_master}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['permission_name'], 'required'],
            [['updated_at'], 'safe'],
            [['updated_by'], 'integer'],
            [['permission_name'], 'string', 'max' => 255],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['updated_by' => 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'permission_id' => 'Permission ID',
            'permission_name' => 'Permission Name',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(Users::className(), ['user_id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUserPermissions()
    {
        return $this->hasMany(AdminUserPermissions::className(), ['permission_id' => 'permission_id']);
    }
    /**
     * Returns permissions  list
     * @method getList
     * @return [type]  [description]
     */
    public static function getList()
    {
       $permissions = self::find()->asArray()->all();
       //$permissions = ArrayHelper::map($permissions,'permission_id','permission_name');
       return $permissions;
    }
}
