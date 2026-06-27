@extends('layouts.admin')
@section('title', 'User Approvals')
@section('content')

<div class="admin-page-header">
    <div>
        <h1><i class="bi bi-person-check text-teal me-2"></i>User Approvals</h1>
        <p class="text-muted small mb-0">Review pending admin registrations and manage account statuses.</p>
    </div>
</div>

{{-- Pending Registrations --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold text-ink font-serif">
            <i class="bi bi-hourglass-split text-warning me-2"></i>Pending Registrations
            @if($pending->count())
                <span class="badge bg-warning text-dark ms-2">{{ $pending->count() }}</span>
            @endif
        </h5>
    </div>
    <div class="card-body p-0">
        @forelse($pending as $user)
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-4 py-3 border-bottom border-light">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-teal-soft text-teal rounded-circle d-flex align-items-center justify-content-center fw-bold"
                     style="width:42px;height:42px;font-size:1.1rem;flex-shrink:0;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="fw-semibold text-ink">{{ $user->name }}</div>
                    <div class="text-muted small">{{ $user->email }}</div>
                    @if($user->phone)
                        <div class="text-muted extra-small"><i class="bi bi-telephone me-1"></i>{{ $user->phone }}</div>
                    @endif
                    <div class="extra-small text-muted mt-1">Registered {{ $user->created_at->diffForHumans() }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Approve form --}}
                <form action="{{ route('admin.approvals.approve', $user) }}" method="POST" class="d-flex align-items-center gap-2" data-no-ajax>
                    @csrf @method('PATCH')
                    <select name="role_id" class="form-select form-select-sm" style="width:150px;" required>
                        <option value="">Assign Role…</option>
                        @foreach($roles as $role)
                            @if(!$role->isSuperAdmin())
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-teal px-3">
                        <i class="bi bi-check-circle me-1"></i>Approve
                    </button>
                </form>
                {{-- Reject --}}
                <form action="{{ route('admin.approvals.reject', $user) }}" method="POST"
                      data-no-ajax
                      onsubmit="return confirm('Reject and permanently delete this registration?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                        <i class="bi bi-x-circle me-1"></i>Reject
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-check2-all fs-1 text-teal opacity-50 d-block mb-2"></i>
            No pending registrations. All caught up!
        </div>
        @endforelse
    </div>
</div>

{{-- Active Admins --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="mb-0 fw-bold text-ink font-serif">
            <i class="bi bi-people text-teal me-2"></i>Admin Accounts
        </h5>
    </div>
    <div class="table-card">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Last Login</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach(\App\Models\User::with('role','approvedBy')->whereNotIn('status',['pending'])->whereHas('role', fn($q) => $q->whereNotIn('slug',['guest']))->latest()->get() as $admin)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-teal-soft text-teal rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:32px;height:32px;font-size:0.85rem;flex-shrink:0;">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <span class="fw-semibold text-ink small">{{ $admin->name }}</span>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $admin->email }}</td>
                    <td>
                        <span class="badge {{ $admin->isSuperAdmin() ? 'bg-teal text-white' : 'bg-teal-soft text-teal' }} px-2 py-1">
                            {{ $admin->role?->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $admin->status === 'active' ? 'bg-success' : 'bg-secondary' }} px-2 py-1">
                            {{ ucfirst($admin->status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $admin->approvedBy?->name ?? ($admin->isSuperAdmin() ? 'System' : '—') }}</td>
                    <td class="text-muted small">{{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="text-end">
                        @if(!$admin->isSuperAdmin() && $admin->id !== auth()->id())
                            @if($admin->status === 'active')
                                <form action="{{ route('admin.approvals.suspend', $admin) }}" method="POST" class="d-inline" data-no-ajax>
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-action-circle btn-delete"
                                            title="Suspend account"
                                            onclick="return confirm('Suspend {{ $admin->name }}?')">
                                        <i class="bi bi-pause-circle"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.approvals.reactivate', $admin) }}" method="POST" class="d-inline" data-no-ajax>
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-action-circle btn-edit" title="Reactivate account">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                </form>
                            @endif
                        @else
                            <span class="text-muted extra-small">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
