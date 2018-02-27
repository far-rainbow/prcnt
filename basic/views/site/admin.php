<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\EntryForm;
use app\models\Coords;

$model = new EntryForm();

//$model->countWeekList = $model->defaultCountWeekList;

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    
    $model->result();    
   
?>
<br>
<p>Отчёт:</p>
<ul>
	<li><label>a</label>: <?= Html::encode($model->functionList) ?></li>
	<li><label>b</label>: <?= Html::encode($model->countWeekList) ?></li>
	<li><label>c</label>: <?= Html::encode($model->startHourList) ?></li>
	<li><label>d</label>: <?= Html::encode($model->periodTypeList) ?></li>
</ul>

<div class="panel panel-default">
	<div class="eList">

<?php 

$weeks = 1;
$days = $weeks*7 - 1;

// Дата крайней записи в таблице $lastRowDate
$R = Coords::find()->orderBy(['timestamp'=>SORT_DESC])->limit(1)->all();
$lastRowDate = $R[0]['timestamp'];

// Функция №1 (других пока что нет)
if ($model->functionList==1) {

    // старшая граница периода в днях = кол-во недель умножить на 7 дней и вычесть из младшей границы  
    $rangeDay = date('Y-m-d',strtotime($lastRowDate . '-'.$days.' days'));

    //var_dump($lastRowDate);
    //var_dump($rangeDay);
    
    $data = Coords::find()->where([
        '>=','DATE(timestamp)',$rangeDay
    ])->all();

    //$wait_prcnt[];// = $lastRowDate;
    
    $wait_prcnt = array();
    
    foreach ($data as $e) {
        $iterDay = date('Y-m-d',strtotime($e['timestamp']));
        if (isset($wait_prcnt[$iterDay])) {
            $wait_prcnt[$iterDay] += $e['wait_prcnt'];
        } else {
            $wait_prcnt[$iterDay] = 0;
        }
    }
    
    foreach ($wait_prcnt as $wp => $wd) {
        print $wp.' -- сумма полного дня wait_prcnt --> '.$wd."<br>";
    }

}

?>

	</div>
</div>
<?php
    
    /*
     *
     * Render the input form with default values before user submission
     *
     */
} else {

    $form = ActiveForm::begin([
        'options' => [
            'class' => 'col-xs-12',
            'enctype' => 'multipart/form-data'
        ],
    ]);

    ?>
<div class="container-fluid">
	<div class="row">

		<div class="col-md-4">
			<div class="item">
        			<?= $form->field($model, 'functionList')->dropDownList(['Функция' => $model->functionList])->label($model->functionListFieldName)->hint('xxx') ?>
        			<?= $form->field($model, 'periodTypeList')->dropDownList(['Период в часах' => $model->periodTypeList])->label($model->periodTypeListName)->hint('xxx') ?>
        			<?= $form->field($model, 'startHourList')->dropDownList(['Час начала дня' => $model->startHourList],array('empty' => 'Type what you need here',))->label($model->startHourListName)->hint('xxx') ?>
        			<?= $form->field($model, 'countWeekList')->dropDownList(['Количество недель' => $model->countWeekList],['options'=>['2' => ['selected'=>'selected']]])->label($model->countWeekListName)->hint('xxx') ?>
        	</div>
		</div>

	</div>
	<br>

	<!-- USER SUBMISSION BUTTON -->
	<div class="form-group">
			<?= Html::submitButton('Выполнить расчёт', ['class' => 'btn btn-primary']) ?>
	</div>
</div>

<?php
    ActiveForm::end();
}
?>
