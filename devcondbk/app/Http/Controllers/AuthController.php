<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Models\User; 
use App\Models\Unit; 

class AuthController extends Controller
{
    // função que retorna um json mostrando que o usuário não é autorizado
    public function unauthorized() {
        return  response()->json([
            'error' => 'Não Autorizado'
        ], 401);
    }

    //função de registro recebendo os dados enviados através do request
    public function register(Request $request){
        $array = ['error' => ''];

        //validando as informações fornecidas pelo usuário
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|digits:11|unique:users,cpf',
            'password' => 'required',
            'password_confirm' => 'required|same:password'
        ]);

        if(!$validator->fails()) {
            //pegando informações fornecidas pelo usuário e salvando
            $name = $request->input('name');
            $email = $request->input('email');
            $cpf = $request->input('cpf');
            $password = $request->input('password');

            //criptografando a senha no padrão do php
            $hash = password_hash($password, PASSWORD_DEFAULT);

            //criando o novo usuário no banco e salvando.
            $newUser = new User();

            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->cpf = $cpf;
            $newUser->password = $hash;
            $newUser->save();

            $token = auth()->attempt([
                'cpf' => $cpf,
                'password' => $password

            ]);

            if(!$token){
                $array['error'] = 'Deu erro';
                return $array;
            }

            $array['token']= $token;

            $user = auth()->user();
            $array['user'] = $user;

            $properties = Unit::select(['id', 'name'])
            ->where('id_owner', $user['id'])
            ->get();


            $array['user']['properties'] = $properties; 
            

        }else {
            //código de erro caso o validador falhe
            $array['error'] = $validator->errors()->first();
            return $array;
        }

        return $array;
    }

}
