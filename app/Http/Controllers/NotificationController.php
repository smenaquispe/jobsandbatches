<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $notifications = $request->user()->notifications;
        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $idNotification)
    {
        $user = $request->user();
        $notification = $user->notifications()->find($idNotification);
        
        if(!$notification) {
            return response()->json([
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->notifications()->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }

    public function getUnreadNotifications(Request $request)
    {
        $user = $request->user();
        $unreadNotifications = $user->unreadNotifications;

        return response()->json([
            'notifications' => $unreadNotifications,
        ]);
    }
}
