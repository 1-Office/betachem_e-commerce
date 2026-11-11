<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    private const STAFF_ROLES = [
        'user',
        'manager',
        'administrator',
        'superuser',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $role = $request->input('role');
        $status = $request->input('status');

        $query = User::query()
            ->whereIn('role', self::STAFF_ROLES)
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'role',
                'status',
                'created_at',
            ]);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($role, self::STAFF_ROLES, true)) {
            $query->where('role', $role);
        }

        if (in_array($status, ['active', 'inactive', 'blocked'], true)) {
            $query->where('status', $status);
        }

        $users = $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => User::whereIn('role', self::STAFF_ROLES)->count(),
            'active' => User::whereIn('role', self::STAFF_ROLES)->where('status', 'active')->count(),
            'inactive' => User::whereIn('role', self::STAFF_ROLES)->where('status', 'inactive')->count(),
            'blocked' => User::whereIn('role', self::STAFF_ROLES)->where('status', 'blocked')->count(),
            'superusers' => User::where('role', 'superuser')->where('status', 'active')->count(),
        ];

        return view('admin.users.index', compact(
            'users',
            'summary',
            'search',
            'role',
            'status'
        ));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'status' => ['required', 'in:active,inactive,blocked'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => $validated['status'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.users.edit', $user->id)
            ->with('success', 'Staff account created successfully.');
    }

    public function edit(int $user): View
    {
        $staff = User::whereIn('role', self::STAFF_ROLES)
            ->findOrFail($user);

        return view('admin.users.edit', compact('staff'));
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        $staff = User::whereIn('role', self::STAFF_ROLES)
            ->findOrFail($user);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', Rule::in(self::STAFF_ROLES)],
            'status' => ['required', 'in:active,inactive,blocked'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $isCurrentUser = (int) Auth::id() === (int) $staff->id;

        // Prevent a logged-in superuser from changing their own access.
        if ($isCurrentUser) {
            $validated['role'] = $staff->role;
            $validated['status'] = $staff->status;
        }

        // Never allow the last active superuser to lose active superuser access.
        $removingActiveSuperuser =
            $staff->role === 'superuser'
            && $staff->status === 'active'
            && (
                $validated['role'] !== 'superuser'
                || $validated['status'] !== 'active'
            );

        if (
            $removingActiveSuperuser
            && User::where('role', 'superuser')
                ->where('status', 'active')
                ->count() <= 1
        ) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'role' => 'The last active superuser cannot be demoted or deactivated.',
                ]);
        }

        $staff->first_name = $validated['first_name'];
        $staff->last_name = $validated['last_name'];
        $staff->email = $validated['email'];
        $staff->phone = $validated['phone'] ?? null;
        $staff->role = $validated['role'];
        $staff->status = $validated['status'];

        if (!empty($validated['password'])) {
            // The User model hashes passwords automatically.
            $staff->password = $validated['password'];
        }

        $staff->save();

        return redirect()
            ->route('admin.users.edit', $staff->id)
            ->with('success', 'Staff account updated successfully.');
    }
}
