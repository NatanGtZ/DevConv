<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WallController extends Controller
{
    public function getAll() {
        $array = ['error' => '', 'list' => []];

        //utilizar e pegar o usuário logado.
        $user = auth()->user();

        return $array;
    }
}
