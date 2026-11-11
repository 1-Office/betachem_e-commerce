@extends('layouts.shop')

@section('title', 'Notifications | Betachem')

@section('content')

<style>
    .notifications-page {
        padding: 45px 0 65px;
        min-height: 65vh;
        font-family: 'Raleway', sans-serif;
    }

    .notifications-heading {
        color: #172b4d;
        font-weight: 800;
        margin-bottom: 7px;
    }

    .notifications-subheading {
        color: #6c757d;
        font-size: 14px;
    }

    .notifications-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 27px;
        height: 27px;
        padding: 0 8px;
        border-radius: 30px;
        background: #dc3545;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        margin-left: 7px;
    }

    /* NOTIFICATION CARD */

    .notification-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        margin-bottom: 13px;
        background: #fff;
        border: 1px solid #e8edf3;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, .035);
        transition: .2s ease;
    }

    .notification-card:hover {
        box-shadow: 0 5px 18px rgba(0, 0, 0, .075);
        transform: translateY(-1px);
    }

    .notification-card.unread {
        background: #f4f8ff;
        border-left: 4px solid #1769aa;
    }

    /* ICON */

    .notification-icon {
        flex: 0 0 45px;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e8f1fb;
        color: #1769aa;
        font-size: 18px;
    }

    /* NOTIFICATION INFORMATION */

    .notification-content {
        flex: 1;
        min-width: 0;
    }

    .notification-title {
        color: #172b4d;
        font-size: 15px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .notification-message {
        color: #606b78;
        font-size: 13px;
        line-height: 1.7;
        margin-bottom: 9px;
        overflow-wrap: anywhere;
    }

    .notification-date {
        color: #929ba5;
        font-size: 11px;
    }

    /* ACTION BUTTONS ON THE RIGHT */

    .notification-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        gap: 8px;
        margin-left: auto;
        flex-shrink: 0;
    }

    .notification-action {
        display: inline-block;
        min-width: 125px;
        text-align: center;
        padding: 9px 12px;
        border-radius: 6px;
        border: 1px solid #1769aa;
        background: #1769aa;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: .2s ease;
    }

    .notification-action:hover {
        background: #125487;
        border-color: #125487;
        color: #fff;
    }

    .notification-read-button {
        min-width: 125px;
        padding: 9px 12px;
        border: 1px solid #d8e0e8;
        border-radius: 6px;
        background: #fff;
        color: #526171;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s ease;
    }

    .notification-read-button:hover {
        background: #edf2f7;
        border-color: #b9c7d5;
    }

    /* EMPTY NOTIFICATIONS */

    .notifications-empty {
        text-align: center;
        background: #fff;
        border: 1px solid #e8edf3;
        border-radius: 12px;
        padding: 55px 20px;
    }

    .notifications-empty i {
        color: #b5c0cc;
        font-size: 42px;
        margin-bottom: 15px;
    }

    .notifications-empty h4 {
        color: #172b4d;
        font-weight: 800;
    }

    .notifications-empty p {
        color: #7a8490;
        font-size: 13px;
    }

    /* PAGE TOOLBAR */

    .notifications-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .notifications-toolbar .btn {
        font-size: 12px;
        font-weight: 700;
        border-radius: 7px;
        padding: 9px 14px;
    }

    /* TABLET AND MOBILE */

    @media (max-width: 768px) {
        .notification-card {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .notification-content {
            flex: 1;
        }

        .notification-actions {
            width: 100%;
            flex-direction: row;
            justify-content: flex-end;
            align-items: center;
            margin-left: 0;
            padding-left: 49px;
        }
    }

    @media (max-width: 576px) {
        .notifications-page {
            padding: 30px 0 45px;
        }

        .notification-card {
            padding: 15px;
            gap: 11px;
        }

        .notification-icon {
            flex-basis: 38px;
            width: 38px;
            height: 38px;
            font-size: 15px;
        }

        .notification-actions {
            padding-left: 0;
            flex-wrap: wrap;
        }

        .notification-action,
        .notification-read-button {
            min-width: 0;
        }
    }
</style>

<div class="container notifications-page">

    {{-- PAGE HEADER --}}

    <div class="notifications-toolbar">

        <div>
            <h2 class="notifications-heading">
                My Notifications

                @if($unreadCount > 0)
                    <span class="notifications-count">
                        {{ $unreadCount }}
                    </span>
                @endif
            </h2>

            <p class="notifications-subheading mb-0">
                Stay updated about your orders and payments.
            </p>
        </div>

        @if($unreadCount > 0)

            <form
                method="POST"
                action="{{ route('shop.notifications.read-all') }}"
            >
                @csrf

                <button type="submit" class="btn btn-outline-primary">
                    <i class="fa fa-check me-1"></i>
                    Mark All as Read
                </button>
            </form>

        @endif

    </div>

    {{-- SUCCESS MESSAGE --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    @endif

    {{-- NOTIFICATIONS LIST --}}

    @if($notifications->count())

        @foreach($notifications as $notification)

            @php
                $icon = match($notification->type) {
                    'order_placed' => 'fa-shopping-bag',
                    'order_status' => 'fa-truck',
                    'payment' => 'fa-credit-card',
                    default => 'fa-bell',
                };
            @endphp

            <div class="notification-card {{ !$notification->is_read ? 'unread' : '' }}">

                {{-- LEFT: ICON --}}

                <div class="notification-icon">
                    <i class="fa {{ $icon }}"></i>
                </div>

                {{-- MIDDLE: NOTIFICATION DETAILS --}}

                <div class="notification-content">

                    <h5 class="notification-title">

                        {{ $notification->title }}

                        @if(!$notification->is_read)
                            <span class="badge bg-primary ms-1">
                                New
                            </span>
                        @endif

                    </h5>

                    <p class="notification-message">
                        {{ $notification->message }}
                    </p>

                    <div class="notification-date">

                        <i class="fa fa-clock-o me-1"></i>

                        {{ \Carbon\Carbon::parse($notification->created_at)->format('d M Y, H:i') }}

                    </div>

                </div>

                {{-- RIGHT: ACTION BUTTONS --}}

                <div class="notification-actions">

                    @if($notification->order_id)

                        <a
                            href="{{ route('shop.account.orders.show', $notification->order_id) }}"
                            class="notification-action"
                        >
                            <i class="fa fa-eye me-1"></i>
                            View Order
                        </a>

                    @endif

                    @if(!$notification->is_read)

                        <form
                            method="POST"
                            action="{{ route('shop.notifications.read', $notification->id) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="notification-read-button"
                            >
                                <i class="fa fa-check me-1"></i>
                                Mark as Read
                            </button>

                        </form>

                    @endif

                </div>

            </div>

        @endforeach

        {{-- PAGINATION --}}

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>

    @else

        {{-- EMPTY STATE --}}

        <div class="notifications-empty">

            <i class="fa fa-bell-o"></i>

            <h4>No Notifications Yet</h4>

            <p class="mb-0">
                You don't have any notifications at the moment.
                We'll notify you when there are updates to your orders or payments.
            </p>

        </div>

    @endif

</div>

@endsection