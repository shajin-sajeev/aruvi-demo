@extends('layouts.admin')
@section('title', 'My Profile')

@section('content')

<div class="admin-page-header">
    <div>
        <h1><i class="bi bi-person-circle text-teal me-2"></i>My Profile</h1>
        <p class="text-muted small mb-0">Update your account information and change your password.</p>
    </div>
</div>

<div class="row g-4">

    {{-- ── Left: Avatar + info card ──────────────────────── --}}
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4">
            {{-- Avatar initial --}}
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-teal text-white fw-bold"
                 style="width:80px;height:80px;font-size:2rem;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h5 class="fw-bold text-ink mb-0 font-serif">{{ $user->name }}</h5>
            <p class="text-muted small mb-2">{{ $user->email }}</p>
            <span class="badge bg-teal text-white px-3 py-2 mb-3">
                <i class="bi bi-shield-fill me-1"></i>{{ $user->role?->name ?? 'No Role' }}
            </span>

            <hr class="opacity-25 my-3">

            <div class="text-start">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-telephone text-teal"></i>
                    <span class="text-muted small">{{ $user->phone ?: 'No phone set' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-circle-fill {{ $user->status === 'active' ? 'text-success' : 'text-warning' }}" style="font-size:0.5rem;"></i>
                    <span class="text-muted small">{{ ucfirst($user->status) }}</span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-clock text-teal"></i>
                    <span class="text-muted small">
                        Last login: {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                    </span>
                </div>
                @if($user->approved_at)
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle text-teal"></i>
                    <span class="text-muted small">
                        Approved {{ $user->approved_at->diffForHumans() }}
                    </span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Right: Edit forms ──────────────────────────────── --}}
    <div class="col-12 col-lg-8 d-flex flex-column gap-4">

        {{-- Profile information --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold text-ink font-serif">
                    <i class="bi bi-person-badge text-teal me-2"></i>Profile Information
                </h5>
            </div>
            <form method="POST" action="{{ route('admin.profile.update') }}" data-no-ajax>
                @csrf @method('PATCH')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-ink">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Phone Number <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}" placeholder="+91 90000 00000">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top px-4 py-3">
                    <button type="submit" class="btn btn-teal px-4">
                        <i class="bi bi-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Change password --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <h5 class="mb-0 fw-bold text-ink font-serif">
                    <i class="bi bi-lock text-teal me-2"></i>Change Password
                </h5>
            </div>
            <form method="POST" action="{{ route('admin.profile.password') }}" data-no-ajax>
                @csrf @method('PATCH')
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-ink">Current Password</label>
                            <div class="position-relative">
                                <input type="password" name="current_password" id="currentPw"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       placeholder="Enter current password" required>
                                <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-1 p-1 border-0 text-muted"
                                        onclick="togglePw('currentPw', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">New Password</label>
                            <div class="position-relative">
                                <input type="password" name="password" id="newPw"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 characters" required>
                                <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-1 p-1 border-0 text-muted"
                                        onclick="togglePw('newPw', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            {{-- Strength bar --}}
                            <div style="height:3px;border-radius:2px;margin-top:0.4rem;background:#e0eced;overflow:hidden;">
                                <div id="pwStrengthBar" style="height:100%;border-radius:2px;width:0%;transition:width .3s,background .3s;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-ink">Confirm New Password</label>
                            <div class="position-relative">
                                <input type="password" name="password_confirmation" id="confirmPw"
                                       class="form-control"
                                       placeholder="Repeat new password" required>
                                <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y me-1 p-1 border-0 text-muted"
                                        onclick="togglePw('confirmPw', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-3 bg-light rounded-3 small text-muted">
                        <i class="bi bi-info-circle text-teal me-1"></i>
                        Password must be at least 8 characters and contain both letters and numbers.
                    </div>
                </div>
                <div class="card-footer bg-white border-top px-4 py-3">
                    <button type="submit" class="btn btn-teal px-4">
                        <i class="bi bi-shield-lock me-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function togglePw(id, btn) {
    var inp = document.getElementById(id);
    var ic  = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        ic.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        ic.className = 'bi bi-eye';
    }
}

// Password strength
var newPwInput = document.getElementById('newPw');
if (newPwInput) {
    newPwInput.addEventListener('input', function () {
        var v = this.value, s = 0;
        if (v.length >= 8)  s++;
        if (v.length >= 12) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        var widths = ['0%','20%','40%','65%','85%','100%'];
        var colors = ['transparent','#ef4444','#f97316','#eab308','#22c55e','#008C95'];
        var bar = document.getElementById('pwStrengthBar');
        if (bar) { bar.style.width = widths[s]; bar.style.background = colors[s]; }
    });
}
</script>
@endpush
