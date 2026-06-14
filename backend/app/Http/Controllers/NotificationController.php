<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {     
        $result = $this->notificationService
            ->getNotifications(
                $request->user(),
                $request->only([
                    'page',
                    'per_page',
                    'filter_type'
                ])
            );

        return response()->json([
            'data' => $result['data']
        ]);
    }

    public function markAsRead(
        Request $request,
        int $id
    ) {
        $result = $this->notificationService
            ->markAsRead(
                $request->user(),
                $id
            );

        if (!$result['success']) {

            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Đã đọc'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $result = $this->notificationService
            ->markAllAsRead(
                $request->user()
            );

        if (
            isset($result['success']) &&
            !$result['success']
        ) {
            return response()->json([
                'message' => $result['message']
            ], $result['code']);
        }

        return response()->json([
            'message' => 'Đã đọc tất cả'
        ]);
    }
}   