<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display all customer accounts.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $status = $request->input('status');

        $query = DB::table('users')
            ->where('role', 'customer')
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'city',
                'province',
                'status',
                'created_at',
            ])
            ->selectSub(
                DB::table('orders')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('orders.user_id', 'users.id'),
                'orders_count'
            )
            ->selectSub(
                DB::table('orders')
                    ->selectRaw('COALESCE(SUM(total), 0)')
                    ->whereColumn('orders.user_id', 'users.id'),
                'orders_total'
            );

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (in_array($status, ['active', 'inactive', 'blocked'], true)) {
            $query->where('status', $status);
        }

        $customers = $query
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => DB::table('users')->where('role', 'customer')->count(),
            'active' => DB::table('users')->where('role', 'customer')->where('status', 'active')->count(),
            'inactive' => DB::table('users')->where('role', 'customer')->where('status', 'inactive')->count(),
            'blocked' => DB::table('users')->where('role', 'customer')->where('status', 'blocked')->count(),
        ];

        return view('admin.customers.index', compact(
            'customers',
            'summary',
            'search',
            'status'
        ));
    }

    /**
     * Display an individual customer and their order history.
     */
    public function show(int $customer): View
    {
        $customer = DB::table('users')
            ->where('role', 'customer')
            ->where('id', $customer)
            ->first();

        abort_if(!$customer, 404);

        $orders = DB::table('orders')
            ->where('user_id', $customer->id)
            ->select([
                'id',
                'order_number',
                'status',
                'payment_status',
                'total',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        $orderSummary = [
            'count' => DB::table('orders')
                ->where('user_id', $customer->id)
                ->count(),

            'total_spent' => DB::table('orders')
                ->where('user_id', $customer->id)
                ->where('payment_status', 'paid')
                ->sum('total'),
        ];

        return view('admin.customers.show', compact(
            'customer',
            'orders',
            'orderSummary'
        ));
    }

    /**
     * Update a customer's account status.
     */
    public function updateStatus(Request $request, int $customer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive,blocked'],
        ]);

        $updated = DB::table('users')
            ->where('id', $customer)
            ->where('role', 'customer')
            ->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ]);

        if (!$updated) {
            abort_if(
                !DB::table('users')
                    ->where('id', $customer)
                    ->where('role', 'customer')
                    ->exists(),
                404
            );
        }

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Customer account status updated successfully.');
    }
}
