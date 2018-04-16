<?php

namespace app\models;

use Yii;

class CandidateEvaluations extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%candidate_evaluation}}';
    }
}