<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%countries}}".
 *
 * @property integer $country_id
 * @property string $country_code
 * @property string $country_name
 *
 * @property States[] $states
 */
class Countries extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%countries}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['country_code', 'country_name'], 'required'],
            [['country_code'], 'string', 'max' => 2],
            [['country_name'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'country_id' => 'Country ID',
            'country_code' => 'Country Code',
            'country_name' => 'Country Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStates() {
        return $this->hasMany(States::className(), ['country_id' => 'country_id']);
    }

}
