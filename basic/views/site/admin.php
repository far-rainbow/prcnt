<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\EntryForm;
use app\models\Coords;

setlocale(LC_ALL, 'ru_RU.UTF-8');

$model = new EntryForm();

$functionList = $model->functionList;
$periodTypeList = $model->periodTypeList;
$startHourList=$model->startHourList;
$countWeekList=$model->countWeekList;

//$model->countWeekList = $model->defaultCountWeekList;

if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    
    $model->result();    
   
?>
<br>
<p>Отчёт отладочный:</p>
<ul>
	<li><label>Функция</label>: <?= Html::encode($functionList[$model->functionList]) ?></li>
	<li><label>Период в часах</label>: <?= Html::encode($periodTypeList[$model->periodTypeList]) ?></li>
	<li><label>Начало суточного периода</label>: <?= Html::encode($startHourList[$model->startHourList]) ?></li>
	<li><label>Недели</label>: <?= Html::encode($countWeekList[$model->countWeekList]) ?></li>
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
$lastRowHour = date('H',strtotime($lastRowDate));

if ($lastRowHour-$startHourList[$model->startHourList]>$periodTypeList[$model->periodTypeList]) {
    $currentDayOk = true;
    var_dump($currentDayOk);
} else {
    $currentDayOk = false;
    var_dump($currentDayOk);
}

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
        
        // если накопительный массив нулёвый, то к else, если там ноль или сумма, то пытаемся накопить данные этой выборки
        if (isset($wait_prcnt[$iterDay])) {
            
            // если в рамках заданного часового периода, то накапливаем, если нет, то игнорируем этот временной промежуток (1/4 часа на итерацию на данный момент)
            if ($model->timeRange($e['timestamp'],$startHourList[$model->startHourList],$periodTypeList[$model->periodTypeList])) {
                $wait_prcnt[$iterDay] += $e['wait_prcnt'];
                //print();
            }
            
        } else {
            // нулёвый инициализируем нулём (т.к. начало дневнего периода не бывает = 00-00, то можно упроститься и забить на техническую потерю значения в случае
            // начала отсчёта в 00-00 -- чего не бывает в указанном алгоритме. Используем это для инициализации массива)
            // !!!Если период дня будет начинаться с 00-00 придётся переделать!!!
            $wait_prcnt[$iterDay] = 0;
        }
    }
    
    $alertDay = array_search((min($wait_prcnt)),$wait_prcnt);
    
    $delim = 0;
    foreach (array_reverse($wait_prcnt) as $wp => $wd) {
        
        if($delim++%7==0) {
            print ('<br>--- Неделя №'.(int)($delim/7+1).'<br>');
        }
        
        if ($alertDay == $wp) {
            print $wp.' ('. $model->dateru(date('l',strtotime($wp))).') -- сумма дня с '.$startHourList[$model->startHourList].' по '.(($startHourList[$model->startHourList]+$periodTypeList[$model->periodTypeList])%24).' ч. --> '.$wd." -- минимальное значение из представленных<br>";
        } else {
            print $wp.' ('. $model->dateru(date('l',strtotime($wp))).') -- сумма дня с '.$startHourList[$model->startHourList].' по '.(($startHourList[$model->startHourList]+$periodTypeList[$model->periodTypeList])%24).' ч. --> '.$wd."<br>";
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
        			<?= $form->field($model, 'functionList')->dropDownList(['Функция' => $model->functionList])->label($model->functionListFieldName)->hint('Тип обработки (логика)') ?>
        			<?= $form->field($model, 'periodTypeList')->dropDownList(['Суточный интервал в часах' => $model->periodTypeList])->label($model->periodTypeListName)->hint('12 или 15 обсчитываемых часов в сутках') ?>
        			<?= $form->field($model, 'startHourList')->dropDownList(['Час начала дневного интервала' => $model->startHourList],array('empty' => 'Type what you need here',))->label($model->startHourListName)->hint('начало дня') ?>
        			<?= $form->field($model, 'countWeekList')->dropDownList(['Количество исследуемых недель' => $model->countWeekList],['options'=>['2' => ['selected'=>'selected']]])->label($model->countWeekListName)->hint('1,2,3,4') ?>
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
