<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SchedulingController; 

// Rotas Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas Protegidas 
Route::middleware('auth:sanctum')->group(function () {
    // Rota para obter dados do usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Rota para logout
    Route::post('/logout', [AuthController::class, 'logout']);
    // Rota para listar barbeiros disponíveis 
    Route::get('/barbers', [SchedulingController::class, 'barbers']);
    // Rota para listar serviços disponíveis 
    Route::get('/services', [SchedulingController::class, 'services']);
    // Rota para checar disponibilidade 
    Route::get('/availability', [SchedulingController::class, 'availability']); 

    // Agendamento
    // Rota para listar os agendamentos do usuário (O QUE ESTAVA FALTANDO)
    Route::get('/appointments', [SchedulingController::class, 'index']);
    // Rota para criar um agendamento
    Route::post('/appointments', [SchedulingController::class, 'store']);
});