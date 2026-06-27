@extends('layouts.admin')
@section('title', 'Message Inbox')
@section('content')

<div class="admin-page-header">
    <div>
        <h1>Message Inbox</h1>
        <p class="text-muted small mb-0">Read and manage customer contact requests from the website.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <span class="badge bg-danger text-white px-3 py-2">
            <i class="bi bi-exclamation-circle me-1"></i>Unread: {{ $unreadCount }}
        </span>
        <span class="badge bg-secondary text-white px-3 py-2">
            Total: {{ $messages->total() }}
        </span>
    </div>
</div>

<div class="row g-3 g-md-4 messages-layout">
    {{-- Message List --}}
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-ink">Recent Messages</h6>
                    <small class="text-muted">Tap any message to read it.</small>
                </div>
                <span class="badge bg-teal-soft text-teal">{{ $messages->count() }} shown</span>
            </div>
            <div class="list-group list-group-flush">
                @forelse($messages as $item)
                    @php $isNew = $item->status === 'new'; @endphp
                    <a href="{{ route('admin.messages.show',$item) }}"
                       class="list-group-item list-group-item-action d-flex align-items-start gap-3 p-3 {{ $isNew ? 'bg-light' : '' }}">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold"
                             style="width:40px;height:40px;font-size:0.9rem;background:{{ $isNew ? '#ffe8e6' : '#e7f7f2' }};color:{{ $isNew ? '#c41c1c' : '#0f7d59' }};">
                            {{ strtoupper(substr($item->name,0,1)) }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                <div class="min-w-0">
                                    <strong class="d-block text-ink text-truncate">{{ $item->name }}</strong>
                                    <small class="text-muted text-truncate d-block">{{ $item->email }}</small>
                                </div>
                                <span class="badge {{ $isNew ? 'bg-danger' : 'bg-secondary' }} text-white text-uppercase flex-shrink-0" style="font-size:0.62rem;">{{ $item->status }}</span>
                            </div>
                            <div class="small text-ink fw-semibold text-truncate">{{ $item->subject ?: 'No subject' }}</div>
                            <p class="text-muted small mb-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $item->message }}</p>
                        </div>
                    </a>
                @empty
                    <div class="p-4 text-center text-muted small">
                        <i class="bi bi-envelope-open fs-2 d-block mb-2"></i>
                        No messages yet.
                    </div>
                @endforelse
            </div>
            <div class="card-footer bg-white border-top py-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>

    {{-- Placeholder --}}
    <div class="col-12 col-xl-8 d-none d-xl-block">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 d-flex flex-column justify-content-center align-items-center text-center">
            <div class="mb-4">
                <div style="width:72px;height:72px;background:var(--teal-soft);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:1rem;">
                    <i class="bi bi-envelope-open text-teal" style="font-size:1.8rem;"></i>
                </div>
                <h5 class="fw-bold text-ink mb-2">Select a message to read</h5>
                <p class="text-muted small mb-0">Click any message from the inbox to view its full content, then mark as read or delete.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">Back to Dashboard</a>
                <a href="{{ route('admin.messages.index') }}" class="btn btn-teal px-4 py-2">Refresh Inbox</a>
            </div>
        </div>
    </div>
</div>
@endsection
