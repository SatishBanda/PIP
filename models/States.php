<?php
namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%states}}".
 *
 * @property integer $state_id
 * @property string $state_code
 * @property string $state_name
 * @property integer $country_id
 *
 * @property Countries $country
 */
class States extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%states}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'state_code',
                    'state_name'
                ],
                'required'
            ],
            [
                [
                    'country_id'
                ],
                'integer'
            ],
            [
                [
                    'state_code'
                ],
                'string',
                'max' => 2
            ],
            [
                [
                    'state_name'
                ],
                'string',
                'max' => 255
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'state_id' => 'State ID',
            'state_code' => 'State Code',
            'state_name' => 'State Name'
        ];
    }

    /**
     */
    public static function getList()
    {
        return self::find()->asArray()->all();
    }
}
