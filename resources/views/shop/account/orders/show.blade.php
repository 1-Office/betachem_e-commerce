@extends('layouts.shop')

@section('title', 'Order Details | Betachem')

@section('content')

<div class="container py-5">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <span class="text-primary fw-semibold">MY ACCOUNT</span>
            <h1 class="fw-bold mb-1">Order Details</h1>
            <p class="text-muted mb-0">
                Order {{ $order->order_number }}
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('shop.account.orders') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Orders
            </a>

            <a href="{{ route('shop.account') }}" class="btn btn-primary">
                My Account
            </a>
        </div>
    </div>

    @php
        $statusClass = match($order->status) {
            'completed' => 'success',
            'cancelled' => 'danger',
            'shipped' => 'info',
            'ready' => 'primary',
            'processing' => 'warning',
            default => 'secondary',
        };

        $paymentClass = match($order->payment_status) {
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'warning',
            'partial' => 'info',
            default => 'secondary',
        };

        $orderStatuses = [
            'pending' => [
                'label' => 'Order Received',
                'description' => 'Your order has been received.',
                'icon' => 'bi-receipt'
            ],
            'processing' => [
                'label' => 'Processing',
                'description' => 'Your order is being prepared.',
                'icon' => 'bi-gear'
            ],
            'ready' => [
                'label' => 'Ready',
                'description' => 'Your order is ready for collection or dispatch.',
                'icon' => 'bi-box-seam'
            ],
            'shipped' => [
                'label' => 'Shipped',
                'description' => 'Your order has been dispatched.',
                'icon' => 'bi-truck'
            ],
            'completed' => [
                'label' => 'Completed',
                'description' => 'Your order has been completed successfully.',
                'icon' => 'bi-check-circle'
            ],
        ];

        $currentStatus = strtolower($order->status);
        $statusOrder = array_keys($orderStatuses);
        $currentIndex = array_search($currentStatus, $statusOrder);

        $proofUploaded = $payment && !empty($payment->proof_of_payment);
        $paymentConfirmed = $payment && $payment->status === 'successful';
    @endphp

    {{-- Success and error messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Order Summary --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Order Number</div>
                    <h5 class="fw-bold mb-0">{{ $order->order_number }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Order Date</div>
                    <h5 class="fw-bold mb-0">
                        {{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, H:i') }}
                    </h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Order Status</div>
                    <span class="badge bg-{{ $statusClass }} fs-6">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- Horizontal Order Status Tracking --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-geo-alt text-primary me-2"></i>
                        Order Status Tracking
                    </h4>
                    <p class="text-muted small mb-0">
                        Follow the progress of your order.
                    </p>
                </div>

                <span class="badge bg-primary fs-6">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            @if($currentStatus === 'cancelled')

                <div class="alert alert-danger mb-0">
                    <h6 class="fw-bold">
                        <i class="bi bi-x-circle-fill me-2"></i>
                        Order Cancelled
                    </h6>
                    <p class="mb-0">
                        This order has been cancelled.
                        Please contact Betachem if you need assistance.
                    </p>
                </div>

            @elseif($currentIndex !== false)

                <div class="order-tracking-wrapper">
                    <div class="order-tracking">

                        @foreach($orderStatuses as $status => $step)

                            @php
                                $stepIndex = array_search($status, $statusOrder);
                                $isCompleted = $stepIndex <= $currentIndex;
                                $isCurrent = $stepIndex === $currentIndex;
                            @endphp

                            <div class="tracking-step">

                                <div class="tracking-icon
                                    {{ $isCompleted ? 'tracking-active' : 'tracking-inactive' }}
                                    {{ $isCurrent ? 'tracking-current' : '' }}">

                                    <i class="bi {{ $isCompleted ? 'bi-check-lg' : $step['icon'] }}"></i>
                                </div>

                                <div class="tracking-label
                                    {{ $isCompleted ? 'tracking-label-active' : '' }}
                                    {{ $isCurrent ? 'fw-bold' : '' }}">

                                    {{ $step['label'] }}
                                </div>

                                <small class="tracking-description">
                                    @if($isCurrent)
                                        Current Status
                                    @elseif($isCompleted)
                                        Completed
                                    @else
                                        Pending
                                    @endif
                                </small>

                            </div>

                            @if(!$loop->last)
                                <div class="tracking-connector
                                    {{ $stepIndex < $currentIndex ? 'connector-active' : '' }}">
                                </div>
                            @endif

                        @endforeach

                    </div>
                </div>

                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Your order progress changes when Betachem updates your order status.
                </div>

            @else

                <div class="alert alert-warning mb-0">
                    Current order status:
                    <strong>{{ ucfirst($order->status) }}</strong>
                </div>

            @endif

        </div>
    </div>

    {{-- Products Ordered --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 p-4">
            <h4 class="fw-bold mb-1">Products Ordered</h4>
            <p class="text-muted small mb-0">
                Products and quantities included in this order.
            </p>
        </div>

        <div class="card-body p-0">

            @if($items->isNotEmpty())

                <div class="table-responsive">
                    <table class="table align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Product</th>
                                <th>SKU</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th class="text-end px-4">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td class="px-4 fw-semibold">
                                        {{ $item->product_name }}
                                    </td>
                                    <td>{{ $item->sku ?: 'N/A' }}</td>
                                    <td>R {{ number_format((float) $item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end px-4 fw-semibold">
                                        R {{ number_format((float) $item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            @else

                <div class="text-center py-4">
                    <p class="text-muted mb-0">
                        No product items were found for this order.
                    </p>
                </div>

            @endif

        </div>
    </div>

    {{-- Delivery and Payment --}}
    <div class="row g-4">

        {{-- Delivery Information --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-truck me-2"></i>
                        Delivery Information
                    </h5>

                    <div class="fw-semibold">
                        {{ $order->shipping_first_name }}
                        {{ $order->shipping_last_name }}
                    </div>

                    <div class="text-muted mt-2">
                        {{ $order->shipping_phone }}
                    </div>

                    @if($order->shipping_email)
                        <div class="text-muted">
                            {{ $order->shipping_email }}
                        </div>
                    @endif

                    <hr>

                    <div>{{ $order->shipping_address }}</div>
                    <div>{{ $order->shipping_city }}</div>
                    <div>{{ $order->shipping_province }}</div>
                    <div>{{ $order->shipping_postal_code }}</div>

                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-credit-card me-2"></i>
                        Payment Information
                    </h5>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Payment Status</span>
                        <span class="badge bg-{{ $paymentClass }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Payment Method</span>
                        <strong>{{ $order->payment_method ?: 'Not specified' }}</strong>
                    </div>

                    @if($payment && $payment->transaction_reference)
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">EFT Reference</span>
                            <strong>{{ $payment->transaction_reference }}</strong>
                        </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>R {{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee</span>
                        <span>R {{ number_format((float) $order->delivery_fee, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount</span>
                        <span>- R {{ number_format((float) $order->discount, 2) }}</span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="fs-5">Order Total</strong>
                        <strong class="fs-4 text-primary">
                            R {{ number_format((float) $order->total, 2) }}
                        </strong>
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{-- EFT Proof of Payment --}}
    @if(strtolower((string) $order->payment_method) === 'eft')

        <div class="card border-0 shadow-sm mt-4">

            <div class="card-body p-4">

                <h4 class="fw-bold mb-2">
                    <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                    EFT Proof of Payment
                </h4>

                <p class="text-muted mb-4">
                    Please upload your bank payment confirmation as a PDF document.
                    Your payment will remain pending until Betachem verifies the funds.
                </p>

                @if($paymentConfirmed)

                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Your payment has been confirmed. Thank you!
                    </div>

                @elseif($proofUploaded)

                    <div class="alert alert-info">
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Proof of payment received.</strong>
                        Your document is awaiting verification by Betachem.
                        @if($payment->proof_uploaded_at)
                            <div class="small mt-1">
                                Submitted:
                                {{ \Carbon\Carbon::parse($payment->proof_uploaded_at)->format('d M Y, H:i') }}
                            </div>
                        @endif
                    </div>

                    <p class="small text-muted mb-3">
                        You may upload a replacement PDF if you need to correct your submission.
                    </p>

                    <form
                        action="{{ route('shop.account.orders.proof.upload', $order->id) }}"
                        method="POST"
                        enctype="multipart/form-data">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                EFT Transaction Reference
                            </label>

                            <input
                                type="text"
                                name="transaction_reference"
                                class="form-control"
                                maxlength="255"
                                value="{{ old('transaction_reference', $payment->transaction_reference ?? '') }}"
                                placeholder="Enter your bank payment reference">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Replace Proof of Payment (PDF)
                            </label>

                            <input
                                type="file"
                                name="proof_of_payment"
                                class="form-control"
                                accept=".pdf,application/pdf"
                                required>

                            <small class="text-muted">
                                PDF files only. Maximum file size: 5 MB.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-2"></i>
                            Upload Replacement PDF
                        </button>

                    </form>

                @else

                    <form
                        action="{{ route('shop.account.orders.proof.upload', $order->id) }}"
                        method="POST"
                        enctype="multipart/form-data">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                EFT Transaction Reference
                            </label>

                            <input
                                type="text"
                                name="transaction_reference"
                                class="form-control"
                                maxlength="255"
                                value="{{ old('transaction_reference') }}"
                                placeholder="Enter your bank payment reference">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Select Proof of Payment (PDF)
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="file"
                                name="proof_of_payment"
                                class="form-control"
                                accept=".pdf,application/pdf"
                                required>

                            <small class="text-muted">
                                PDF files only. Maximum file size: 5 MB.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-upload me-2"></i>
                            Submit Proof of Payment
                        </button>

                    </form>

                @endif

            </div>
        </div>

    @endif

    {{-- Order Notes --}}
    @if($order->notes)

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-4">

                <h5 class="fw-bold mb-2">
                    <i class="bi bi-chat-left-text me-2"></i>
                    Order Notes
                </h5>

                <p class="text-muted mb-0">
                    {{ $order->notes }}
                </p>

            </div>
        </div>

    @endif

</div>

{{-- Order Tracking Styles --}}
<style>
    .order-tracking-wrapper {
        width: 100%;
        overflow-x: auto;
        padding: 20px 5px;
    }

    .order-tracking {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        min-width: 650px;
    }

    .tracking-step {
        flex: 0 0 110px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .tracking-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 21px;
        margin-bottom: 12px;
        transition: all .3s ease;
    }

    .tracking-active {
        background: #0d6efd;
        color: #ffffff;
        border: 2px solid #0d6efd;
    }

    .tracking-inactive {
        background: #ffffff;
        color: #8a94a6;
        border: 2px solid #dce3ed;
    }

    .tracking-current {
        box-shadow: 0 0 0 6px rgba(13, 110, 253, .13);
    }

    .tracking-label {
        font-size: 13px;
        line-height: 1.4;
        min-height: 36px;
        color: #7b8794;
    }

    .tracking-label-active {
        color: #0d6efd;
    }

    .tracking-description {
        font-size: 11px;
        color: #7b8794;
    }

    .tracking-connector {
        flex: 1;
        height: 4px;
        background: #dce3ed;
        margin-top: 22px;
        min-width: 25px;
        border-radius: 10px;
        transition: background .3s ease;
    }

    .connector-active {
        background: #0d6efd;
    }

    @media (max-width: 576px) {
        .order-tracking-wrapper {
            padding-left: 0;
            padding-right: 0;
        }

        .tracking-step {
            flex-basis: 110px;
        }
    }
</style>

@endsection
