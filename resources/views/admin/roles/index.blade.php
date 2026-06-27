@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@push('styles')
<style>/* ── Role list ──────────────────────────────────────────── */
.role-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.18s ease;
    background: #fff;
    margin-bottom: 0.4rem;
    text-decoration: none;
}
.role-list-item:hover {
    background: var(--teal-soft, rgba(0,140,149,0.06));
    border-color: rgba(0,140,149,0.2);
}
.role-list-item.active {
    background: rgba(0,140,149,0.08);
    border-color: var(--teal, #008C95);
}
.role-badge {
    width: 36px; height: 36px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

/* ── Permission matrix ──────────────────────────────────── */
.perm-section-header {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #6b8a8c;
    padding: 0.6rem 0 0.35rem;
    border-bottom: 1px solid rgba(0,140,149,0.08);
    margin-bottom: 0.5rem;
}
.perm-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.4rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.03);
}
.perm-row:last-child { border-bottom: none; }

/* Action toggle buttons */
.perm-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.65rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1.5px solid #dde8e9;
    background: #fff;
    color: #9ab5b7;
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.perm-action-btn.active-view   { border-color:#008C95; background:rgba(0,140,149,0.1); color:#008C95; }
.perm-action-btn.active-create { border-color:#22c55e; background:rgba(34,197,94,0.08); color:#16a34a; }
.perm-action-btn.active-edit   { border-color:#f59e0b; background:rgba(245,158,11,0.08); color:#b45309; }
.perm-action-btn.active-delete { border-color:#ef4444; background:rgba(239,68,68,0.08); color:#dc2626; }

/* Select all row per section */
.section-select-all {
    font-size: 0.72rem;
    color: var(--teal, #008C95);
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    padding: 0 0.25rem;
}
.section-select-all:hover { text-decoration: underline; }
</style>
@endpush

@section('content')

<div class="admin-page-header">
    <div>
        <h1><i class="bi bi-shield-lock text-teal me-2"></i>Roles &amp; Permissions</h1>
        <p class="text-muted small mb-0">Create roles and control what each role can access.</p>
    </div>
    <button class="btn btn-teal btn-sm px-3" data-bs-toggle="modal" data-bs-target="#newRoleModal">
        <i class="bi bi-plus-lg me-1"></i> New Role
    </button>
</div>

<div class="row g-4 align-items-start">

    {{-- ── Left: Roles list ─────────────────────────────── --}}
    <div class="col-12 col-lg-3">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-3">
                <span class="fw-bold text-ink small"><i class="bi bi-people me-1 text-teal"></i>All Roles</span>
            </div>
            <div class="card-body p-3">
                @foreach($roles as $role)
                    @php
                        $icons = ['super-admin'=>'bi-star-fill','admin'=>'bi-shield-fill','manager'=>'bi-person-gear','guest'=>'bi-person'];
                        $iconClass = $icons[$role->slug] ?? 'bi-person-badge';
                        $colors = ['super-admin'=>'bg-teal text-white','admin'=>'bg-teal-soft text-teal','manager'=>'bg-warning bg-opacity-10 text-warning','guest'=>'bg-light text-secondary'];
                        $bgClass = $colors[$role->slug] ?? 'bg-teal-soft text-teal';
                        $isActive = request()->get('role') == $role->id || ($loop->first && !request()->get('role'));
                    @endphp
                    <a href="?role={{ $role->id }}"
                       class="role-list-item {{ $isActive ? 'active' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <span class="role-badge {{ $bgClass }}">
                                <i class="bi {{ $iconClass }}"></i>
                            </span>
                            <div>
                                <div class="fw-semibold text-ink" style="font-size:0.88rem;">{{ $role->name }}</div>
                                <div class="text-muted" style="font-size:0.72rem;">
                                    @if($role->isSuperAdmin())
                                        All permissions
                                    @else
                                        {{ $role->permissions->count() }} permissions
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($role->is_system)
                            <span class="badge bg-light text-muted border" style="font-size:0.62rem;">System</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Right: Permission editor for selected role ────── --}}
    @php
        $selectedRoleId = request()->get('role');
        $activeRole = $selectedRoleId
            ? $roles->firstWhere('id', $selectedRoleId)
            : $roles->first();
    @endphp

    <div class="col-12 col-lg-9">
        @if($activeRole)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="mb-0 fw-bold text-ink font-serif">
                        <i class="bi bi-pencil-square text-teal me-2"></i>{{ $activeRole->name }}
                    </h5>
                    @if($activeRole->is_system)
                        <span class="badge bg-teal-soft text-teal extra-small px-2 py-1">System Role</span>
                    @endif
                </div>
                @if(!$activeRole->isSuperAdmin() && !$activeRole->is_system)
                    <button type="button"
                            class="btn btn-sm btn-outline-danger trigger-delete-role"
                            data-role-name="{{ $activeRole->name }}"
                            data-form-action="{{ route('admin.role-permissions.destroy', $activeRole) }}">
                        <i class="bi bi-trash me-1"></i>Delete Role
                    </button>
                    {{-- hidden form submitted by modal --}}
                    <form id="deleteRoleForm" method="POST" action="" data-no-ajax class="d-none">
                        @csrf @method('DELETE')
                    </form>
                @endif
            </div>

            @if($activeRole->isSuperAdmin())
                <div class="card-body px-4 py-4 text-center">
                    <i class="bi bi-infinity display-4 text-teal opacity-50 d-block mb-3"></i>
                    <h6 class="fw-bold text-ink mb-1">Super Admin — Unrestricted Access</h6>
                    <p class="text-muted small mb-0">Super Admin automatically has every permission in the system. No configuration required.</p>
                </div>
            @else
            <form action="{{ route('admin.role-permissions.update', $activeRole) }}" method="POST" data-no-ajax id="permForm">
                @csrf @method('PATCH')
                <div class="card-body px-4 py-3" style="max-height:65vh;overflow-y:auto;">

                    {{-- Global select all / clear all --}}
                    <div class="d-flex align-items-center justify-content-between mb-4 p-3 bg-light rounded-3">
                        <span class="text-muted small fw-semibold">Quick select:</span>
                        <div class="d-flex gap-3">
                            <a href="#" class="section-select-all" onclick="toggleAll(true);return false;">
                                <i class="bi bi-check-all me-1"></i>Select All
                            </a>
                            <span class="text-muted">|</span>
                            <a href="#" class="section-select-all text-muted" onclick="toggleAll(false);return false;">
                                <i class="bi bi-x-lg me-1"></i>Clear All
                            </a>
                        </div>
                    </div>

                    @foreach($permissions as $resource => $group)
                        @php
                            $label = ucwords(str_replace('-', ' ', $resource));
                            $sectionId = 'sec-' . str_replace([' ','-'], '_', $resource);
                        @endphp
                        <div class="mb-4" data-section="{{ $sectionId }}">
                            <div class="perm-section-header d-flex align-items-center justify-content-between">
                                <span>{{ $label }}</span>
                                <div class="d-flex gap-2">
                                    <a href="#" class="section-select-all" onclick="toggleSection('{{ $sectionId }}',true);return false;">All</a>
                                    <span class="text-muted" style="font-size:0.7rem;">|</span>
                                    <a href="#" class="section-select-all text-muted" onclick="toggleSection('{{ $sectionId }}',false);return false;">None</a>
                                </div>
                            </div>

                            @foreach($group as $permission)
                                @php
                                    $action  = explode('.', $permission->slug)[1] ?? '';
                                    $checked = $activeRole->permissions->contains('id', $permission->id);
                                    $activeClass = 'active-' . $action;
                                    $actionIcon = match($action) {
                                        'view'   => 'bi-eye',
                                        'create' => 'bi-plus-circle',
                                        'edit'   => 'bi-pencil',
                                        'delete' => 'bi-trash',
                                        default  => 'bi-key',
                                    };
                                @endphp
                                <div class="perm-row" data-section-item="{{ $sectionId }}">
                                    <span class="text-ink small" style="font-size:0.83rem;">{{ $permission->name }}</span>
                                    <label class="perm-action-btn {{ $checked ? $activeClass : '' }}"
                                           data-action="{{ $action }}"
                                           data-perm-id="{{ $permission->id }}">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->id }}"
                                               class="d-none"
                                               {{ $checked ? 'checked' : '' }}>
                                        <i class="bi {{ $actionIcon }}"></i>
                                        {{ ucfirst($action) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <div class="card-footer bg-white border-top px-4 py-3 d-flex align-items-center justify-content-between">
                    <span class="text-muted small" id="permCount">
                        {{ $activeRole->permissions->count() }} permission(s) selected
                    </span>
                    <button type="submit" class="btn btn-teal px-4">
                        <i class="bi bi-save me-2"></i>Save Permissions
                    </button>
                </div>
            </form>
            @endif
        </div>
        @endif
    </div>
</div>

{{-- New Role Modal --}}
<div class="modal fade" id="newRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold font-serif text-ink">
                    <i class="bi bi-plus-circle text-teal me-2"></i>Create New Role
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.role-permissions.store') }}" method="POST" data-no-ajax>
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-ink">Role Name</label>
                        <input type="text" name="name" id="newRoleName" class="form-control"
                               placeholder="e.g. Content Editor" required autocomplete="off">
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold small text-ink">Role Slug</label>
                        <input type="text" name="slug" id="newRoleSlug" class="form-control"
                               placeholder="e.g. content-editor" required
                               pattern="[a-z0-9\-]+" title="Lowercase letters, numbers and hyphens only"
                               autocomplete="off">
                        <small class="text-muted extra-small mt-1 d-block">
                            <i class="bi bi-info-circle me-1"></i>Auto-generated from name. Lowercase, hyphens only.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-teal px-4"><i class="bi bi-check-lg me-1"></i>Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Role Confirmation Modal --}}
<div class="modal fade" id="deleteRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10"
                     style="width:60px;height:60px;">
                    <i class="bi bi-trash3-fill text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold text-ink mb-1">Delete Role?</h5>
                <p class="text-muted small mb-4">
                    You are about to permanently delete the <strong id="deleteRoleName"></strong> role.
                    Users with this role will lose their access. This cannot be undone.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light px-4 fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4 fw-semibold"
                            onclick="document.getElementById('deleteRoleForm').submit();">
                        <i class="bi bi-trash3-fill me-1"></i>Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    /* ── Toggle single permission button ─────────────────── */
    document.querySelectorAll('.perm-action-btn').forEach(function (label) {
        label.addEventListener('click', function () {
            const checkbox = this.querySelector('input[type=checkbox]');
            const action   = this.dataset.action;
            checkbox.checked = !checkbox.checked;
            const activeClass = 'active-' + action;
            this.classList.toggle(activeClass, checkbox.checked);
            updateCount();
        });
    });

    /* ── Toggle all permissions ──────────────────────────── */
    window.toggleAll = function (state) {
        document.querySelectorAll('#permForm input[type=checkbox]').forEach(function (cb) {
            cb.checked = state;
            const label = cb.closest('.perm-action-btn');
            if (label) {
                const action = label.dataset.action;
                label.classList.toggle('active-' + action, state);
            }
        });
        updateCount();
    };

    /* ── Toggle a single section ─────────────────────────── */
    window.toggleSection = function (sectionId, state) {
        document.querySelectorAll('[data-section-item="' + sectionId + '"] input[type=checkbox]').forEach(function (cb) {
            cb.checked = state;
            const label = cb.closest('.perm-action-btn');
            if (label) {
                label.classList.toggle('active-' + label.dataset.action, state);
            }
        });
        updateCount();
    };

    /* ── Live permission count ───────────────────────────── */
    function updateCount() {
        const total   = document.querySelectorAll('#permForm input[type=checkbox]:checked').length;
        const counter = document.getElementById('permCount');
        if (counter) counter.textContent = total + ' permission(s) selected';
    }

    /* ── Auto-generate slug from role name ───────────────── */
    const nameInput = document.getElementById('newRoleName');
    const slugInput = document.getElementById('newRoleSlug');
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function () {
            slugInput.value = this.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        });
    }

    /* ── Delete role confirmation modal ──────────────────── */
    var deleteModal      = document.getElementById('deleteRoleModal');
    var deleteRoleName   = document.getElementById('deleteRoleName');
    var deleteRoleForm   = document.getElementById('deleteRoleForm');

    document.querySelectorAll('.trigger-delete-role').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var name   = this.dataset.roleName;
            var action = this.dataset.formAction;
            if (deleteRoleName) deleteRoleName.textContent = name;
            if (deleteRoleForm) deleteRoleForm.setAttribute('action', action);
            var modal = bootstrap.Modal.getOrCreateInstance(deleteModal);
            modal.show();
        });
    });
})();
</script>
@endpush
