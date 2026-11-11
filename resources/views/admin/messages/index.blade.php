@extends('layouts.admin')

@section('title', 'Contact Messages')

@section('content')

<style>
    .message-page {
        color: #263248;
    }

    .message-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .message-heading h2 {
        font-weight: 700;
        margin-bottom: 5px;
    }

    .message-muted {
        color: #778198;
        font-size: 13px;
    }

    .message-stat {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
    }

    .message-stat small {
        color: #778198;
        font-weight: 600;
    }

    .message-stat h3 {
        margin: 8px 0 0;
        font-weight: 800;
        color: #2D208A;
    }

    .message-stat .stat-icon {
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

    .message-card {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
        overflow: hidden;
    }

    .message-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #edf0f5;
        font-weight: 700;
    }

    .message-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        padding: 18px 20px;
        background: #fafbfe;
        border-bottom: 1px solid #edf0f5;
    }

    .message-filters .form-control,
    .message-filters .form-select {
        min-height: 42px;
        border-color: #e0e5ee;
        border-radius: 8px;
    }

    .message-table th {
        color: #788198;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 700;
        padding: 14px 16px;
        background: #fafbfe;
        white-space: nowrap;
    }

    .message-table td {
        padding: 15px 16px;
        vertical-align: middle;
        border-color: #edf0f5;
        font-size: 13px;
    }

    .message-table tbody tr:hover {
        background: #fafbff;
    }

    .sender-name {
        font-weight: 700;
        color: #263248;
    }

    .sender-email {
        font-size: 12px;
        color: #8992a5;
    }

    .message-subject {
        font-weight: 600;
        color: #303c55;
    }

    .message-status {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .status-new {
        background: #fff0e5;
        color: #c65d10;
    }

    .status-read {
        background: #e9f1ff;
        color: #2458b8;
    }

    .status-replied {
        background: #e6f7ef;
        color: #16834f;
    }

    .status-closed {
        background: #edf0f4;
        color: #687386;
    }

    .message-view-btn {
        background: #2D208A;
        color: #fff;
        border: 0;
        border-radius: 7px;
        padding: 7px 13px;
        font-size: 12px;
        text-decoration: none;
        display: inline-block;
    }

    .message-view-btn:hover {
        background: #1745CA;
        color: #fff;
    }

    .message-empty {
        padding: 55px 20px;
        text-align: center;
        color: #8992a5;
    }

    .message-empty i {
        font-size: 35px;
        margin-bottom: 12px;
        color: #b7bfd0;
    }

    @media(max-width: 767px) {
        .message-stat {
            padding: 15px;
        }

        .message-filters > * {
            width: 100%;
        }
    }
</style>

<div class="message-page">

    <div class="message-heading">
        <div>
            <h2>Contact Messages</h2>
            <div class="message-muted">
                Manage customer enquiries and website contact requests.
            </div>
        </div>

        <div class="message-muted">
            <i class="fa fa-envelope"></i>
            {{ $summary['total'] }} Total Messages
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">

        @foreach([
            ['label' => 'Total Messages', 'value' => $summary['total'], 'icon' => 'fa-envelope'],
            ['label' => 'New Messages', 'value' => $summary['new'], 'icon' => 'fa-envelope-open'],
            ['label' => 'Read Messages', 'value' => $summary['read'], 'icon' => 'fa-eye'],
            ['label' => 'Replied', 'value' => $summary['replied'], 'icon' => 'fa-reply'],
            ['label' => 'Closed', 'value' => $summary['closed'], 'icon' => 'fa-check-circle'],
        ] as $stat)

            <div class="col-6 col-md">
                <div class="message-stat">
                    <div class="d-flex justify-content-between align-items-center">
                        <small>{{ $stat['label'] }}</small>
                        <div class="stat-icon">
                            <i class="fa {{ $stat['icon'] }}"></i>
                        </div>
                    </div>

                    <h3>{{ number_format($stat['value']) }}</h3>
                </div>
            </div>

        @endforeach

    </div>

    {{-- Messages table --}}
    <div class="message-card">

        <div class="message-card-header">
            <i class="fa fa-inbox me-2"></i>
            Customer Enquiries
        </div>

        {{-- Search and filters --}}
        <form method="GET"
              action="{{ route('admin.messages.index') }}"
              class="message-filters">

            <div class="flex-grow-1">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search name, email or subject..."
                    value="{{ $search }}"
                >
            </div>

            <div style="min-width: 180px;">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="new" @selected($status === 'new')>New</option>
                    <option value="read" @selected($status === 'read')>Read</option>
                    <option value="replied" @selected($status === 'replied')>Replied</option>
                    <option value="closed" @selected($status === 'closed')>Closed</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-search me-1"></i> Search
            </button>

            <a href="{{ route('admin.messages.index') }}"
               class="btn btn-light border">
                Reset
            </a>

        </form>

        <div class="table-responsive">

            <table class="table message-table mb-0">

                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Phone</th>
                        <th>Received</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($messages as $message)

                        <tr>
                            <td>
                                <div class="sender-name">
                                    {{ $message->first_name }} {{ $message->last_name }}
                                </div>
                                <div class="sender-email">
                                    {{ $message->email }}
                                </div>
                            </td>

                            <td>
                                <div class="message-subject">
                                    {{ $message->subject }}
                                </div>
                            </td>

                            <td>
                                {{ $message->phone ?: '—' }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($message->created_at)->format('d M Y') }}
                                <div class="message-muted">
                                    {{ \Carbon\Carbon::parse($message->created_at)->format('H:i') }}
                                </div>
                            </td>

                            <td>
                                <span class="message-status status-{{ $message->status }}">
                                    {{ ucfirst($message->status) }}
                                </span>
                            </td>

                            <td class="text-end">
                                <a href="{{ route('admin.messages.show', $message->id) }}"
                                   class="message-view-btn">
                                    <i class="fa fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">
                                <div class="message-empty">
                                    <i class="fa fa-envelope-open-o d-block"></i>
                                    <strong>No messages found</strong>
                                    <div class="mt-2">
                                        Try changing your search or filter.
                                    </div>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($messages->hasPages())
            <div class="p-3 border-top">
                {{ $messages->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
