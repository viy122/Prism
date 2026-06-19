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
            ->get(['id', 'type', 'title', 'message', 'action_url', 'read_at', 'created_at']);

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
