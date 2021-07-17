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


    public function getDisabledDates($id) {
        $array = ['error' => ''];

        $area = Area::find($id);
        if($area) {

            // dias disabled padrão
            $disabledDays = AreaDisabledDay::where('id_area', $id)->get();
            foreach($disabledDays as $disabledDay){
                $array['list'][] = $disabledDay['day'];
            }


            //diaas disabled atraves do allowed
            $allowedDays = explode(',', $area['days']);
            $offDays = [];
            for($q = 0; $q < 7; $q++){
                if(!in_array($q, $allowedDays)){
                    $offDays[] = $q;
                }
            }

           //listar dias proibidos 3 meses para frente
            $start = time();
            $end = strtotime('+3 months');
            $current = $start;
            $keep = true;

            for(
                $current = $start; 
                $current < $end;
                $current = strtotime('+1 day', $current)
            ) {
                $wd = date('w', $current);

                    if(in_array($wd, $offDays)) {
                        $array['list'][] = date('Y-m-d', $current);
                    }                
            }

        } else {
            $array['error'] = 'Área Inexistente';
        }

        return $array;
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
        
    // lista de disponibilidade de horários
    public function getTimes($id, Request $request) {
        $array = ['error' => '', 'list' => []];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d'
        ]);

        if(!$validator->fails()) {
            $date = $request->input('date');
            $area = Area::find($id);

            if($area){

                $can = true; 

                //verificar se é dia disabled 
                $existingDisabledDay = AreaDisabledDay::where('id_area', $id)
                ->where('day', $date)
                ->count();
                if($existingDisabledDay > 0 ) {
                    $can = false;
                }



                // verificar se é dia permitido
                $allowedDays = explode(',', $area['days']);
                $weekday = date('w', strtotime($date));
                if(!in_array($weekday, $allowedDays)) {
                    $can = false;
                }

                if($can) {
                    $start = strtotime($area['start_time']);
                    $end = strtotime($area['end_time']);

                    $times = [];

                    for(
                        $lastTime = $start;
                        $lastTime < $end;
                        $lastTime = strtotime('+1 hour', $lastTime)
                    ) {
                        
                        $times[] = $lastTime;

                    }

                   $timeList =[];
                   foreach($times as $time) {
                       $timeList[] = [
                            'id' => date('h:i:s', $time),
                            'title' => date('h:i', $time).' - '.date('H:i', strtotime('+1 hour', $time))
                       ];
                   }

                   //removendo as reservas
                   $reservations = Reservation::where('id_area', $id)
                   ->whereBetween('reservation_date', [
                        $date.' 00:00:00',
                        $date.' 23:59:59'
                   ])
                   ->get();

                    $toRemove = [];
                    foreach($reservations as $reservation) {
                        $time = date('H:i:s', strtotime($reservation['reservation_date']));
                        $toRemove[] = $time;
                    }


                   foreach($timeList as $TimeItem){
                       if(!in_array($TimeItem['id'], $toRemove)) {
                           $array['list'][] = $TimeItem;
                       }
                   }

                }

            } else {
                $array['error'] = 'área Inexistente';
            }


        } else {
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }


    public function getMyReservations(Request $request) {
        $array = ['error' => '', 'list' => []];

        $property = $request->input('property');
        if($property){
            $unit = Unit::find($property);
            if($unit) {

                $reservations = Reservation::where('id_unit', $property)
                ->orderBy('reservation_date', 'DESC')
                ->get();

                foreach($reservations as $reservation) {
                    $area = Area::find($reservation['id_area']);

                    $daterev = date('d/m/Y H:i', strtotime($reservation['reservation_date']));
                    $aftertime = date('H:i', strtotime('+1 hour', strtotime($reservation['reservation_date'])));
                    $daterev.= ' à '.$aftertime;

                    $array['list'][] = [
                        'id' => $reservation['id'],
                        'id_area' => $reservation['id_area'],
                        'title' => $area['title'],
                        'cover' => asset('storage/'.$area['cover']),
                        'datereserved' => $daterev
                    ];

                }

            } else {
                $array['error'] = 'Propriedade Inexistente';
            return $array;
            }

        }else {
            $array['error'] = 'Propriedade Necessária';
            return $array;
        }

        return $array;
    }


    public function delMyReservation($id) {
        $array = ['error' => ''];

        $user = auth()->user();
        $reservation = Reservation::find($id);
        if($reservation) {

            $unit = Unit::where('id', $reservation['id_unit'])
            ->where('id_owner', $user['id'])
            ->count();


            if($unit > 0) {
                Reservation::find($id)->delete();
            } else {
                $array['error'] = 'Esta Reserva não é sua';
                return $array;
            }

        } else {
            $array['error'] = 'Reserva Inexistente';
            return $array;
        }

        return $array;
    }


}
