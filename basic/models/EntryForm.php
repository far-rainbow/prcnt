<?php
namespace app\models;

use Yii;
use yii\base\Model;

class EntryForm extends Model
{

    public $defaultStartHourList = 1;
    public $defaultCountWeekList = 3;
    public $defaultFunctionList = 1;
    public $defaultPeriodTypeList = 1;

    public $startHourList = [
        1 => 5,
        2 => 6,
        3 => 7,
        4 => 8,
        5 => 9,
        6 => 10,
        7 => 11,
        8 => 12,
        9 => 13,
        10 => 14,
        11 => 15,
        12 => 16,
        13 => 17,
        14 => 18,
        15 => 19,
        16 => 20,
        17 => 21,
        18 => 22,
    ];
    public $startHourListName = 'Час начала дня';

    
    public $countWeekList = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
    ];
    public $countWeekListName = 'Количество недель (1-4)';

    
    public $periodTypeList = [
        1 => '12',
        2 => '15',
    ];
    public $periodTypeListName = 'Период в часах';

    
    public $functionList = [
        1 => 'Поиск наименьшей суммы wait_prcnt по дням недели',
    ];
    public $functionListFieldName = 'Функция';

    public function result() {
        
        return 'TEST';
        
    }

    // tr = проверяемая на соответствие дата (таймштамп из выболрки из бд с часами и минутами Y-m-d H-M-s)
    // sh = начальный час интервала в сутках
    // tp = 12 или 15 часов интервал
    public function timeRange($tr,$sh,$tp) {

        // int час для проверки на соответствие интервалу
        $hour = date('H',strtotime($tr));
        
        // 12-часовой интервал
        if ($tp==12) {
            if( ($hour >= $sh) && ($hour <= $sh+$tp-1)) {
                return true;
            }
            return false;
        } else {
            // 15-часовой интервал с отбросом 3-х часов в середине интервала
            if( (($hour >= $sh) && ($hour <= $sh+$tp-1)) || (($hour < $sh+4) && ($hour > $sh+6))  ) {
                return true;
            }
            return false;
        }
    }
    
    static public function dateru($str) {
        $day = "";

        switch($str) {
            
            case 'Monday':$day = "Понедельник";break;
            case 'Tuesday':$day = "Вторник";break;
            case 'Wednesday':$day = "Среда";break;
            case 'Thursday':$day = "Четверг";break;
            case 'Friday':$day = "Пятница";break;
            case 'Saturday':$day = "Суббота";break;
            case 'Sunday':$day = "Воскресенье";break;
            
        }
        
        return $day;
    }
    
    public function getLastSunday($R) {
        
        foreach ($R as $D) {
            //if ( date('l',strtotime($D['timestamp']))==='Sunday' ) {
            var_dump($D['timestamp']);
            //}
        }
        
    }
    
    public function rules()
    {
        return [
            [
                ['countWeekList','functionList','startHourList','periodTypeList'],'required'],
            [
                'startHourList',
                'number'
            ],
            [
                'countWeekList',
                'number'
            ],
            [
                'periodTypeList',
                'number'
            ],
            [
                'functionList',
                'string'
            ]
        ];
    }
}