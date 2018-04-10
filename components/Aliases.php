<?php

namespace app\components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;

class Aliases extends Component {

    public function init() {
        Yii::setAlias('@brand_logo_save_url', Yii::getAlias('@webroot') . '/images/uploads/brands');
    	Yii::setAlias('@admin_uploads_save_url', Yii::getAlias('@webroot') . '/uploads/admin');
    	Yii::setAlias('@client_uploads_save_url', Yii::getAlias('@webroot') . '/uploads/clients');
    	Yii::setAlias('@pdf_forms_save_root_url',  Yii::getAlias('@webroot') . '/files/pdf');
    	Yii::setAlias('@pdf_forms_save_url',  Url::to('@web/files/pdf', true));
    	Yii::setAlias('@csv_forms_save_url', Url::to('@web/files/formcsv', true));
    }

}

//sample
// \Yii::getAlias('@post_images_save_url');