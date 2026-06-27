@extends('layouts.admin')
@section('title', 'Message Details')
@section('content')

<div class="admin-page-header">
    <div>
        <h1>Message Details</h1>
        <p class="text-muted small mb-0">Opened and marked as read automatically.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i>Inbox
        </a>
        <button type="button" class="btn btn-outline-danger px-3 py-2"
                data-bs-toggle="modal"
                data-bs-target="#deleteConfirmModal"
                data-action="{{ route('admin.messages.destroy',$message) }}">
            <i class="bi bi-trash3-fill me-1"></i>Delete
        </button>
    </div>
</div>

<div class="row g-3 g-md-4">
    {{-- Main message --}}
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
            <div class="card-body p-3 p-md-4">

                {{-- Subject & status --}}
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-4">
                    <div>
                        <h4 class="fw-bold text-ink mb-1" style="font-size:1.1rem;">{{ $message->subject ?: 'No subject set' }}</h4>
                        <small class="text-muted">Sent {{ $message->created_at->format('d M Y, H:i A') }}</small>
                    </div>
                    <span class="badge {{ $message->status === 'new' ? 'bg-danger' : 'bg-success' }} text-white text-uppercase px-3 py-2 flex-shrink-0" style="font-size:0.65rem;">
                        {{ $message->status }}
                    </span>
                </div>

                {{-- Sender info --}}
                <div class="bg-light border rounded-3 p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <p class="mb-1 text-muted extra-small text-uppercase fw-bold">From</p>
                            <h6 class="fw-bold mb-0">{{ $message->name }}</h6>
                            <p class="mb-0 text-muted small">{{ $message->email }}</p>
                        </div>
                        <div class="col-12 col-sm-6">
                            <p class="mb-1 text-muted extra-small text-uppercase fw-bold">Phone</p>
                            <h6 class="fw-bold mb-0">{{ $message->phone ?: 'Not provided' }}</h6>
                            <p class="mb-0 text-muted small">Message #{{ $message->id }}</p>
                        </div>
                    </div>
                </div>

                {{-- Message body --}}
                <div class="rounded-3 border border-secondary border-opacity-10 overflow-hidden">
                    <div class="bg-teal text-white px-3 py-2">
                        <h6 class="mb-0 small fw-bold"><i class="bi bi-chat-text me-2"></i>Message Content</h6>
                    </div>
                    <div class="p-3 p-md-4 bg-white">
                        <p class="text-ink mb-0" style="white-space:pre-line;line-height:1.7;">{{ $message->message }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions panel --}}
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-teal text-white py-3 px-4">
                <h6 class="mb-0 fw-bold"><i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                    <div class="icon-avatar-small bg-teal-soft text-teal"><i class="bi bi-envelope-fill"></i></div>
                    <div>
                        <p class="text-muted small mb-0">Status</p>
                        <strong class="small">{{ $message->status === 'new' ? 'Unread' : 'Read' }}</strong>
                    </div>
                </div>
                <dl class="mb-0" style="display:grid;grid-template-columns:auto 1fr;gap:0.4rem 1rem;font-size:0.85rem;">
                    <dt class="text-muted fw-normal">Sender</dt>
                    <dd class="mb-0 fw-semibold text-ink">{{ $message->name }}</dd>
                    <dt class="text-muted fw-normal">Email</dt>
                    <dd class="mb-0 text-ink">{{ $message->email }}</dd>
                    <dt class="text-muted fw-normal">Subject</dt>
                    <dd class="mb-0 fw-semibold text-ink">{{ $message->subject ?: '—' }}</dd>
                    <dt class="text-muted fw-normal">Received</dt>
                    <dd class="mb-0 text-ink">{{ $message->created_at->diffForHumans() }}</dd>
                </dl>
                <div class="mt-4 d-grid gap-2">
                    <a href="mailto:{{ $message->email }}" class="btn btn-outline-teal py-2 fw-semibold">
                        <i class="bi bi-reply-fill me-2"></i>Reply via Email
                    </a>
                    <button class="btn btn-outline-danger py-2 fw-semibold"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteConfirmModal"
                            data-action="{{ route('admin.messages.destroy',$message) }}">
                        <i class="bi bi-trash3-fill me-2"></i>Delete Message
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
