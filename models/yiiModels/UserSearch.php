<?php
//**********************************************************************************************
//                                       UserSearch.php 
// SILEX-PHIS
// Copyright © INRA 2017
// Creation date: Apr, 2017
// Contact: morgane.vidal@inra.fr, arnaud.charleroy@inra.fr, anne.tireau@inra.fr,
//          pascal.neveu@inra.fr
//***********************************************************************************************

namespace app\models\yiiModels;

use app\models\yiiModels\YiiUserModel;
use app\models\wsModels\WSConstants;

/**
 * UserSearch represents the model used for the search form about app\models\User
 * Based on the Yii2 Search basic classes
 * @author Morgane Vidal <morgane.vidal@inra.fr>, Arnaud Charleroy <arnaud.charleroy@inra.fr>
 * @update [Arnaud Charleroy] 19 September, 2018 : Pagination fixed
 * @update [Arnaud Charleroy] 26 September, 2018 : Pagination fixed
 */
class UserSearch extends YiiUserModel {
    
    //SILEX:refactor
    //create a trait (?) with methods search and jsonListOfArray and use it in 
    //each class ElementNameSearch
    //\SILEX:refactor
    
    /**
     * @inheritdoc
     */
    public function rules() {
        return [
          [['email', 'familyName', 'firstName', 'phone', 'affiliation', 'orcid', 'available', 'isAdmin', 'uri'], 'safe']  
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
        //1. Load the searched params 
        $this->load($params);
 
        //2. Check search parameters
        $this->checkParameters($params);
        
        //3. Check validity of search data
        if (!$this->validate()) {
            return new \yii\data\ArrayDataProvider();
        }
        
        //4. Request to the web service and return result
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
                    'totalCount' => $this->totalCount,
                    'page' => $this->page
                ],
                //SILEX:info
                //totalCount must be there too to get the pagination in GridView
                'totalCount' => $this->totalCount
                //\SILEX:info
            ]);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributesToArray() {
        $elementForWebService = parent::attributesToArray();
        
        // add page attribute for WS paramaters
        if(isset($this->page)){
            $elementForWebService[WSConstants::PAGE] = $this->getPageForWS();
        }
      
        return $elementForWebService;
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
    
    /**
     * Change the page depending on parameters
     * if parameters are new, page number is set to one on the web client and
     *    on page zero for the WS
     * else the latest page number searched is given
     * @param array $params paramters send by Yii2 send by the page
     */
    private function checkParameters($params) {
        // get class name
        $session = \Yii::$app->session;
        $className = get_class();
        $classNameExploded = explode('\\', $className);
        $class = end($classNameExploded);

        // on first page loading
        if (isset($params[$class])) {
            // save old parameters
            if (!isset($session[$class])) {
                $session[$class] = $params[$class];
            }
            // compare old and new parameters
            $parametersOld = array_filter($session[$class]);
            $parametersNew = array_filter($params[$class]);
            $parametersDiff = array_diff($parametersNew, $parametersOld);
            // define the right page
            // if no modification from users load the page requested
            // if there is a modification page number one is load
            if (count($parametersDiff) != 0 || (count($parametersDiff) != 0 && count($parametersNew) == 0)) {
                // add page attribute
                $this->page = 1;
            } else {
                if (isset($params[YiiModelsConstants::PAGE])) {
                    $this->page = $params[YiiModelsConstants::PAGE];
                }
            }
            // save new parameters for the next checks
            $session[$class] = $params[$class];
        } else {
            // if page two request on the first load
            // not necessary but it's a security
            if (isset($params[YiiModelsConstants::PAGE])) {
                $this->page = $params[YiiModelsConstants::PAGE];
            }
        }
    }
}
