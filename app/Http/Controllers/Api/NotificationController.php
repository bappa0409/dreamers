<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'unread'=>'nullable|boolean',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $query=$request->user()
            ->notifications()
            ->latest();

        if($request->boolean('unread')){
            $query->whereNull('read_at');
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??15),100)
            ),
            'unread_count'=>$this->notificationService
                ->unreadCount($request->user())
        ]);
    }

    public function unread(Request $request)
    {
        $notifications=$request->user()
            ->unreadNotifications()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success'=>true,
            'count'=>$this->notificationService
                ->unreadCount($request->user()),
            'notifications'=>$notifications
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'success'=>true,
            'count'=>$this->notificationService
                ->unreadCount($request->user())
        ]);
    }

    public function markAsRead(Request $request,string $id)
    {
        $this->notificationService->markRead(
            $request->user(),
            $id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Notification marked as read.'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllRead(
            $request->user()
        );

        return response()->json([
            'success'=>true,
            'message'=>'All notifications marked as read.'
        ]);
    }

    public function destroy(Request $request,string $id)
    {
        $this->notificationService->delete(
            $request->user(),
            $id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Notification deleted successfully.'
        ]);
    }
}