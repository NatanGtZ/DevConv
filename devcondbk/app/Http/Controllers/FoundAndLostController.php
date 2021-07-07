<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use app\Models\FoundAndLost;

class FoundAndLostController extends Controller
{
    public function getAll() {
        $array = ['error' => ''];

        
        $lost = FoundAndLost::where('status', 'LOST')
        ->orderBy('datecreated', 'DESC')
        ->orderBy('id', 'DESC')
        -get();

        $array['lost'] = $lost; 



        return $array;
    }
}
