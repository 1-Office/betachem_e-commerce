@extends('layouts.admin')

@section('title', 'View Contact Message')

@section('content')

<style>
    .message-detail-page {
        color: #263248;
    }

    .detail-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }

    .detail-heading h2 {
        font-weight: 700;
        margin-bottom: 5px;
    }

    .detail-muted {
        color: #778198;
        font-size: 13px;
    }

    .detail-card {
        background: #fff;
        border: 1px solid #e8ebf2;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(30, 50, 90, .04);
        margin-bottom: 22px;
        overflow: hidden;
    }

    .detail-card-header {
        padding: 17px 20px;
        border-bottom: 1px solid #edf0f5;
        font-weight: 700;
    }

    .detail-card-body {
        padding: 22px;
    }

    .sender-avatar {
        width: 55px;
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #eeedff;
        color: #2D208A;
        font-size: 22px;
    }

    .sender-title {
        font-size: 19px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .contact-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 17px;
        font-size: 13px;
    }

    .contact-item i {
        width: 20px;
        color: #2D208A;
        margin-top: 3px;
    }

    .contact-item a {
        color: #1745CA;
        text-decoration: none;
        overflow-wrap: anywhere;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 700;
        color: #8992a5;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 14px;
        font-weight: 600;
        color: #303c55;
    }

    .message-content {
        background: #fafbfe;
        border: 1px solid #edf0f5;
        border-radius: 9px;
        padding: 20px;
        min-height: 150px;
        font-size: 14px;
        line-height: 1.8;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .detail-form .form-label {
        font-size: 13px;
        font-weight: 700;
    }

    .detail-form .form-control,
    .detail-form .form-select {
        border-color: #dfe4ed;
        border-radius: 8px;
        min-height: 43px;
    }

    .detail-form textarea {
        min-height: 150px;
    }

    .detail-status {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
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

    .btn-betachem {
        background: #2D208A;
        color: #fff;
        border: 0;
        border-radius: 8px;
        padding: 11px 18px;
        font-weight: 600;
    }

    .btn-betachem:hover {
        background: #1745CA;
        color: #fff;
    }

    .back-btn {
        color: #2D208A;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
    }

    @media(max-width: 767px) {
        .detail-card-body {
            padding: 17px;
        }
    }
</style>

<div class="message-detail-page">

    <div class="detail-heading">
        <div>
            <h2>Contact Message</h2>
            <div class="detail-muted">
                Review the enquiry and manage its status.
            </div>
        </div>

        <a href="{{ route('admin.messages.index') }}" class="back-btn">
            <i class="fa fa-arrow-left me-1"></i>
            Back to Messages
        </a>
    </div>

    {{-- Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- Main message --}}
        <div class="col-lg-8">

            <div class="detail-card">

                <div class="detail-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        <i class="fa fa-envelope-open me-2"></i>
                        Message Details
                    </span>

                    <span class="detail-status status-{{ $message->status }}">
                        {{ ucfirst($message->status) }}
                    </span>
                </div>

                <div class="detail-card-body">

                    <div class="d-flex align-items-center gap-3 mb-4">

                        <div class="sender-avatar">
                            <i class="fa fa-user"></i>
                        </div>

                        <div>
                            <div class="sender-title">
                                {{ $message->first_name }} {{ $message->last_name }}
                            </div>

                            <div class="detail-muted">
                                Received
                                {{ \Carbon\Carbon::parse($message->created_at)->format('d F Y \a\t H:i') }}
                            </div>
                        </div>

                    </div>

                    <hr class="mb-4">

                    <div class="mb-4">
                        <div class="detail-label">Subject</div>
                        <div class="detail-value">
                            {{ $message->subject }}
                        </div>
                    </div>

                    <div>
                        <div class="detail-label">Message</div>

                        <div class="message-content">{{ $message->message }}</div>
                    </div>

                </div>

            </div>

            {{-- Contact information --}}
            <div class="detail-card">

                <div class="detail-card-header">
                    <i class="fa fa-address-card me-2"></i>
                    Sender Contact Information
                </div>

                <div class="detail-card-body">

                    <div class="contact-item">
                        <i class="fa fa-envelope"></i>
                        <div>
                            <div class="detail-label">Email Address</div>
                            <a href="mailto:{{ $message->email }}">
                                {{ $message->email }}
                            </a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fa fa-phone"></i>
                        <div>
                            <div class="detail-label">Phone Number</div>

                            @if($message->phone)
                                <a href="tel:{{ $message->phone }}">
                                    {{ $message->phone }}
                                </a>
                            @else
                                <span class="detail-value">Not provided</span>
                            @endif
                        </div>
                    </div>

                    <div class="contact-item mb-0">
                        <i class="fa fa-user"></i>
                        <div>
                            <div class="detail-label">Linked Customer Account</div>

                            @if($message->user_id)
                                <span class="detail-value">
                                    Customer ID #{{ $message->user_id }}
                                </span>
                            @else
                                <span class="detail-muted">
                                    No customer account linked
                                </span>
                            @endif
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- Management panel --}}
        <div class="col-lg-4">

            <div class="detail-card">

                <div class="detail-card-header">
                    <i class="fa fa-cog me-2"></i>
                    Manage Message
                </div>

                <div class="detail-card-body">

                    <form method="POST"
                          action="{{ route('admin.messages.update', $message->id) }}"
                          class="detail-form">

                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="status" class="form-label">
                                Message Status
                            </label>

                            <select name="status" id="status" class="form-select" required>
                                <option value="new" @selected(old('status', $message->status) === 'new')>
                                    New
                                </option>

                                <option value="read" @selected(old('status', $message->status) === 'read')>
                                    Read
                                </option>

                                <option value="replied" @selected(old('status', $message->status) === 'replied')>
                                    Replied
                                </option>

                                <option value="closed" @selected(old('status', $message->status) === 'closed')>
                                    Closed
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="admin_notes" class="form-label">
                                Internal Admin Notes
                            </label>

                            <textarea
                                name="admin_notes"
                                id="admin_notes"
                                class="form-control"
                                rows="7"
                                placeholder="Add internal notes about this enquiry..."
                            >{{ old('admin_notes', $message->admin_notes) }}</textarea>

                            <small class="text-muted">
                                These notes are internal and are not sent to the customer.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-betachem w-100">
                            <i class="fa fa-save me-2"></i>
                            Save Changes
                        </button>

                    </form>

                </div>

            </div>

            <div class="detail-card">
                <div class="detail-card-body">

                    <div class="detail-label">Message Reference</div>

                    <div class="detail-value mb-3">
                        #{{ $message->id }}
                    </div>

                    <div class="detail-label">Last Updated</div>

                    <div class="detail-value">
                        {{ \Carbon\Carbon::parse($message->updated_at)->format('d M Y, H:i') }}
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection
