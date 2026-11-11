<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    /**
     * Permissions for each administration section.
     */
    private const PERMISSIONS = [
        'dashboard' => [
            'user',
            'manager',
            'administrator',
            'superuser',
        ],

        'categories' => [
            'manager',
            'administrator',
            'superuser',
        ],

        'products' => [
            'user',
            'manager',
            'administrator',
            'superuser',
        ],

        'orders' => [
            'manager',
            'administrator',
            'superuser',
        ],

        'customers' => [
            'manager',
            'administrator',
            'superuser',
        ],

        'contact_messages' => [
            'manager',
            'administrator',
            'superuser',
        ],

        'admin_users' => [
            'superuser',
        ],
    ];

    /**
     * Check access to an administration section.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('shop.login');
        }

        $allowedRoles = self::PERMISSIONS[$permission] ?? [];

        if (!in_array($user->role, $allowedRoles, true)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}