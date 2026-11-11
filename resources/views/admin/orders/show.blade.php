@extends('layouts.admin')

@section('title', 'Order Details | Betachem')

@section('content')

<style>
.admin-order-details {
    background: #f4f7fc;
    min-height: 100vh;
    padding: 30px 0 60px;
    color: #24344d;
}

.admin-order-details .page-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 25px;
}

.admin-order-details .page-heading h2 {
    font-size: 27px;
    font-weight: 800;
    margin-bottom: 5px;
    color: #202d45;
}

.admin-order-details .page-heading p {
    color: #77849a;
    margin: 0;
}

.admin-order-details .panel {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    box-shadow: 0 4px 15px rgba(30, 50, 90, .035);
    margin-bottom: 22px;
    overflow: hidden;
}

.admin-order-details .panel-header {
    padding: 18px 22px;
    border-bottom: 1px solid #edf0f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.admin-order-details .panel-header h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #263650;
}

.admin-order-details .panel-body {
    padding: 22px;
}

.admin-order-details .detail-label {
    font-size: 11px;
    font-weight: 800;
    color: #8994a6;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 6px;
}

.admin-order-details .detail-value {
    font-size: 13px;
    font-weight: 600;
    color: #263650;
    overflow-wrap: anywhere;
}

.admin-order-details .detail-block {
    margin-bottom: 19px;
}

.admin-order-details .detail-block:last-child {
    margin-bottom: 0;
}

.admin-order-details .status-badge {
    display: inline-block;
    border-radius: 30px;
    padding: 7px 11px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-order-details .status-pending,
.admin-order-details .payment-pending {
    background: #fff3d9;
    color: #a36b00;
}

.admin-order-details .status-processing,
.admin-order-details .payment-partial {
    background: #e8efff;
    color: #2854bd;
}

.admin-order-details .status-ready {
    background: #e5f4ff;
    color: #1670a5;
}

.admin-order-details .status-shipped,
.admin-order-details .payment-refunded {
    background: #f0eaff;
    color: #6843b9;
}

.admin-order-details .status-completed,
.admin-order-details .payment-paid {
    background: #e5f7ed;
    color: #18834b;
}

.admin-order-details .status-cancelled,
.admin-order-details .payment-failed {
    background: #fde8e8;
    color: #bd3434;
}

.admin-order-details .form-label {
    font-size: 12px;
    font-weight: 700;
    color: #64738b;
    margin-bottom: 7px;
}

.admin-order-details .form-control,
.admin-order-details .form-select {
    min-height: 43px;
    border: 1px solid #dfe6f0;
    border-radius: 8px;
    font-size: 13px;
    box-shadow: none;
}

.admin-order-details .form-control:focus,
.admin-order-details .form-select:focus {
    border-color: #1745ca;
    box-shadow: 0 0 0 3px rgba(23, 69, 202, .08);
}

.admin-order-details .btn-primary {
    background: #1745ca;
    border-color: #1745ca;
    border-radius: 8px;
    font-weight: 700;
    font-size: 12px;
    padding: 10px 16px;
}

.admin-order-details .btn-primary:hover {
    background: #2d208a;
    border-color: #2d208a;
}

.admin-order-details .btn-back {
    background: #eef2f8;
    color: #46566f;
    border-radius: 8px;
    min-height: 40px;
    padding: 9px 15px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
}

.admin-order-details .btn-back:hover {
    background: #e1e8f3;
    color: #263650;
}

.admin-order-details .table {
    margin: 0;
    vertical-align: middle;
}

.admin-order-details .table thead th {
    background: #f8fafd;
    color: #758198;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 13px 16px;
    border-bottom: 1px solid #e9edf4;
    white-space: nowrap;
}

.admin-order-details .table tbody td {
    padding: 15px 16px;
    border-color: #edf0f5;
    font-size: 12px;
}

.admin-order-details .product-name {
    color: #263650;
    font-weight: 700;
}

.admin-order-details .muted {
    color: #8994a6;
    font-size: 11px;
}

.admin-order-details .total-row {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 9px 0;
    color: #64738b;
    font-size: 13px;
}

.admin-order-details .total-row strong {
    color: #263650;
    font-weight: 700;
}

.admin-order-details .grand-total {
    border-top: 1px solid #e8edf5;
    margin-top: 8px;
    padding-top: 16px;
    font-size: 16px;
    font-weight: 800;
    color: #263650;
}

.admin-order-details .proof-box {
    background: #f8fafd;
    border: 1px solid #e8edf5;
    border-radius: 10px;
    padding: 16px;
}

.admin-order-details .proof-box i {
    color: #1745ca;
    font-size: 23px;
}

.admin-order-details .alert {
    font-size: 13px;
    border-radius: 9px;
}

.admin-order-details .notes-box {
    background: #f8fafd;
    border: 1px solid #e8edf5;
    padding: 14px;
    border-radius: 9px;
    font-size: 13px;
    color: #46566f;
    white-space: pre-wrap;
}

@media (max-width: 767px) {
    .admin-order-details {
        padding: 20px 0 40px;
    }

    .admin-order-details .page-heading h2 {
        font-size: 23px;
    }

    .admin-order-details .panel-body {
        padding: 17px;
    }
}
</style>

@php
    $orderStatusClasses = [
        'pending' => 'status-pending',
        'processing' => 'status-processing',
        'ready' => 'status-ready',
        'shipped' => 'status-shipped',
        'completed' => 'status-completed',
        'cancelled' => 'status-cancelled',
    ];

    $paymentStatusClasses = [
        'pending' => 'payment-pending',
        'paid' => 'payment-paid',
        'failed' => 'payment-failed',
        'partial' => 'payment-partial',
        'refunded' => 'payment-refunded',
    ];
@endphp

<div class="admin-order-details">
    <div class="container-fluid px-3 px-lg-4">

        {{-- Page Heading --}}
        <div class="page-heading">
            <div>
                <h2>Order Details</h2>
                <p>
                    Order {{ $order->order_number }}
                    &nbsp;·&nbsp;
                    Placed {{ \Carbon\Carbon::parse($order->created_at)->format('d F Y, H:i') }}
                </p>
            </div>

            <a href="{{ route('admin.orders.index') }}" class="btn-back">
                <i class="fa fa-arrow-left me-2"></i> Back to Orders
            </a>
        </div>

        {{-- Feedback Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">

            {{-- Main Column --}}
            <div class="col-12 col-xl-8">

                {{-- Order Items --}}
                <div class="panel">
                    <div class="panel-header">
                        <h5><i class="fa fa-shopping-bag me-2 text-primary"></i> Ordered Products</h5>
                        <span class="muted">{{ $items->count() }} line items</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="product-name">
                                                {{ $item->product_name }}
                                            </div>
                                        </td>
                                        <td>{{ $item->sku ?: '—' }}</td>
                                        <td>R {{ number_format((float) $item->price, 2) }}</td>
                                        <td>{{ number_format($item->quantity) }}</td>
                                        <td class="text-end fw-bold">
                                            R {{ number_format((float) $item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No order items were found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="panel-body">
                        <div class="row justify-content-end">
                            <div class="col-12 col-md-7 col-lg-6">

                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <strong>R {{ number_format((float) $order->subtotal, 2) }}</strong>
                                </div>

                                <div class="total-row">
                                    <span>Delivery Fee</span>
                                    <strong>R {{ number_format((float) $order->delivery_fee, 2) }}</strong>
                                </div>

                                <div class="total-row">
                                    <span>Discount</span>
                                    <strong>R {{ number_format((float) $order->discount, 2) }}</strong>
                                </div>

                                <div class="total-row grand-total">
                                    <span>Order Total</span>
                                    <strong>R {{ number_format((float) $order->total, 2) }}</strong>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Customer and Delivery Information --}}
                <div class="panel">
                    <div class="panel-header">
                        <h5><i class="fa fa-user me-2 text-primary"></i> Customer & Delivery Information</h5>
                    </div>

                    <div class="panel-body">
                        <div class="row g-4">

                            <div class="col-12 col-md-6">
                                <div class="detail-block">
                                    <div class="detail-label">Customer Name</div>
                                    <div class="detail-value">
                                        {{ $order->shipping_first_name }}
                                        {{ $order->shipping_last_name }}
                                    </div>
                                </div>

                                <div class="detail-block">
                                    <div class="detail-label">Email Address</div>
                                    <div class="detail-value">
                                        {{ $order->shipping_email }}
                                    </div>
                                </div>

                                <div class="detail-block">
                                    <div class="detail-label">Phone Number</div>
                                    <div class="detail-value">
                                        {{ $order->shipping_phone }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="detail-block">
                                    <div class="detail-label">Delivery Address</div>
                                    <div class="detail-value">
                                        {{ $order->shipping_address }}
                                    </div>
                                </div>

                                <div class="detail-block">
                                    <div class="detail-label">City & Province</div>
                                    <div class="detail-value">
                                        {{ $order->shipping_city }},
                                        {{ $order->shipping_province }}
                                    </div>
                                </div>

                                <div class="detail-block">
                                    <div class="detail-label">Postal Code</div>
                                    <div class="detail-value">
                                        {{ $order->shipping_postal_code }}
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Customer Notes --}}
                @if ($order->notes)
                    <div class="panel">
                        <div class="panel-header">
                            <h5><i class="fa fa-sticky-note me-2 text-primary"></i> Customer Notes</h5>
                        </div>
                        <div class="panel-body">
                            <div class="notes-box">{{ $order->notes }}</div>
                        </div>
                    </div>
                @endif

                {{-- Payment History --}}
                <div class="panel">
                    <div class="panel-header">
                        <h5><i class="fa fa-history me-2 text-primary"></i> Payment History</h5>
                    </div>

                    @if ($payments->count())
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($payments as $paymentRecord)
                                        <tr>
                                            <td>{{ $paymentRecord->payment_method ?: '—' }}</td>
                                            <td>{{ $paymentRecord->transaction_reference ?: '—' }}</td>
                                            <td class="fw-bold">
                                                R {{ number_format((float) $paymentRecord->amount, 2) }}
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $paymentStatusClasses[$paymentRecord->status] ?? 'payment-pending' }}">
                                                    {{ ucfirst($paymentRecord->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($paymentRecord->created_at)->format('d M Y, H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="panel-body">
                            <p class="text-muted mb-0" style="font-size:13px;">
                                No payment record has been created yet.
                            </p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Sidebar --}}
            <div class="col-12 col-xl-4">

                {{-- Order Status --}}
                <div class="panel">
                    <div class="panel-header">
                        <h5><i class="fa fa-tasks me-2 text-primary"></i> Order Processing</h5>
                    </div>

                    <div class="panel-body">

                        <div class="detail-block">
                            <div class="detail-label">Current Order Status</div>
                            <span class="status-badge {{ $orderStatusClasses[$order->status] ?? 'status-pending' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>

                        <form method="POST"
                              action="{{ route('admin.orders.status.update', $order->id) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="status" class="form-label">
                                    Update Order Status
                                </label>

                                <select name="status" id="status" class="form-select" required>
                                    @foreach (['pending', 'processing', 'ready', 'shipped', 'completed', 'cancelled'] as $status)
                                        <option value="{{ $status }}"
                                            {{ $order->status === $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-save me-1"></i> Update Order Status
                            </button>
                        </form>

                    </div>
                </div>

                {{-- Payment Information --}}
                <div class="panel">
                    <div class="panel-header">
                        <h5><i class="fa fa-credit-card me-2 text-primary"></i> Payment Information</h5>
                    </div>

                    <div class="panel-body">

                        <div class="detail-block">
                            <div class="detail-label">Order Payment Status</div>
                            <span class="status-badge {{ $paymentStatusClasses[$order->payment_status] ?? 'payment-pending' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>

                        <div class="detail-block">
                            <div class="detail-label">Payment Method</div>
                            <div class="detail-value">
                                {{ $order->payment_method ? strtoupper($order->payment_method) : 'Not specified' }}
                            </div>
                        </div>

                        @if ($payment)
                            <div class="detail-block">
                                <div class="detail-label">Transaction Reference</div>
                                <div class="detail-value">
                                    {{ $payment->transaction_reference ?: 'Not supplied' }}
                                </div>
                            </div>

                            <div class="detail-block">
                                <div class="detail-label">Proof Uploaded</div>
                                <div class="detail-value">
                                    {{ $payment->proof_uploaded_at
                                        ? \Carbon\Carbon::parse($payment->proof_uploaded_at)->format('d M Y, H:i')
                                        : 'No proof upload date recorded' }}
                                </div>
                            </div>

                            @if ($payment->proof_of_payment)
                                <div class="proof-box mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="fa fa-file-pdf-o"></i>
                                        <div>
                                            <div class="detail-value">Proof of Payment</div>
                                            <div class="muted">Private PDF document</div>
                                        </div>
                                    </div>

                                    <a href="{{ route('admin.orders.proof.download', $order->id) }}"
                                       class="btn btn-outline-primary btn-sm w-100 mt-3">
                                        <i class="fa fa-download me-1"></i>
                                        Download PDF
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fa fa-exclamation-circle me-1"></i>
                                    No proof of payment has been uploaded.
                                </div>
                            @endif

                        @else
                            <div class="alert alert-warning">
                                No payment record exists for this order yet.
                            </div>
                        @endif

                        @if ($order->payment_status !== 'paid')
                            <hr>

                            <form method="POST"
                                  action="{{ route('admin.orders.payment.update', $order->id) }}"
                                  onsubmit="return confirm('Are you sure you want to update this payment decision?');">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">
                                        Payment Verification Decision
                                    </label>

                                    <select name="payment_status"
                                            id="payment_status"
                                            class="form-select"
                                            required>
                                        <option value="">Select decision</option>
                                        <option value="successful">Verify as Paid</option>
                                        <option value="failed">Mark as Failed</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-check-circle me-1"></i>
                                    Submit Payment Decision
                                </button>
                            </form>
                        @else
                            <div class="alert alert-success mb-0">
                                <i class="fa fa-check-circle me-1"></i>
                                This order has been marked as paid.
                            </div>
                        @endif

                    </div>
                </div>

                {{-- Order Reference --}}
                <div class="panel">
                    <div class="panel-body">
                        <div class="detail-block">
                            <div class="detail-label">Order Number</div>
                            <div class="detail-value">{{ $order->order_number }}</div>
                        </div>

                        <div class="detail-block">
                            <div class="detail-label">Order ID</div>
                            <div class="detail-value">#{{ $order->id }}</div>
                        </div>

                        <div class="detail-block">
                            <div class="detail-label">Last Updated</div>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($order->updated_at)->format('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
