<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CanceledAppointmentNotification extends Notification
{
    use Queueable;

    private $clientName;
    private $startTime;

    public function __construct($clientName, $startTime)
    {
        $this->clientName = $clientName;
        $this->startTime = $startTime;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'canceled_appointment',
            'message' => "Cancelamento: {$this->clientName} desmarcou o serviço!",
            'start_time' => $this->startTime,
        ];
    }
}