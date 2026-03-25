<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unread(Request $request)
    {
        $notifications = $request->user()->unreadNotifications;
        
        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notificação marcada como lida.']);
        }

        return response()->json(['message' => 'Notificação não encontrada.'], 404);
    }
}