<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Roles permitted to access the Admin Portal.
     */
    private const ADMIN_ROLES = [
        'user',
        'manager',
        'administrator',
        'superuser',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Require an authenticated account.
        if (!$user) {
            return redirect()->route('shop.login');
        }

        // Only active accounts may access administration.
        if ($user->status !== 'active') {
            abort(403, 'Your account is not authorised to access the Admin Portal.');
        }

        // Check administrator role.
        if (!in_array($user->role, self::ADMIN_ROLES, true)) {
            abort(403, 'You do not have permission to access the Admin Portal.');
        }

        return $next($request);
    }
}
