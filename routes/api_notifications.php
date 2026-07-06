<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Order;

Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
    Route::get('/', function (Request $request) {
        $user = $request->user();
        $notifications = [];
        
        $orders = Order::where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        foreach ($orders as $order) {
            $notifications[] = [
                'id' => 'order_' . $order->id,
                'title' => 'Order ' . ucfirst($order->status),
                'message' => 'Order #' . substr($order->uuid, 0, 8) . ' is ' . $order->status,
                'type' => 'order',
                'read' => $order->status !== 'pending',
                'created_at' => $order->created_at->toIso8601String(),
                'data' => ['order_uuid' => $order->uuid],
            ];
        }
        
        if ($user->created_at->diffInDays(now()) < 7) {
            $notifications[] = [
                'id' => 'welcome',
                'title' => 'Karibu Mkulima Forum!',
                'message' => 'Asante kwa kujiunga. Anza kununua au kuuza bidhaa za kilimo.',
                'type' => 'system',
                'read' => false,
                'created_at' => $user->created_at->toIso8601String(),
                'data' => null,
            ];
        }
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => collect($notifications)->where('read', false)->count(),
        ]);
    });
    
    Route::post('/{id}/read', function (Request $request, $id) {
        return response()->json(['message' => 'Notification marked as read']);
    });
    
    Route::post('/read-all', function (Request $request) {
        return response()->json(['message' => 'All notifications marked as read']);
    });
});
