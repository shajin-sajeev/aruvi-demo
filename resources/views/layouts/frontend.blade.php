<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Aruvi on the Cliff - Luxury Beachside Resort')</title>
    <meta name="description" content="@yield('meta_description', 'A premium luxury beachside resort with online booking, dining, gallery, amenities, and CMS-managed content.')">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/resort.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand brand-mark d-flex align-items-center gap-2" href="{{ route('home') }}#home">
            <img src="{{ asset($settings['site_logo'] ?? 'images/default/logo.ico') }}" alt="Aruvi on the Cliff" class="brand-logo">
            <img src="{{ asset($settings['site_brand_image'] ?? 'images/default/brand.png') }}" alt="Aruvi on the Cliff" style="height: 52px; max-width: 220px; object-fit: contain;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div id="nav" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#rooms">Rooms</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#amenities">Amenities</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#dining">Dining</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#gallery">Gallery</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#attractions">Attractions</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#reviews">Reviews</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#contact">Contact</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-teal px-3 py-2" href="#">
                        <i class="bi bi-calendar-check me-1"></i>Book Now
                    </a>
                </li>
                @auth
                    @if(auth()->user()->isAdmin())<li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a></li>@endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="container alert-wrap"><div class="alert alert-success mb-0">{{ session('success') }}</div></div>
@endif
@if($errors->any())
    <div class="container alert-wrap"><div class="alert alert-danger mb-0">{{ $errors->first() }}</div></div>
@endif

<main>@yield('content')</main>

<footer class="footer section pb-4" id="footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <a href="{{ route('home') }}#home" class="d-flex align-items-center gap-3 text-decoration-none">
                        <img src="{{ asset($settings['site_logo'] ?? 'images/default/logo.ico') }}" alt="Aruvi Logo" class="footer-logo" style="height:64px;width:auto;">
                        <img src="{{ asset($settings['site_brand_image'] ?? 'images/default/brand.png') }}" alt="Aruvi on the Cliff" style="height:64px;max-width:220px;object-fit:contain;">
                    </a>
                </div>
                <p class="text-muted mb-4 lead-sm">An architectural tribute to the beauty of the coast. Experience the tranquil overlap of luxury, sea breeze, and cliffside dining.</p>
                @isset($socialLinks)
                    <div class="social-row d-flex gap-3 flex-wrap">
                        @foreach($socialLinks as $social)
                            @php
                                // Determine if icon is an uploaded file path or a plain text value
                                $iconValue = $social->icon ?? '';
                                $isFilePath = $iconValue && (
                                    str_starts_with($iconValue, '/uploads/') ||
                                    str_starts_with($iconValue, 'uploads/') ||
                                    str_starts_with($iconValue, '/images/')
                                );
                                // Bootstrap icon fallback map
                                $biClass = match(strtolower($social->platform ?? '')) {
                                    'instagram'      => 'bi-instagram',
                                    'facebook'       => 'bi-facebook',
                                    'youtube'        => 'bi-youtube',
                                    'twitter', 'x'  => 'bi-twitter-x',
                                    'threads'        => 'bi-threads',
                                    'linkedin'       => 'bi-linkedin',
                                    'pinterest'      => 'bi-pinterest',
                                    'tiktok'         => 'bi-tiktok',
                                    'whatsapp'       => 'bi-whatsapp',
                                    default          => 'bi-link-45deg'
                                };
                            @endphp
                            <a href="{{ $social->url }}"
                               target="_blank"
                               rel="noopener"
                               aria-label="{{ $social->platform }}"
                               class="social-link-item"
                               title="{{ ucfirst($social->platform) }}">
                                @if($isFilePath)
                                    <img src="{{ asset($iconValue) }}"
                                         alt="{{ $social->platform }}"
                                         style="width:18px;height:18px;object-fit:contain;filter:brightness(0) invert(1);">
                                @else
                                    <i class="bi {{ $biClass }}"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endisset
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title text-white uppercase tracking-wider mb-4">Contact Us</h6>
                <ul class="list-unstyled footer-contact-list">
                    <li class="d-flex align-items-start gap-3 mb-3">
                        <i class="bi bi-telephone text-teal"></i>
                        <div>
                            <small class="text-muted d-block">Reservations</small>
                            <span class="text-light">{{ $settings['contact_phone'] ?? '+91 9497473320' }}</span>
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-3 mb-3">
                        <i class="bi bi-envelope-open text-teal"></i>
                        <div>
                            <small class="text-muted d-block">Email Address</small>
                            <a href="mailto:{{ $settings['contact_email'] ?? 'aruvionthecliff@gmail.com' }}" class="text-light text-decoration-none hover-teal">{{ $settings['contact_email'] ?? 'aruvionthecliff@gmail.com' }}</a>
                        </div>
                    </li>
                    <li class="d-flex align-items-start gap-3">
                        <i class="bi bi-geo-alt text-teal"></i>
                        <div>
                            <small class="text-muted d-block">Location</small>
                            <span class="text-light">North Cliff in Varkala, Kerala</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title text-white uppercase tracking-wider mb-4">Resort Policies</h6>
                <ul class="list-unstyled footer-links-list">
                    <li class="mb-2"><a href="{{ route('policies.show', 'privacy-policy') }}" class="hover-teal"><i class="bi bi-chevron-right small me-1"></i> Privacy Policy</a></li>
                    <li class="mb-2"><a href="{{ route('policies.show', 'terms-and-conditions') }}" class="hover-teal"><i class="bi bi-chevron-right small me-1"></i> Terms & Conditions</a></li>
                    <li class="mb-2"><a href="{{ route('policies.show', 'cancellation-policy') }}" class="hover-teal"><i class="bi bi-chevron-right small me-1"></i> Cancellation Policy</a></li>
                    <li><a href="{{ route('policies.show', 'resort-policies') }}" class="hover-teal"><i class="bi bi-chevron-right small me-1"></i> Resort Policies</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="footer-title text-white uppercase tracking-wider mb-4">Stay in Touch</h6>
                <p class="text-muted small mb-3">Subscribe to receive private offers, seasonal menus, and coastal updates.</p>
                <form action="{{ route('newsletter.subscribe') }}" method="post" class="footer-form-premium">
                    @csrf
                    <div class="input-group">
                        <input class="form-control bg-dark border-secondary text-white" name="email" type="email" placeholder="Email Address" required>
                        <button class="btn btn-teal px-3"><i class="bi bi-send-fill"></i></button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="border-secondary opacity-25 my-4">
        
        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 footer-bottom">
            <p class="mb-0 text-muted small text-center">&copy; {{ date('Y') }} Aruvi on the Cliff. Handcrafted for premium hospitality. All rights reserved.</p>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    const menuToggle = document.getElementById('nav');
    if (menuToggle) {
        const bsCollapse = new bootstrap.Collapse(menuToggle, {toggle:false});
        navLinks.forEach((l) => {
            l.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    bsCollapse.hide();
                }
            });
        });
    }

    const sectionLinks = Array.from(navLinks).filter((link) => {
        const hash = new URL(link.href, window.location.href).hash;
        return hash && document.querySelector(hash);
    });

    const setActiveNavLink = (activeId) => {
        sectionLinks.forEach((link) => {
            const linkHash = new URL(link.href, window.location.href).hash;
            const isActive = activeId && linkHash === `#${activeId}`;
            link.classList.toggle('active', isActive);
            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const updateActiveSection = () => {
        if (!sectionLinks.length) {
            return;
        }

        const navbarHeight = document.querySelector('.navbar')?.offsetHeight || 0;
        const probeLine = navbarHeight + Math.round(window.innerHeight * 0.28);
        let activeSection = null;

        sectionLinks.forEach((link) => {
            const hash = new URL(link.href, window.location.href).hash;
            const section = document.querySelector(hash);
            const rect = section.getBoundingClientRect();

            if (rect.top <= probeLine && rect.bottom > probeLine) {
                activeSection = section;
            }
        });

        if (!activeSection) {
            const visibleSections = sectionLinks
                .map((link) => document.querySelector(new URL(link.href, window.location.href).hash))
                .filter((section) => section.getBoundingClientRect().top <= probeLine);

            activeSection = visibleSections.length ? visibleSections[visibleSections.length - 1] : null;
        }

        setActiveNavLink(activeSection ? activeSection.id : null);
    };

    updateActiveSection();
    window.addEventListener('scroll', updateActiveSection, { passive: true });
    window.addEventListener('resize', updateActiveSection);
});
</script>
</body>
</html>
