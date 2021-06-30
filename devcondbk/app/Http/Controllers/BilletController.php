<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Storage;

use App\Models\Billets;
use App\Models\Units;

class BilletController extends Controller
{
    public function getAll() {
        $array = ['error' => ''];

        $property = $request->input('property');
        if($property) {

            $billets = Billets::where('id_unit', $property)->get();

            foreach($billets as $billetKey => $BilletValue) {
                $billets[$billetKey]['fileurl'] = asset('storage/'.$BilletValue['fileurl']);
            }

            $array['list'] = $billets;

        }else {
            $array['error'] = 'a propriedade é necessária';
        }
        return $array;
    }
}
