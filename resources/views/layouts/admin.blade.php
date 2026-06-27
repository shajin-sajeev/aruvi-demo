<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - Aruvi on the Cliff</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/resort.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-admin-light">
<div class="admin-shell">
    <aside class="admin-sidebar shadow-sm">
        <div class="sidebar-header border-bottom pb-3 mb-3 d-flex align-items-center gap-2" style="border-color: rgba(0,140,149,0.12) !important;">
            <img src="{{ asset($settings['admin_logo'] ?? 'images/default/logo.ico') }}" alt="Logo" style="height: 34px; width: auto; object-fit: contain;">
            <div>
                <h6 class="text-ink font-serif mb-0 fw-bold" style="font-size:0.92rem;">Aruvi Administration</h6>
                <small class="text-teal extra-small">Resort Control Panel</small>
            </div>
        </div>
        <div class="sidebar-navigation">
            <small class="text-uppercase text-teal tracking-wider extra-small fw-bold opacity-75 d-block mb-2">core</small>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow me-2"></i> Dashboard Analytics
            </a>
            @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.theme.customization') }}" class="{{ request()->routeIs('admin.theme.customization') ? 'active' : '' }}">
                <i class="bi bi-palette-fill me-2"></i> Theme Customization
            </a>
            @endif

            @php
                $pendingCount = auth()->user()->isSuperAdmin()
                    ? \App\Models\User::where('status','pending')->count()
                    : 0;
            @endphp

            @if(auth()->user()->isSuperAdmin())
            <small class="text-uppercase text-teal tracking-wider extra-small fw-bold opacity-75 d-block mt-4 mb-2">Super Admin</small>
            <a href="{{ route('admin.approvals.index') }}" class="d-flex align-items-center justify-content-between {{ request()->routeIs('admin.approvals.*') ? 'active' : '' }}">
                <span><i class="bi bi-person-check me-2"></i> User Approvals</span>
                @if($pendingCount > 0)
                    <span class="badge bg-danger text-white">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.role-permissions.index') }}" class="{{ request()->routeIs('admin.role-permissions.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock me-2"></i> Roles &amp; Permissions
            </a>
            @endif
            
            @php
                $sidebarGroups = [
                    'Website & Branding' => [
                        'hero-slides'   => ['icon' => 'bi-images',     'title' => 'Hero Slider'],
                        'home-sections' => ['icon' => 'bi-house-gear', 'title' => 'Homepage Layout'],
                        'social-links'  => ['icon' => 'bi-share',      'title' => 'Social Links'],
                    ],
                    'Accommodations' => [
                        'room-types' => ['icon' => 'bi-layout-text-window', 'title' => 'Room Types'],
                        'rooms'      => ['icon' => 'bi-door-open',          'title' => 'Rooms & Suites'],
                        'amenities'  => ['icon' => 'bi-stars',              'title' => 'Amenities'],
                    ],
                    'Dining Menu' => [
                        'restaurant-categories' => ['icon' => 'bi-tags',      'title' => 'Menu Categories'],
                        'restaurant-items'      => ['icon' => 'bi-egg-fried', 'title' => 'Menu Items'],
                    ],
                    'Resort Media & Guide' => [
                        'gallery-categories' => ['icon' => 'bi-folder',  'title' => 'Gallery Categories'],
                        'gallery-items'      => ['icon' => 'bi-image',   'title' => 'Gallery Items'],
                        'attractions'        => ['icon' => 'bi-compass', 'title' => 'Local Attractions'],
                    ],
                    'Guest Book & Pages' => [
                        'reviews'          => ['icon' => 'bi-chat-left-heart',       'title' => 'Reviews / Feedback'],
                        'contact-messages' => ['icon' => 'bi-envelope',              'title' => 'Message Inbox',     'route' => route('admin.messages.index')],
                        'policies'         => ['icon' => 'bi-file-earmark-text',     'title' => 'Website Policies',  'route' => route('admin.policies.index')],
                        'faqs'             => ['icon' => 'bi-question-circle',       'title' => 'FAQs Management'],
                        'cms-pages'        => ['icon' => 'bi-file-earmark-richtext', 'title' => 'CMS Pages'],
                    ],
                ];
            @endphp

            @foreach($sidebarGroups as $groupName => $items)
                @php
                    $visibleItems = collect($items)->filter(function($config, $key) {
                        $resource = $key === 'contact-messages' ? 'contact-messages' : $key;
                        if($key === 'policies') $resource = 'policies';
                        return auth()->user()->hasPermission("{$resource}.view");
                    });
                @endphp
                @if($visibleItems->isNotEmpty())
                <small class="text-uppercase text-teal tracking-wider extra-small fw-bold opacity-75 d-block mt-4 mb-2">{{ $groupName }}</small>
                @foreach($visibleItems as $key => $config)
                    @php
                        $itemRoute = $config['route'] ?? route('admin.resources.index', $key);
                        $isMessagesRoute = isset($config['route']) && $config['route'];
                    @endphp
                    <a href="{{ $itemRoute }}" class="d-flex align-items-center justify-content-between {{ request()->is('admin/'.($isMessagesRoute ? 'messages*' : $key.'*')) ? 'active' : '' }}">
                        <span><i class="bi {{ $config['icon'] }} me-2"></i> {{ $config['title'] }}</span>
                        @if($key === 'contact-messages' && ($unreadMessageCount ?? 0) > 0)
                            <span class="badge bg-danger text-white ms-2">{{ $unreadMessageCount }}</span>
                        @endif
                    </a>
                @endforeach
                @endif
            @endforeach
            
            <hr class="opacity-25 my-2" style="border-color: rgba(0,140,149,0.2);">
            <a href="{{ route('admin.profile') }}" class="{{ request()->routeIs('admin.profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle me-2"></i> My Profile
            </a>
            <a href="{{ route('home') }}" target="_blank">
                <i class="bi bi-globe2 me-2"></i> View Live Site
            </a>
            <form action="{{ route('logout') }}" method="post" class="mt-3">
                @csrf
                <button class="btn btn-sm btn-teal w-100 py-2"><i class="bi bi-box-arrow-left me-1"></i> Logout</button>
            </form>
        </div>
    </aside>
    
    <div class="admin-body-wrapper">
        <header class="admin-topbar d-flex justify-content-between align-items-center bg-white px-3 px-md-4 shadow-sm border-bottom">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-breadcrumb">
                    <span class="text-muted tb-label">Admin</span>
                    <i class="bi bi-chevron-right text-muted tb-label" style="font-size:0.65rem;"></i>
                    <strong class="text-teal topbar-title small">@yield('title', 'Control Center')</strong>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.messages.index') }}" class="btn btn-white btn-sm border position-relative shadow-sm d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 12px; flex-shrink:0;">
                    <i class="bi bi-envelope-fill text-teal"></i>
                    @if(($unreadMessageCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem; min-width: 18px; height: 18px;">{{ $unreadMessageCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.profile') }}"
                   class="badge bg-teal-soft text-teal font-sans px-2 px-sm-3 py-2 d-flex align-items-center gap-1 text-decoration-none"
                   title="My Profile">
                    <i class="bi bi-person-badge-fill"></i>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </a>
            </div>
        </header>
        
        <main class="admin-main">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-x-circle-fill"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <strong class="d-block mb-2">Please fix the following errors:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<!-- Delete Confirmation Modal — bottom-sheet on mobile, centered on desktop -->
<div class="modal fade delete-modal-redesign" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg bg-white overflow-hidden">
            <div class="modal-body p-4 p-sm-5 text-center">
                <span class="delete-sheet-handle"></span>
                <div class="modal-danger-icon mx-auto mb-3">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <h5 class="fw-bold text-ink mb-2">Delete this record?</h5>
                <p class="text-muted small mb-4">This action is permanent and cannot be reversed. The selected record will be removed from the database.</p>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-light flex-grow-1 py-2 fw-semibold rounded-3" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancel
                    </button>
                    <form id="modalDeleteForm" method="post" action="" class="flex-grow-1">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold rounded-3">
                            <i class="bi bi-trash3-fill me-1"></i>Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="sidebarOverlay" class="sidebar-overlay"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
<script>
// Auto-close flash alerts after 1 second (globally for all admin pages)
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.admin-main .alert').forEach(function (el) {
        setTimeout(function () {
            var instance = bootstrap.Alert.getOrCreateInstance(el);
            if (instance) instance.close();
        }, 1500);
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // ── Mobile Sidebar Toggle ──────────────────────────────────────────
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar  = document.querySelector('.admin-sidebar');
    const overlay       = document.getElementById('sidebarOverlay');

    function openSidebar() {
        adminSidebar.classList.add('sidebar-open');
        overlay.classList.add('active');
        sidebarToggle.setAttribute('aria-expanded', 'true');
        sidebarToggle.querySelector('i').className = 'bi bi-x-lg';
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        adminSidebar.classList.remove('sidebar-open');
        overlay.classList.remove('active');
        sidebarToggle.setAttribute('aria-expanded', 'false');
        sidebarToggle.querySelector('i').className = 'bi bi-list';
        document.body.style.overflow = '';
    }

    if (sidebarToggle && adminSidebar && overlay) {
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
        });
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on nav link click (mobile)
    adminSidebar && adminSidebar.querySelectorAll('a').forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth < 992) closeSidebar();
        });
    });

    // Close on resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebar();
            document.body.style.overflow = '';
        }
    });
    // 1. Delete Confirm Modal Action Handler
    const deleteModal = document.getElementById('deleteConfirmModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            document.getElementById('modalDeleteForm').setAttribute('action', action);
        });
    }

    // 2. Global AJAX Form Validation Handler (Vanilla JS)
    // Intercept form submissions inside the main admin panel content area
    document.querySelectorAll('.admin-main form').forEach(function(form) {
        if (form.id === 'modalDeleteForm') return;
        if (form.hasAttribute('data-no-ajax')) return; // skip forms that want normal submit
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button:not([type])');
            let originalBtnHtml = '';
            if (submitBtn) {
                originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            }
            
            // Clear previous validation highlights and helper texts
            form.querySelectorAll('.is-invalid').forEach(function(el) {
                el.classList.remove('is-invalid');
            });
            form.querySelectorAll('.invalid-feedback').forEach(function(el) {
                el.remove();
            });
            
            const formData = new FormData(form);
            
            fetch(form.getAttribute('action'), {
                method: form.getAttribute('method') || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                if (!response.ok) {
                    return response.text().then(function(text) {
                        let data = null;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            data = null;
                        }
                        throw { status: response.status, data: data, text: text, statusText: response.statusText };
                    });
                }
                return response.json();
            })
            .then(function(responseJSON) {
                // Success redirect or reload
                if (responseJSON.redirect) {
                    window.location.href = responseJSON.redirect;
                } else {
                    window.location.reload();
                }
            })
            .catch(function(error) {
                // Restore button state
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                
                const errorMessage = (error && error.data && (error.data.message || (typeof error.data === 'string' ? error.data : null)))
                    || (error && error.text)
                    || (error && error.statusText)
                    || 'An unexpected error occurred. Please try again.';

                if (error && error.status === 422 && error.data && error.data.errors) {
                    const errors = error.data.errors;
                    
                    const oldSummary = form.querySelector('.form-validation-summary');
                    if (oldSummary) oldSummary.remove();
                    
                    const summaryAlert = document.createElement('div');
                    summaryAlert.className = 'alert alert-danger border-0 shadow-sm mb-4 form-validation-summary d-flex gap-3 align-items-start';
                    
                    let errorListHtml = '<ul class="mb-0 ps-3 small mt-1">';
                    
                    Object.keys(errors).forEach(function(field) {
                        const errorMsg = errors[field][0];
                        errorListHtml += `<li>${errorMsg}</li>`;
                        
                        let input = form.querySelector(`[name="${field}"]`) || form.querySelector(`[name="${field}[]"]`);
                        if (!input && field === 'value') {
                            input = form.querySelector('[name="value"]');
                        }
                        
                        if (input) {
                            input.classList.add('is-invalid');
                            
                            const parentDashed = input.closest('.border-dashed');
                            const parentCheck = input.closest('.form-check');
                            
                            const feedback = document.createElement('div');
                            feedback.className = 'invalid-feedback d-block mt-1';
                            feedback.innerHTML = `<i class="bi bi-exclamation-circle-fill me-1"></i>${errorMsg}`;
                            
                            if (parentDashed) {
                                feedback.classList.add('mt-2');
                                parentDashed.after(feedback);
                            } else if (parentCheck) {
                                feedback.classList.add('mt-2');
                                parentCheck.after(feedback);
                            } else {
                                input.after(feedback);
                            }
                        }
                    });
                    
                    errorListHtml += '</ul>';
                    
                    summaryAlert.innerHTML = `
                        <div class="fs-4 text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div>
                            <strong class="d-block text-ink">Submission Failed</strong>
                            <span class="text-muted small">Please verify and correct the details listed below:</span>
                            ${errorListHtml}
                        </div>
                    `;
                    
                    form.prepend(summaryAlert);
                    summaryAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    const oldSummary = form.querySelector('.form-validation-summary');
                    if (oldSummary) oldSummary.remove();
                    
                    const summaryAlert = document.createElement('div');
                    summaryAlert.className = 'alert alert-danger border-0 shadow-sm mb-4 form-validation-summary d-flex gap-3 align-items-start';
                    summaryAlert.innerHTML = `
                        <div class="fs-4 text-danger"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div>
                            <strong class="d-block text-ink">Submission Failed</strong>
                            <span class="text-muted small">${errorMessage}</span>
                        </div>
                    `;
                    form.prepend(summaryAlert);
                    summaryAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    });
});
</script>
</body>
</html>
