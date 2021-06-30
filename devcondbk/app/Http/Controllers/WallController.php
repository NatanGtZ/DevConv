<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Wall;
use App\Models\WallLike ;

class WallController extends Controller
{
    public function getAll() {
        // list = sempre vai ter o retorno de uma lista que pode ou não ter items
        $array = ['error' => '', 'list' => []];

        //utilizar e pegar o usuário logado.
        $user = auth()->user();

        $walls = Wall::all();

        $array['list'] = $walls;

        //foreach para mostrar a quantidade de likes de cada post, e se o usuário logado 
        //ja deu like naquele post específico
        foreach($walls as $wallKey => $wallValue) {
            $walls[$wallKey]['likes'] = 0;
            $walls[$wallKey]['liked'] = false;

            $likes = Walllike::where('id_wall', $wallValue['id'])->count();
            $walls[$wallKey]['likes'] = $likes;

            // procura na tabela registro se o usuário logado deu like no post
            $meLikes = Walllike::where('id_wall', $wallValue['id'])
            ->where('id_user', $user['id'])
            ->count();

            if($meLikes > 0){
                $walls[$wallKey]['liked'] = true;
            }
        }
    return $array;
    }


    public function like($id) {
        $array = ['error' => ''];

        $user = auth()->user();

        //procura uma postagem que o usuário deu like
        $meLikes = WallLike::where('id_wall', $id)
        ->where('id_user', $user['id'])
        ->count();

        if($meLikes > 0){
            // remover like
            
            WallLike::where('id_wall', $id)
            ->where('id_user', $user['id'])
            ->delete();

            $array['liked'] = false;
        }else {
            //adicionar like
            $newLike = new WallLike();
            $newLike->id_wall = $id;
            $newLike->id_user = $user['id'];
            $newLike->save();
            $array['liked'] = true;
        }

        //reconta a quantidade de likes e returna no array
        $array['likes'] = Walllike::where('id_wall', $id)->count();

        return $array;
    }
}
