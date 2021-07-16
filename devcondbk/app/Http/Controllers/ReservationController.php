<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use App\Models\Area;
use App\Models\Unit;
use App\Models\AreaDisabledDay;
use App\Models\Reservation;

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


    public function getDisabledDates() {

    }























      
    
    // fazendo a reserva
       public function setReservation($id, Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'property' => 'required'
        ]);
        if(!$validator->fails()){
            $date = $request->input('date');
            $time = $request->input('time');
            $property = $request->input('property');

            $unit = Unit::find($property);
            $area = Area::find($id);

            if($unit && $area) {
                $can = true;

                $weekday = date('w', strtotime($date));

                // Verificar se a reserva está dentro da disponibilidade padrão
                $allowedDays = explode(',', $area['days']);
                if(!in_array($weekday, $allowedDays)) {
                    $can = false;
                } else {
                    $start = strtotime($area['start_time']);
                    $end = strtotime('-1 hour', strtotime($area['end_time']));
                    $revtime = strtotime($time);
                    if($revtime < $start || $revtime > $end) {
                        $can = false;
                    } 
                }

                // verificar se está dentro dos DisabledDays
                $existingDisabledDay = AreaDisabledDay::where('id_area', $id)
                ->where('day', $date)
                ->count();

                if($existingDisabledDay > 0){
                    $can = false;
                }

                // verificar se não há outra reserva no mesmo local e horas
                $existingReservations = Reservation::where('id_area', $id)
                ->where('reservation_date', $date.' '.$time )
                ->count();
                if($existingReservations > 0){
                    $can = false;
                }


                if($can) {

                    $newReservation = new Reservation();
                    $newReservation->id_unit = $property;
                    $newReservation->id_area = $id;
                    $newReservation->reservation_date = $date.' '.$time;
                    $newReservation->save();


                } else {
                    $array['error'] = 'Reserva não Permitida neste dia/Horário';
                    return $array;
                }


            } else {
                $array['error'] = 'Dados Incorretos';
                return $array;
            }

        }else {
            $array['error'] = $validator->errors()->first();
            return $array;
        }


        return $array;
    }
        


}
