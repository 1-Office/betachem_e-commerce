<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display notifications belonging to the logged-in customer.
     */
    public function index(): View
    {
        $user = Auth::user();

        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        $unreadCount = DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->count();

        return view('shop.notifications.index', compact(
            'notifications',
            'unreadCount'
        ));
    }

    /**
     * Mark one of the customer's notifications as read.
     */
    public function markRead(int $id): RedirectResponse
    {
        $user = Auth::user();

        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->update([
                'is_read' => 1,
            ]);

        return redirect()
            ->route('shop.notifications.index')
            ->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all of the customer's notifications as read.
     */
    public function markAllRead(): RedirectResponse
    {
        $user = Auth::user();

        DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
            ]);

        return redirect()
            ->route('shop.notifications.index')
            ->with('success', 'All notifications have been marked as read.');
    }
}