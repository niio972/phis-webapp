<?php

//******************************************************************************
//                                       AnnotationSearch.php
//
// Author(s): Arnaud Charleroy <arnaud.charleroy@inra.fr>
// PHIS-SILEX version 1.0
// Copyright © - INRA - 2018// Creation date: 9 july 2018
// Contact: arnaud.charleroy@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
// Last modification date:  9 july 2018
// Subject: AnnotationSearch represents the model behind the search form about
//          \app\models\Annotation based ont he Yii2 search basic classes
//******************************************************************************

namespace app\models\yiiModels;

use app\models\yiiModels\YiiAnnotationModel;

/**
 * implements the search action for the annotations
 * @author Arnaud Charleroy <arnaud.charleroy@inra.fr>
 */
class AnnotationSearch extends YiiAnnotationModel {

    public function __construct($pageSize = null, $page = null) {
        parent::__construct($pageSize,$page);
         $this->creationDate = null;
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['uri', 'creator', 'motivatedBy', 'comment', 'targets'], 'safe']
        ];
    }

    /**
     * 
     * @param array $sessionToken used for the data access
     * @param string $params search params
     * @return mixed DataProvider of the result 
     *               or string \app\models\wsModels\WSConstants::TOKEN if the user needs to log in
     */
    public function search($sessionToken, $params) {
        //1. load the searched params 
        $this->load($params);
        if (isset($params[YiiModelsConstants::PAGE])) {
            $this->page = $params[YiiModelsConstants::PAGE];
        }

        //2. Check validity of search data
        if (!$this->validate()) {
            return new \yii\data\ArrayDataProvider();
        }

        //3. Request to the web service and return result
        $findResult = $this->find($sessionToken, $this->attributesToArray());

        if (is_string($findResult)) {
            return $findResult;
        } else if (isset($findResult[\app\models\wsModels\WSConstants::TOKEN])) {
            return $findResult;
        } else {
            $resultSet = $this->jsonListOfArraysToArray($findResult);
            return new \yii\data\ArrayDataProvider([
                'models' => $resultSet,
                'pagination' => [
                    'pageSize' => $this->pageSize,
                    'totalCount' => $this->totalCount
                ],
                //SILEX:info
                //totalCount must be there too to get the pagination in GridView
                'totalCount' => $this->totalCount
                    //\SILEX:info
            ]);
        }
    }

    /**
     * transform the json into array
     * @param json jsonList
     * @return array
     */
    private function jsonListOfArraysToArray($jsonList) {
        $toReturn = [];
        if ($jsonList !== null) {
            foreach ($jsonList as $value) {
                $toReturn[] = $value;
            }
        }
        return $toReturn;
    }

}
