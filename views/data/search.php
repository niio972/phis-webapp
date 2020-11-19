<?php

//******************************************************************************
//                                       index.php
// PHIS-SILEX
// Copyright © INRA 2019
// Creation date: 22 mai 2019
// Contact: morgane.vidal@inra.fr, anne.tireau@inra.fr, pascal.neveu@inra.fr
//******************************************************************************

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\icons\Icon;
use kartik\form\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DataSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $variables array */

$this->title = Yii::t('app', '{n, plural, =1{Data} other{Data}}', ['n' => 2]);
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="row">
    <div class="col-md-2">
        <h4><?= Yii::t('yii', 'Search form') ?> </h4>
    </div>
    <div class="col-md-2">
        <?=
        Html::buttonInput(Yii::t('app/messages', 'Mask/show search form'),
                ['class' => 'btn btn-info',
                    'data-toggle' => "collapse",
                    'data-target' => "#searchForm",
                    "aria-expanded" => "true",
                    "aria-controls" => "searchForm"
        ]);
        ?>
    </div>
</div>
<script>

    /**
     * Fill variable dropdown
     * @param string experimentUri
     * @returns void        */
    function populateVariableList(experimentUri) {
        var select = $('#uriVariable-selector');
        var settings = select.attr('data-krajee-select2'),
                settings = window[settings];
        if (experimentUri !== undefined && experimentUri !== null) {
            $.ajax({
                url: '<?= Url::toRoute(['experiment/ajax-get-experiment-mesured-variables-select-list']); ?>',
                type: 'GET',
                dataType: 'json',
                data: {"experimentUri": experimentUri}
            })
                    .done(function (data) {
                        select.html("");
                        console.log(data)
                        if (data.data.length === 0) {
                            settings.data = {};
                            select.val('').change();
                        } else {
                            settings.data = data.data;
                            select.select2(settings).change();
                        }


                    })
                    .fail(function (jqXHR, textStatus) {
                        // Disaply errors
                        console.log(jqXHR)
                    });
        } else {
            select.val('').change();
            select.html("");
        }
    }

    // On provenance change update provenance fields
    $("#experiment-selector").change(function () {
        populateVariableList($(this).val());
    });
</script>
<div class="data-index">

    <div class="collapse in" id="searchForm">
        <?php
        $form = ActiveForm::begin([
                    'method' => 'GET',
                    'action' => Url::to(['search']),
        ]);

        echo $form->field($searchModel, 'experiment')->widget(\kartik\select2\Select2::classname(), [
            'data' => $this->params['experiments'],
            'options' => [
                'placeholder' => Yii::t('app/messages', 'Select one experiment') . ' ...',
                'id' => 'experiment-selector',
                'multiple' => false
            ],
            'pluginEvents' => [
                'select2:select' => 'function(e) { populateVariableList(e.params.data.id); }',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => false
            ]
        ]);

        echo $form->field($searchModel, 'variable')->widget(\kartik\select2\Select2::classname(), [
            'data' => $variables,
            'options' => [
                'id' => 'uriVariable-selector',
                'placeholder' => Yii::t('app/messages', 'Select a variable') . ' ...'
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'tags' => false
            ],
        ]);
        ?> 
        <div class="col-sm-6">
            <?php
            echo $form->field($searchModel, 'startDate')->widget(\kartik\date\DatePicker::className(), [
                'options' => [
                    'placeholder' => 'Enter date start'],
                'class' => 'col-sm-6 control-label',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]);
            ?>
        </div>
        <div class="col-sm-6">
            <?php
            echo $form->field($searchModel, 'endDate')->widget(\kartik\date\DatePicker::className(), [
                'options' => [
                    'placeholder' => 'Enter date end'],
                'class' => 'col-sm-6 control-label',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd'
                ]
            ]);
            ?>
        </div> 
        <?php
        foreach ($this->params['provenances'] as $uri => $provenance) {
            $provenancesArray[$uri] = $provenance->label . " (" . $uri . ")";
        }

        echo $form->field($searchModel, 'provenance')->widget(\kartik\select2\Select2::classname(), [
            'data' => $provenancesArray,
            'options' => [
                'placeholder' => Yii::t('app/messages', 'Select existing provenance') . ' ...',
                'id' => 'provenance-selector',
                'multiple' => false
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);



        echo Html::submitButton(Yii::t('app/messages', 'Search'), ['class' => 'btn btn-success',]);

        $form->end();
        ?>
    </div>
    <br>
    <?php  
    if ($dataProvider->count > 0) {
       echo Html::a(Icon::show('download-alt', [], Icon::BSG) . " " . Yii::t('yii', 'Download Search Result'), ['download-experiment-csv', 'model' => $searchModel], ['class' => 'btn btn-primary']);
    }
    ?>
    <h3><?= Yii::t('yii', 'Results') ?></h3>

    <?php 
    echo GridView::widget([
        'dataProvider' => $dataProvider, 
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
              'attribute' => 'variable',
              'format' => 'raw',
              'value' => function ($model, $key, $index) {
                    return Html::a($model->variable->label, ['variable/view', 'uri' => $model->variable->uri]);
              },
             
            ],
            [
                'attribute' => 'date', 
                'format' => 'raw',
                'value' => 'date',
     
            ],
            'value',
            [
              'attribute' => 'object',
              'format' => 'raw',
              'value' => function ($model) {
                    if ($model->object != null) {
                        $objectLabels = "";
                        foreach ($model->object->labels as $label) {
                            $objectLabels .= $label . "<br/> ";
                        }
                        return $objectLabels;
                    }
              }
            ],                    
            [
              'attribute' => 'provenance',
              'format' => 'raw',
              'value' => function ($model) {
                    if ($model->provenance != null) {
                        if ($model->provenance->label != null) {
                            return $model->provenance->label;
                        } else {
                            //SILEX:info
                            //it is a sensor. It is a temporary solution
                            //\SILEX:info
                            return Html::a($model->provenance->uri, ['sensor/view', 'id' => $model->provenance->uri]);
                        }
                    }
              }
            ],
        ],
    ]); ?>
</div>