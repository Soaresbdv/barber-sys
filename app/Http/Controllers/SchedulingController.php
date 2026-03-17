<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->appointments()
            ->with('service') 
            ->orderBy('start_time', 'desc')
            ->get();
    }

    public function barbers()
    {
        return User::where('role', 'barber')->get(['id', 'name', 'avatar']);
    }

    public function services()
    {
        return Service::where('active', true)->get();
    }

    public function availability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'barber_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id' 
        ]);

        $date = Carbon::parse($request->date);
        $barberId = $request->barber_id;
        
        $serviceDuration = Service::find($request->service_id)->duration_minutes;

        $startOfDay = $date->copy()->setHour(9)->setMinute(0)->setSecond(0);
        $endOfDay = $date->copy()->setHour(19)->setMinute(0)->setSecond(0);

        $existingAppointments = Appointment::where('barber_id', $barberId)
            ->whereDate('start_time', $date)
            ->where('status', '!=', 'canceled') 
            ->get();

        $slots = [];
        $currentSlot = $startOfDay->copy();

        while ($currentSlot->lessThan($endOfDay)) {
            
            $potentialEnd = $currentSlot->copy()->addMinutes($serviceDuration);

            if ($potentialEnd->greaterThan($endOfDay)) {
                break;
            }

            $isAvailable = true;

            foreach ($existingAppointments as $appointment) {
                
                $appStart = Carbon::parse($appointment->start_time);
                $appEnd = Carbon::parse($appointment->end_time);

                if ($currentSlot->lessThan($appEnd) && $potentialEnd->greaterThan($appStart)) {
                    $isAvailable = false;
                    break; 
                }
            }

            if ($isAvailable) {
                $slots[] = $currentSlot->format('H:i');
            }

            $currentSlot->addMinutes(30);
        }

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'available_slots' => $slots
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i' 
        ]);

        $startTime = Carbon::parse($request->date . ' ' . $request->time);
        
        $service = Service::find($request->service_id);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        $exists = Appointment::where('barber_id', $request->barber_id)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime]);
            })->exists();

        if ($exists) {
            return response()->json(['message' => 'Desculpe, esse horário acabou de ser ocupado!'], 409);
        }

        Appointment::create([
            'user_id' => $request->user()->id,
            'barber_id' => $request->barber_id,
            'service_id' => $request->service_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'scheduled'
        ]);

        return response()->json(['message' => 'Agendamento realizado com sucesso!']);
    }  
    public function destroy($id)
{
    $appointment = auth()->user()->appointments()->findOrFail($id);
    $appointment->delete(); 
    return response()->json(['message' => 'Agendamento cancelado com sucesso.']);
}

public function update(Request $request, $id)
{
    $appointment = $request->user()->appointments()->findOrFail($id);

    $request->validate([
        'barber_id' => 'required|exists:users,id',
        'service_id' => 'required|exists:services,id',
        'date' => 'required|date',
        'time' => 'required|date_format:H:i' 
    ]);

    $startTime = Carbon::parse($request->date . ' ' . $request->time);
    $service = Service::find($request->service_id);
    $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

    $exists = Appointment::where('barber_id', $request->barber_id)
        ->where('id', '!=', $id) 
        ->where('status', '!=', 'canceled')
        ->where(function ($query) use ($startTime, $endTime) {
            $query->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime]);
        })->exists();

    if ($exists) {
        return response()->json(['message' => 'Este horário choca com outro agendamento!'], 409);
    }

    $appointment->update([
        'barber_id' => $request->barber_id,
        'service_id' => $request->service_id,
        'start_time' => $startTime,
        'end_time' => $endTime,
    ]);

    return response()->json(['message' => 'Agendamento atualizado com sucesso!']);
}

public function barberAgenda(Request $request)
    {
        if ($request->user()->role !== 'barber' && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado. Área restrita para profissionais.'], 403);
        }

        $appointments = Appointment::with(['service', 'user'])
            ->where('barber_id', $request->user()->id)
            ->orderBy('start_time', 'asc')
            ->get();

        return response()->json($appointments);
    }

    public function updateStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'barber' && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'status' => 'required|in:completed,canceled'
        ]);

        $appointment = Appointment::where('id', $id)
            ->where('barber_id', $request->user()->id)
            ->firstOrFail();

        $appointment->update([
            'status' => $request->status
        ]);

        return response()->json(['message' => 'Status atualizado com sucesso!']);
    }
}