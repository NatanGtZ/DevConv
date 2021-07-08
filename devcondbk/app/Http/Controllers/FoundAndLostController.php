<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


class FoundAndLostController extends Controller
{
    public function getAll() {
        $array = ['error' => ''];

        $lost = foundAndLost::where('status', 'LOST')
        ->orderBy('datecreated', 'DESC')
        ->orderBy('id', 'DESC')
        ->get();

        $array['lost'] = $lost; 
     



        return $array;
    }
}
