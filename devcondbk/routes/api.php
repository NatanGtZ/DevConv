<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\FoundAndLostController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\WarningController;


Route::get('/ping', function(){
        return ['pong'=>true];
});

//rota não autorizado, ou sem login
Route::get('/401', [AuthController::class, 'unauthorized'])->name('login'); 


//rota para fazer login e registro.
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);


// grupo de rotas em que o usuário precisa estar logado para acessar
Route::middleware('auth:api')->group(function(){
        
        Route::post('/auth/validate', [AuthController::class, 'validateToken']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        //Mural de Avisos       

        //rota para pegar todos os likes
        Route::get('/walls', [WallController::class, 'getAll']);
        //rota para dar e tirar likes
        Route::post('/wall/{id}/like', [WallController::class, 'like']);

        //documentos
        Route::get('/docs', [DocController::class, 'getAll']);

        //Livro de Ocorrências
        Route::get('/warnings', [WarningController::class, 'getMyWarnings']);
        Route::post('/warning', [WarningController::class, 'setWarning']);
        Route::post('warning/file', [WarningController::class, 'addWarningFile']);

        //Boletos
        Route::get('/billets',[BilletController::class, 'getAll']);

        //Achados e Perdidos
        Route::get('/foundandlost', [FoundAndLostController::class, 'getAll']);
        Route::post('/foundandlost', [FoundAndLostController::class, 'insert']);
        Route::put('foundandlost/{id}', [FoundAndLostController::class, 'update']);

        //Unidade
        Route::get('/unit/{id}', [UnitController::class, 'getInfo']);
        Route::post('/unit/{id}/addperson', [UnitController::class, 'addPerson']);
        Route::post('/unit/{id}/addvehicle', [UnitController::class, 'addVehicle']);
        Route::post('/unit/{id}/addpet', [UnitController::class, 'addPet']);
        Route::post('/unit/{id}/removeperson', [UnitController::class, 'removePerson']);
        Route::post('/unit/{id}/removevehicle', [UnitController::class, 'removeVehicle']);
        Route::post('/unit/{id}/removepet', [UnitController::class, 'removePet']);

        //reservas
        Route::get('/reservations', [ReservationController::class, 'getReservations']);
        Route::get('/myreservations', [ReservationController::class, 'getMyReservations']);

        Route::get('/reservation/{id}/disableddates', [ReservationController::class, 'getDisabledDates']);
        Route::get('/reservation/{id}/times', [ReservationController::class, 'getTimes']);

        Route::delete('/myreservation/{id}', [ReservationController::class, 'delMyReservation']);
        Route::post('/reservation/{id}', [ReservationController::class, 'setReservation']);
        
});





