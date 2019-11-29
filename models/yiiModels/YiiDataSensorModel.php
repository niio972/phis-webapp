<?php

//**********************************************************************************************
//                                       YiiDataSensorModel.php 
//
// Author(s): Morgane VIDAL
// PHIS-SILEX version 1.0
// Copyright © - INRA - 2017
// Creation date: October 2017
// Contact: arnaud.charleroy@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
// Last modification date:  November, 2 2019
// Subject: The Yii model for the sensor dataset. Used with web services
//***********************************************************************************************

namespace app\models\yiiModels;

use Yii;
use app\models\yiiModels\YiiDatasetModel;

/**
 * Model for the dataset. Used with web services
 * @author Arnaud Charleroy <arnaud.charleroy@inra.fr>
 */
class YiiDataSensorModel extends YiiDatasetModel {

  /**
     * 
     * @return array the rules of the attributes
     */
    public function rules() {
        $rules =  parent::rules();
        $rules[] =[['provenanceSensingDevices'], 'required'];
        return $rules;
    }
    
    /**
     * 
     * @return array the labels of the attributes
     */
    public function attributeLabels() {
        $rules =  parent::attributeLabels();
        $rules['variables'] = Yii::t('app', 'Variable(s)');
        return $rules;
    }
}
