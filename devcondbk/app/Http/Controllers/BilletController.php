<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Storage;

use App\Models\Billets;
use App\Models\Unit;

class BilletController extends Controller
{
    public function getAll() {
        $array = ['error' => ''];

        $property = $request->input('property');
        if($property) {

            //pega o usuário logado
            $user = auth()->user();

            //conta as unidades que o usuário possui
            $unit = Unit::where('id', $property)
            ->where('id_owner', $user['id'])
            ->count();

            //verifica se a unidade desejada pertence ao usuário logado
            if($unit > 0){
                $billets = Billets::where('id_unit', $property)->get();

                //guarda informações no storage
                foreach($billets as $billetKey => $BilletValue) {
                    $billets[$billetKey]['fileurl'] = asset('storage/'.$BilletValue['fileurl']);
                }

                $array['list'] = $billets;
            }else {
                $array['error'] = 'Esta unidade não é sua';
            } 

        }else {
            $array['error'] = 'a propriedade é necessária';
        }
        return $array;
    }
}
