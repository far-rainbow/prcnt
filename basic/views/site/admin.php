<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\EntryForm;
use app\models\Coords;

setlocale(LC_ALL, 'ru_RU.UTF-8');

$model = new EntryForm();

//$model->countWeekList = $model->defaultCountWeekList;

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    
    $model->result();    
   
?>
<br>
<p>Отчёт отладочный:</p>
<ul>
	<li><label>a</label>: <?= Html::encode($model->functionList) ?></li>
	<li><label>Недели</label>: <?= Html::encode($model->countWeekList) ?></li>
	<li><label>c</label>: <?= Html::encode($model->startHourList) ?></li>
	<li><label>d</label>: <?= Html::encode($model->periodTypeList) ?></li>
</ul>

<div class="panel panel-success">
    <div class="panel panel-heading">
    	Неделя
    </div>
    
	<div class="eList panel-body">

<?php 

$weeks = $model->countWeekList;
$days = $weeks*7 - 1;

// Дата крайней записи в таблице $lastRowDate
$R = Coords::find()->orderBy(['timestamp'=>SORT_DESC])->limit(1)->all();
$lastRowDate = $R[0]['timestamp'];

// Функция №1 (других пока что нет)
if ($model->functionList==1) {

    // старшая граница периода в днях = кол-во недель умножить на 7 дней и вычесть из младшей границы  
    $rangeDay = date('Y-m-d',strtotime($lastRowDate . '-'.$days.' days'));

    // запрос к БД    
    $data = Coords::find()->where([
        '>=','DATE(timestamp)',$rangeDay
    ])->all();

    // обработка результата    
    $alertDay = 0;
    $wait_prcnt = array();
    
    foreach ($data as $e) {
        $iterDay = date('Y-m-d',strtotime($e['timestamp']));
        if (isset($wait_prcnt[$iterDay])) {
            $wait_prcnt[$iterDay] += $e['wait_prcnt'];
        } else {
            $wait_prcnt[$iterDay] = 0;
        }
    }
    
    $alertDay = array_search((min($wait_prcnt)),$wait_prcnt);
    
    $delim = 0;
    foreach ($wait_prcnt as $wp => $wd) {
        
        if($delim++%7==0) {
            print ('--- Неделя №'.(int)($delim/7+1).'<br>');
        }
        
        if ($alertDay == $wp) {
            print $wp.' -- сумма полного дня wait_prcnt ('. $model->dateru(date('l',strtotime($wp))).') --> '.$wd." -- минимальное значение из представленных<br>";
        } else {
            print $wp.' -- сумма полного дня wait_prcnt ('. $model->dateru(date('l',strtotime($wp))).') --> '.$wd."<br>";
        }
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
