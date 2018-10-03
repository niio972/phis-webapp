<?php

//******************************************************************************
//                                       BaseController.php
// SILEX-PHIS
// Copyright © INRA 2018
// Creation date: 21 Aug, 2018
// Contact: arnaud.charleroy@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\yiiModels\YiiUserModel;
use yii\filters\AccessControl;
use app\models\wsModels\WSConstants;

/**
 * Regroup common method used by controllers
 * Base behaviors for client web app access right
 * @see yii\web\Controller
 * @author Arnaud Charleroy <arnaud.charleroy@inra.fr>
 */
class BaseController extends Controller {

    /**
     * Define the behaviors
     * @return array
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            $result = true;
                            $userModel = new YiiUserModel();
                            
                            // If the session is set, the user is connected 
                            // Verify the WS token
                            if (isset(Yii::$app->session['access_token']) && isset(Yii::$app->session['email'])) {
                                $findByEmail = $userModel->findByEmail(Yii::$app->session[WSConstants::ACCESS_TOKEN], Yii::$app->session['email']);
                                // If the session is not found in the WS, the user is disconnected
                                if ($findByEmail === WSConstants::FAILED_TO_CONNECT) {
                                    throw new \yii\base\UserException(WSConstants::FAILED_TO_CONNECT);
                                }
                                // If the session is not found in the WS, the user is disconnected
                                if ($result && isset($findByEmail[WSConstants::TOKEN])) {
                                    $result = false;
                                }
                             // If session not set, the user is disconnected
                            } else {
                                // Test if web service is available
                                $find = null;
                                try {
                                    $find = $userModel->find('tokentest', []);
                                } catch (\GuzzleHttp\Exception\ServerException $exc) {
                                    // The webservice works
                                    $result = true;
                                }
                                // Test if web service is available
                                if (isset($find) && $find === WSConstants::FAILED_TO_CONNECT) {
                                    throw new \yii\base\UserException(WSConstants::FAILED_TO_CONNECT);
                                }
                            }
                            return $result;
                        }
                    ]
                ]
            ]
        ];
    }

}
