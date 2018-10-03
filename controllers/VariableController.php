<?php

//**********************************************************************************************
//                                       VariableController.php 
//
// Author(s): Morgane VIDAL
// PHIS-SILEX version 1.0
// Copyright © - INRA - 2017
// Creation date: November, 27 2017
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
// Last modification date:  November, 27 2017
// Subject: implements the CRUD actions for WSVariableModel
//***********************************************************************************************

namespace app\controllers;
use app\controllers\BaseController;
use app\models\yiiModels\VariableSearch;
use app\models\yiiModels\MethodSearch;
use app\models\yiiModels\TraitSearch;
use app\models\yiiModels\UnitSearch;
use app\models\yiiModels\YiiMethodModel;
use app\models\yiiModels\YiiTraitModel;
use app\models\yiiModels\YiiUnitModel;
use app\models\yiiModels\YiiVariableModel;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * CRUD actions for YiiVariableModel
 * @see yii\web\Controller
 * @see app\models\yiiModels\YiiVariableModel
 * @author Morgane Vidal <morgane.vidal@inra.fr>
 */
class VariableController extends BaseController {

    /**
     * Search a variable by uri
     * @param string $uri searched variable's uri
     * @return string|YiiVariableModel "token" if user must log in
     *                                  YiiVariableModel representing the 
     *                                                      searched variable
     */
    public function findModel($uri) {
        $sessionToken = Yii::$app->session['access_token'];
        $variableModel = new YiiVariableModel(null, null);
        $requestRes = $variableModel->findByURI($sessionToken, $uri);
        
        if ($requestRes === true) {
            return $variableModel;
        } else if(isset($requestRes["token"])) {
            return "token";
        } else {
           throw new NotFoundHttpException('The requested page does not exist');
        }
    }
    
    /**
     * List all Variables
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new VariableSearch();
        
        $searchResult = $searchModel->search(Yii::$app->session['access_token'], Yii::$app->request->queryParams);
        if (is_string($searchResult)) {
            return $this->render('/site/error', [
                    'name' => Yii::t('app/messages','Internal error'),
                    'message' => $searchResult]);
        } else if (is_array($searchResult) && isset($searchResult["token"])) { //user must log in
            return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
        } else {
            return $this->render('index', [
               'searchModel' => $searchModel,
                'dataProvider' => $searchResult
            ]);
        }
    }
    
    /**
     * Displays a single Variable model
     * @param string $uri variable uri
     * @return mixed
     */
    public function actionView($uri) {
        $res = $this->findModel($uri);
        
        if ($res === "token") {
            return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
        } else {
            return $this->render('view', [
                'model' => $res
            ]);
        }
    }
    
    /**
     * 
     * @param mixed $instanceDefinitions instances definitions list. (traits, methods or units)
     * @return ArrayHelper of the instances definitions uri => label (traits, methods, units)
     */
    private function instancesDefinitionsToMap($instanceDefinitions) {
        if ($instanceDefinitions !== null) {
            return \yii\helpers\ArrayHelper::map($instanceDefinitions, 'uri', 'label');
        } else {
            return null;
        }
    }
    
    /**
     * create an element
     * @param WSActiveRecord $model
     * @return mixed request res
     */
    private function createElement($model) {  
        $dataToSend[] = $model->attributesToArray(); 
        $requestRes = $model->insert(Yii::$app->session['access_token'], $dataToSend);
        if (isset($requestRes->{'metadata'}->{'datafiles'})) {
            return $requestRes->{'metadata'}->{'datafiles'};
        } else {
            return $requestRes;
        }
    }
    
    /**
     * 
     * @param array $ontologiesReferences the references ontologies array 
     *                                    given by the form. Each line contains 
     *                                    entity, property, object and seeAlso
     * @param string $entity the entity for which we want the ontologies references
     * @return array the ontologies references corresponding to the given entity
     */
    private function getEntityOntologiesReferences($ontologiesReferences, $entity) {
        $toReturn = null;
        foreach ($ontologiesReferences as $ontologyReference) {
            if ($ontologyReference['entity'] === $entity) {
                $toReturn[] = $ontologyReference;
            }
        }
        
        return $toReturn;
    }
    
    /**
     * create variable and associated trait, method and unit if needed
     * @return mixed
     * @throws \yii\web\HttpException
     */
    public function actionCreate() {
        $sessionToken = Yii::$app->session['access_token'];
        
        $variableModel = new YiiVariableModel();
        $traitModel = new YiiTraitModel();
        $methodModel = new YiiMethodModel();
        $unitModel = new YiiUnitModel();
        
        //Form has been complete
        if ($variableModel->load(Yii::$app->request->post()) 
                && $traitModel->load(Yii::$app->request->post())
                && $methodModel->load(Yii::$app->request->post())
                && $unitModel->load(Yii::$app->request->post())){
            //1. Get ontologies references and given them to the right models
            $traitModel->ontologiesReferences = $this->getEntityOntologiesReferences($variableModel->ontologiesReferences, \config::path()['cTrait']);
            $methodModel->ontologiesReferences = $this->getEntityOntologiesReferences($variableModel->ontologiesReferences, \config::path()['cMethod']);
            $unitModel->ontologiesReferences = $this->getEntityOntologiesReferences($variableModel->ontologiesReferences, \config::path()['cUnit']);
            $variableModel->ontologiesReferences = $this->getEntityOntologiesReferences($variableModel->ontologiesReferences, \config::path()['cVariable']);
            
            //2. create trait/method/unit if needed
            if ($variableModel->trait === "") {
                $requestRes = $this->createElement($traitModel);
                if ($requestRes === "token") {
                    return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));;
                } else {
                    $variableModel->trait = $requestRes[0];
                }
            }
            if ($variableModel->method === "") {
                $requestRes = $this->createElement($methodModel);
                if ($requestRes === "token") {
                    return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));;
                } else {
                    $variableModel->method = $requestRes[0];
                }
            }
            if ($variableModel->unit === "") {
                $requestRes = $this->createElement($unitModel);
                if ($requestRes === "token") {
                    return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));;
                } else {
                    $variableModel->unit = $requestRes[0];
                }
            }
            //3. create variable
            $requestRes = $this->createElement($variableModel);
            if ($requestRes === "token") {
                return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));;
            } else if (is_string($requestRes)) {
                throw new \yii\web\HttpException(400, 'Bad Request');
            } else {
                return $this->redirect(['view', 'uri' => $requestRes[0]]);
            }
            
        } else { 
            $searchTraitModel = new TraitSearch();
            $traits = $searchTraitModel->find($sessionToken, []);
            
            $searchMethodModel = new MethodSearch();
            $methods = $searchMethodModel->find($sessionToken, []);
            
            $searchUnitModel = new UnitSearch();
            $units = $searchUnitModel->find($sessionToken, []);
            
            if (is_string($traits) || is_string($methods) || is_string($units)) {
                return $this->render('/site/error', [
                    'name' => Yii::t('app/messages','Internal error'),
                    'message' => "Request error while getting existing traits, methods and units"]);
            } else if (is_array($units) && isset($units["token"])) {
                return $this->redirect(Yii::$app->urlManager->createUrl("site/login"));
            } else {
                $traits = $this->instancesDefinitionsToMap($traits);
                $methods = $this->instancesDefinitionsToMap($methods);
                $units = $this->instancesDefinitionsToMap($units);
                $variableModel->isNewRecord = true;
                
                return $this->render('create', [
                    'modelVariable' => $variableModel,
                    'modelTrait' => $traitModel,
                    'modelMethod' => $methodModel,
                    'modelUnit' => $unitModel,
                    'listTraits' => $traits,
                    'listMethods' => $methods,
                    'listUnits' => $units
                ]);                
            }
        }
    }
}
