<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Notifications are derived from real domain events (orders, onboarding)
 * with genuine per-user read tracking in `notification_reads`.
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->buildFor($user);

        $readKeys = DB::table('notification_reads')
            ->where('user_id', $user->id)
            ->pluck('notification_key')
            ->all();

        foreach ($notifications as &$notification) {
            $notification['read'] = in_array($notification['id'], $readKeys, true);
        }

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => collect($notifications)->where('read', false)->count(),
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        DB::table('notification_reads')->updateOrInsert(
            ['user_id' => $request->user()->id, 'notification_key' => $id],
            ['read_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        foreach ($this->buildFor($user) as $notification) {
            DB::table('notification_reads')->updateOrInsert(
                ['user_id' => $user->id, 'notification_key' => $notification['id']],
                ['read_at' => now(), 'updated_at' => now(), 'created_at' => now()]
            );
        }

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Derive the user's notification feed from real domain events.
     */
    protected function buildFor($user): array
    {
        $notifications = [];

        $orders = Order::where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        foreach ($orders as $order) {
            $isBuyer = $order->buyer_id === $user->id;
            $notifications[] = [
                'id' => 'order_' . $order->id . '_' . $order->status,
                'title' => $isBuyer ? 'Oda Yako: ' . ucfirst($order->status) : 'Oda Mpya: ' . ucfirst($order->status),
                'message' => 'Oda #' . substr($order->uuid, 0, 8) . ' — ' . $order->status,
                'type' => 'order',
                'read' => false, // resolved against notification_reads by caller
                'created_at' => $order->updated_at->toIso8601String(),
                'data' => ['order_uuid' => $order->uuid],
            ];
        }

        if ($user->created_at->diffInDays(now()) < 7) {
            $notifications[] = [
                'id' => 'welcome_' . $user->id,
                'title' => 'Karibu Mkulima Forum!',
                'message' => 'Asante kwa kujiunga. Anza kununua au kuuza bidhaa za kilimo.',
                'type' => 'system',
                'read' => false,
                'created_at' => $user->created_at->toIso8601String(),
                'data' => null,
            ];
        }

        return $notifications;
    }
}
