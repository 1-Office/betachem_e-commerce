@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')

<style>
    .staff-page {
        color: #263248;
    }

    .staff-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .staff-heading h2 {
        font-weight: 700;
        margin-bottom: 5px;
    }

    .staff-muted {
        color: #778198;
        font-size: 13px;
    }

    .staff-stat {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        padding: 19px;
        height: 100%;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
    }

    .staff-stat small {
        color: #778198;
        font-weight: 600;
    }

    .staff-stat h3 {
        margin: 8px 0 0;
        font-weight: 800;
        color: #2D208A;
    }

    .staff-stat-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #f0efff;
        color: #2D208A;
        font-size: 18px;
    }

    .staff-card {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
        overflow: hidden;
    }

    .staff-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f5;
        font-weight: 700;
    }

    .staff-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 18px 20px;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
    }

    .staff-filters .form-control,
    .staff-filters .form-select {
        min-height: 42px;
        border-color: #e0e5ee;
        border-radius: 8px;
    }

    .staff-table th {
        color: #788198;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
        padding: 14px 16px;
        background: #fafbfe;
        white-space: nowrap;
    }

    .staff-table td {
        padding: 15px 16px;
        vertical-align: middle;
        border-color: #edf0f5;
        font-size: 13px;
    }

    .staff-table tbody tr:hover {
        background: #fafbff;
    }

    .staff-name {
        font-weight: 700;
        color: #263248;
    }

    .staff-email {
        font-size: 12px;
        color: #8992a5;
    }

    .staff-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .role-user {
        background: #edf0f4;
        color: #586579;
    }

    .role-manager {
        background: #e9f1ff;
        color: #2458b8;
    }

    .role-administrator {
        background: #f0edff;
        color: #5a40ad;
    }

    .role-superuser {
        background: #fff0e5;
        color: #c65d10;
    }

    .status-active {
        background: #e6f7ef;
        color: #16834f;
    }

    .status-inactive {
        background: #edf0f4;
        color: #687386;
    }

    .status-blocked {
        background: #ffe9e9;
        color: #bb3434;
    }

    .staff-edit-btn {
        background: #2D208A;
        color: #fff;
        border: 0;
        border-radius: 7px;
        padding: 7px 13px;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }

    .staff-edit-btn:hover {
        background: #1745CA;
        color: #fff;
    }

    .staff-empty {
        padding: 55px 20px;
        text-align: center;
        color: #8992a5;
    }

    .staff-empty i {
        font-size: 35px;
        margin-bottom: 12px;
        color: #b7bfd0;
    }

    .superuser-note {
        padding: 13px 16px;
        border-radius: 9px;
        background: #f5f3ff;
        color: #51439b;
        font-size: 12px;
        margin-bottom: 20px;
    }

    @media(max-width: 767px) {
        .staff-stat {
            padding: 15px;
        }

        .staff-filters > * {
            width: 100%;
        }
    }
</style>

<div class="staff-page">

    <div class="staff-heading">
        <div>
            <h2>Staff & User Management</h2>
            <div class="staff-muted">
                Manage Betachem administrators and staff accounts.
            </div>
        </div>

        <a href="{{ route('admin.users.create') }}"
           class="btn btn-primary">
            <i class="fa fa-plus me-2"></i>
            Add Staff User
        </a>
    </div>

    <div class="superuser-note">
        <i class="fa fa-shield me-2"></i>
        This section is restricted to superusers. Customer accounts are managed separately under Customers.
        There are currently {{ $summary['superusers'] }} active superuser account(s).
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">

        @foreach([
            ['label' => 'Total Staff', 'value' => $summary['total'], 'icon' => 'fa-users'],
            ['label' => 'Active', 'value' => $summary['active'], 'icon' => 'fa-check-circle'],
            ['label' => 'Inactive', 'value' => $summary['inactive'], 'icon' => 'fa-pause-circle'],
            ['label' => 'Blocked', 'value' => $summary['blocked'], 'icon' => 'fa-ban'],
        ] as $stat)

            <div class="col-6 col-md-3">
                <div class="staff-stat">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>{{ $stat['label'] }}</small>

                        <div class="staff-stat-icon">
                            <i class="fa {{ $stat['icon'] }}"></i>
                        </div>
                    </div>

                    <h3>{{ number_format($stat['value']) }}</h3>
                </div>
            </div>

        @endforeach

    </div>

    {{-- Staff table --}}
    <div class="staff-card">

        <div class="staff-card-header">
            <i class="fa fa-id-badge me-2"></i>
            Staff Accounts
        </div>

        <form method="GET"
              action="{{ route('admin.users.index') }}"
              class="staff-filters">

            <div class="flex-grow-1">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search name or email..."
                    value="{{ $search }}"
                >
            </div>

            <div style="min-width: 180px;">
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="user" @selected($role === 'user')>User</option>
                    <option value="manager" @selected($role === 'manager')>Manager</option>
                    <option value="administrator" @selected($role === 'administrator')>Administrator</option>
                    <option value="superuser" @selected($role === 'superuser')>Superuser</option>
                </select>
            </div>

            <div style="min-width: 150px;">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    <option value="blocked" @selected($status === 'blocked')>Blocked</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-search me-1"></i> Search
            </button>

            <a href="{{ route('admin.users.index') }}"
               class="btn btn-light border">
                Reset
            </a>

        </form>

        <div class="table-responsive">

            <table class="table staff-table mb-0">

                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($users as $staff)

                        <tr>
                            <td>
                                <div class="staff-name">
                                    {{ $staff->first_name }} {{ $staff->last_name }}
                                </div>

                                <div class="staff-email">
                                    {{ $staff->email }}
                                </div>
                            </td>

                            <td>{{ $staff->phone ?: '—' }}</td>

                            <td>
                                <span class="staff-badge role-{{ $staff->role }}">
                                    {{ ucfirst($staff->role) }}
                                </span>
                            </td>

                            <td>
                                <span class="staff-badge status-{{ $staff->status }}">
                                    {{ ucfirst($staff->status) }}
                                </span>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($staff->created_at)->format('d M Y') }}
                            </td>

                            <td class="text-end">
                                <a href="{{ route('admin.users.edit', $staff->id) }}"
                                   class="staff-edit-btn">
                                    <i class="fa fa-pencil me-1"></i>
                                    Manage
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">
                                <div class="staff-empty">
                                    <i class="fa fa-users d-block"></i>
                                    <strong>No staff accounts found</strong>
                                    <div class="mt-2">
                                        Add a staff account or change your search filters.
                                    </div>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($users->hasPages())
            <div class="p-3 border-top">
                {{ $users->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
