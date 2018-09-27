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
                            // if session not set, the user is disconnected
                            if (isset(Yii::$app->session['access_token']) && isset(Yii::$app->session['email'])) {
                                $userModel = new YiiUserModel();
                                $findByEmail = $userModel->findByEmail(Yii::$app->session[WSConstants::ACCESS_TOKEN], Yii::$app->session['email']);
                                // if the session is not found in the WS, the user is disconnected
                                if (!isset($findByEmail[WSConstants::TOKEN])) {
                                    return true;
                                }
                            }
                            return false;
                        }
                    ],
                ],
            ],
        ];
    }
}
