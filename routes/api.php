<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SchedulingController; 

// Rotas Públicas //
// Rota de registro
Route::post('/register', [AuthController::class, 'register']);
// Rota de login
Route::post('/login', [AuthController::class, 'login']);

// Rotas Protegidas //
Route::middleware('auth:sanctum')->group(function () {
    // Rota para obter dados do usuário autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Rota para logout //
    Route::post('/logout', [AuthController::class, 'logout']);
    // Rota para listar barbeiros disponíveis 
    Route::get('/barbers', [SchedulingController::class, 'barbers']);
    // Rota para listar serviços disponíveis 
    Route::get('/services', [SchedulingController::class, 'services']);
    // Rota para checar disponibilidade 
    Route::get('/availability', [SchedulingController::class, 'availability']); 

    // Agendamento //
    // Rota para listar os agendamentos do usuário (O QUE ESTAVA FALTANDO)
    Route::get('/appointments', [SchedulingController::class, 'index']);
    // Rota para criar um agendamento
    Route::post('/appointments', [SchedulingController::class, 'store']);
    // Rota para atualizar um agendamento
    Route::put('/appointments/{id}', [SchedulingController::class, 'update']);
    // Rota para cancelar um agendamento
    Route::delete('/appointments/{id}', [SchedulingController::class, 'destroy']);

    // Agenda barbeiro //
    // Rota para o barbeiro ver sua agenda
    Route::get('/barber/agenda', [SchedulingController::class, 'barberAgenda']);
    // Rota para o barbeiro atualizar o status de um agendamento
    Route::patch('/appointments/{id}/status', [SchedulingController::class, 'updateStatus']);

    // Rotas de Bloqueio de Agenda (Profissional) //
    // Rota para listar os bloqueios do barbeiro
    Route::get('/barber/blocks', [SchedulingController::class, 'getBlocks']);
    // Rota para criar um bloqueio
    Route::post('/barber/blocks', [SchedulingController::class, 'storeBlock']);
    // Rota para deletar um bloqueio
    Route::delete('/barber/blocks/{id}', [SchedulingController::class, 'deleteBlock']);

    // Rotas de Métricas e Despesas (Admin) //
    // Rota para obter métricas do dashboard
    Route::get('/admin/metrics', [\App\Http\Controllers\AdminController::class, 'getDashboardMetrics']);
    // Rota para obter despesas do mês
    Route::get('/admin/expenses', [\App\Http\Controllers\AdminController::class, 'getExpenses']);
    // Rota para criar uma nova despesa
    Route::post('/admin/expenses', [\App\Http\Controllers\AdminController::class, 'storeExpense']);
    
    // Rotas de Gestão de Barbeiros (Admin) //
    // Rota para listar barbeiros
    Route::get('/admin/barbers', [\App\Http\Controllers\AdminController::class, 'getBarbers']);
    // Rota para criar um novo barbeiro
    Route::post('/admin/barbers', [\App\Http\Controllers\AdminController::class, 'storeBarber']);
    // Rota para excluir um barbeiro
    Route::delete('/admin/barbers/{id}', [\App\Http\Controllers\AdminController::class, 'destroyBarber']);

    // Rotas de Gestão de Serviços (Admin)//
    // Rota para listar serviços
    Route::get('/admin/services', [\App\Http\Controllers\AdminController::class, 'getServices']);
    // Rota para criar um novo serviço
    Route::post('/admin/services', [\App\Http\Controllers\AdminController::class, 'storeService']);
    // Rota para atualizar um serviço existente
    Route::put('/admin/services/{id}', [\App\Http\Controllers\AdminController::class, 'updateService']);
    // Rota para excluir um serviço
    Route::delete('/admin/services/{id}', [\App\Http\Controllers\AdminController::class, 'destroyService']);

    // Rotas de Notificações //
    // Rota para o Histórico Completo (NOVA)
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    // Rota para listar notificações não lidas do usuário
    Route::get('/notifications/unread', [\App\Http\Controllers\NotificationController::class, 'unread']);
    // Rota para marcar uma notificação como lida
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
});