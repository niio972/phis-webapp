<?php
//******************************************************************************
//                           index.php
// SILEX-PHIS
// Copyright © INRA 2018
// Creation date: 6 Aug, 2017
// Contact: arnaud.charleroy@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\yiiModels\YiiDatasetModel;
use app\controllers\DatasetController;
use kartik\select2\Select2;
use app\components\helpers\Vocabulary;
use yii\bootstrap\BaseHtml;

/** 
 * Index of annotations (with search) 
 * @var $this yii\web\View 
 * @var $searchModel app\models\DatasetSearch 
 * @var $dataProvider yii\data\ActiveDataProvider 
 */

$this->title = Yii::t('app', '{n, plural, =1{Dataset} other{Datasets}}', ['n' => 2]);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="annotation-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
          'variables',
          'creationDate',
          'wasGeneratedBy',
          'wasGeneratedByDescription',
//            ['class' => 'yii\grid\ActionColumn',
//                'template' => '{view}',
//                'buttons' => [
//                    'view' => function($url, $model, $key) {
//                        return Html::a(BaseHtml::icon('eye-open'), ['dataset/view', 'id' => $model->uri]);
//                    },
//                ]
//            ],
        ],
    ]);
    ?>
</div>