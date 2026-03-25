<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAppointmentNotification extends Notification
{
    use Queueable;

    private $appointment;
    private $clientName;

    // Recebe os dados do agendamento quando a notificação é disparada
    public function __construct($appointment, $clientName)
    {
        $this->appointment = $appointment;
        $this->clientName = $clientName;
    }

    // Define POR ONDE a notificação vai ser enviada (Neste caso, Banco de Dados)
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Define O QUE vai ser salvo na coluna 'data' do banco de dados
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_appointment',
            'message' => "Você tem um novo agendamento com {$this->clientName}!",
            'appointment_id' => $this->appointment->id,
            'start_time' => $this->appointment->start_time,
        ];
    }
}