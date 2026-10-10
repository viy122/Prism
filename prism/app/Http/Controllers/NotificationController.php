<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = auth()->user()
            ->prismNotifications()
            ->latest()
            ->take(20)
            ->get(['id', 'type', 'title', 'message', 'action_url', 'data_json', 'read_at', 'created_at'])
            ->map(fn ($notification) => [
                'id'         => $notification->id,
                'type'       => $notification->type,
                'title'      => $notification->title,
                'message'    => $notification->message,
                'action_url' => $notification->action_url,
                'data'       => $notification->data_json,
                'read_at'    => $notification->read_at,
                'created_at' => $notification->created_at,
            ]);

        return response()->json($notifications);
    }

    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()
            ->prismNotifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(int $id): JsonResponse
    {
        auth()->user()
            ->prismNotifications()
            ->where('id', $id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        auth()->user()
            ->prismNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
