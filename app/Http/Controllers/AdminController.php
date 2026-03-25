<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Expense;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function getDashboardMetrics(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado. Exclusivo para administradores.'], 403);
        }

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $completedAppointments = Appointment::with('service')
            ->where('status', 'completed')
            ->whereMonth('start_time', $currentMonth)
            ->whereYear('start_time', $currentYear)
            ->get();
        
        $revenue = $completedAppointments->sum(function($app) {
            return $app->service ? $app->service->price : 0;
        });

        $scheduledAppointments = Appointment::with('service')
            ->where('status', 'scheduled')
            ->whereMonth('start_time', $currentMonth)
            ->whereYear('start_time', $currentYear)
            ->get();
        
        $forecast = $scheduledAppointments->sum(function($app) {
            return $app->service ? $app->service->price : 0;
        });

        $allExpenses = Expense::whereMonth('expense_date', $currentMonth)
            ->whereYear('expense_date', $currentYear)
            ->get();
        $expenses = $allExpenses->sum('amount');

        $profit = $revenue - $expenses;
        $totalAppointments = $completedAppointments->count() + $scheduledAppointments->count();

        $daysInMonth = Carbon::now()->daysInMonth;
        $chartLabels = [];
        $chartRevenue = [];
        $chartExpenses = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $chartLabels[] = str_pad($i, 2, '0', STR_PAD_LEFT); 
            $dayDate = Carbon::now()->setDay($i)->format('Y-m-d');
            
            $dayRev = $completedAppointments->filter(function($app) use ($dayDate) {
                return Carbon::parse($app->start_time)->format('Y-m-d') === $dayDate;
            })->sum(function($app) {
                return $app->service ? $app->service->price : 0;
            });

            $dayExp = $allExpenses->filter(function($exp) use ($dayDate) {
                return Carbon::parse($exp->expense_date)->format('Y-m-d') === $dayDate;
            })->sum('amount');

            $chartRevenue[] = $dayRev;
            $chartExpenses[] = $dayExp;
        }

        return response()->json([
            'current_month' => Carbon::now()->translatedFormat('F/Y'),
            'metrics' => [
                'revenue' => $revenue,
                'forecast' => $forecast,
                'total_projected' => $revenue + $forecast,
                'expenses' => $expenses,
                'profit' => $profit,
                'total_appointments' => $totalAppointments
            ],
            'chart' => [
                'labels' => $chartLabels,
                'revenue' => $chartRevenue,
                'expenses' => $chartExpenses
            ]
        ]);
    }

    public function getExpenses(Request $request)
    {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);
        
        return Expense::orderBy('expense_date', 'desc')->get();
    }

    public function storeExpense(Request $request)
    {
        if ($request->user()->role !== 'admin') return response()->json(['message' => 'Acesso negado.'], 403);

        $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string'
        ]);

        $expense = Expense::create($request->all());

        return response()->json(['message' => 'Despesa registrada com sucesso!', 'expense' => $expense]);
    }
}