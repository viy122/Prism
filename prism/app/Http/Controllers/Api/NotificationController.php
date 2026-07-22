<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrismNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function registerPushToken(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string|max:200']);

        auth()->user()->update(['expo_push_token' => $request->input('token')]);

        return response()->json(['success' => true]);
    }

    public function index(): JsonResponse
    {
        $userId = auth()->id();

        $items = PrismNotification::where('user_id', $userId)
            ->latest()
            ->take(50)
            ->get()
            ->map(fn (PrismNotification $n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'read_at'    => $n->read_at?->toIso8601String(),
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return response()->json([
            'items'  => $items,
            'unread' => PrismNotification::where('user_id', $userId)->whereNull('read_at')->count(),
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread' => PrismNotification::where('user_id', auth()->id())->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $n = PrismNotification::where('user_id', auth()->id())->findOrFail($id);
        if (!$n->read_at) {
            $n->update(['read_at' => now()]);
        }
        return response()->json(['success' => true]);
    }

    public function markAllRead(): JsonResponse
    {
        PrismNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
