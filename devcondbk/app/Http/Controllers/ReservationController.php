<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Area;

class ReservationController extends Controller
{
    public function getReservations() {
        $array = ['error' => '', 'list' => [] ];
        $daysHelper = ['Dom', 'Seg', 'Ter', 'Quar', 'Qui', 'Sex', 'Sáb'];

        $areas = Area::where('allowed', 1)->get();

        foreach($areas as $area) {
            $dayList = explode(',', $area['days']);

            $dayGroups = [];

            // primeiro dia
            $lastDay = intval(current($dayList));
            $dayGroups[] = $daysHelper[$lastDay];
            array_shift($dayList);

            // verifica a quebra na sequencia de dias. Pega os dias relevantes. 
            foreach($dayList as $day){
                if(intval($day) != $lastDay+1) {
                    $dayGroups[] = $daysHelper[$lastDay];
                    $dayGroups[] = $daysHelper[$day];
                }

                $lastDay = intval($day);
            }


            // ultimo dia
            $dayGroups[] = $daysHelper[end($dayList)];

            // juntando as datas  dia1-dia2 
            $dates = '';
            $close = 0;

            foreach($dayGroups as $group) {
                if($close === 0) {
                    $dates .= $group;
                } else {
                    $dates .= '-'.$group.',';
                }
                
                $close = 1 - $close;
            }

            $dates = explode(',', $dates);
            array_pop($dates);


            // adicionando as horas de inicio e fim  dia1-dia3 07:00 as 23:00
            $start = date('H:i', strtotime($area['start_time']));
            $end = date('H:i', strtotime($area['end_time']));

            foreach($dates as $dKey => $dValue) {
                $dates[$dKey] .= ' '.$start. ' as '.$end;
            }

            $array['list'][] = [
                'id' => $area['id'],
                'cover' => asset('storage/'.$area['cover']),
                'title' => $area['title'],
                'dates' => $dates
            ];
        

        }


        return $array;
    }
}
