@extends('layouts.frontend')

@section('content')
<!-- Hero Section -->
<div id="home" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
    <div class="carousel-indicators">
        @foreach($slides as $index => $slide)
            <button type="button" data-bs-target="#home" data-bs-slide-to="{{ $index }}" class="@if($index === 0) active @endif" aria-current="true" aria-label="Slide {{ $index + 1 }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner">
        @forelse($slides as $slide)
            <div class="carousel-item @if($loop->first) active @endif">
                <div class="hero-slide-bg" style="background-image: linear-gradient(to right, rgba(7, 59, 63, 0.85), rgba(7, 59, 63, 0.4)), url('{{ $slide->image }}');"></div>
                <div class="hero-content-wrapper">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 text-start">
                                <span class="badge bg-teal-soft text-teal mb-3 px-3 py-2 text-uppercase tracking-wider animate-fade-in-up">{{ $slide->eyebrow ?: 'Luxury Beachside Resort' }}</span>
                                <h1 class="hero-title font-serif animate-fade-in-up delay-1">{{ $slide->title }}</h1>
                                <p class="hero-copy my-4 lead text-light-muted animate-fade-in-up delay-2">{{ $slide->subtitle }}</p>
                                <div class="animate-fade-in-up delay-3">
                                    <a class="btn btn-teal btn-lg px-4 py-3 shadow-lg me-3 transition-transform" href="{{ $slide->button_url ?: '#contact' }}">
                                        <i class="bi bi-calendar-check me-2"></i>{{ $slide->button_label ?: 'Explore Resort' }}
                                    </a>
                                    <a class="btn btn-outline-light btn-lg px-4 py-3 transition-transform" href="#about">
                                        Explore Resort
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="carousel-item active">
                <div class="hero-slide-bg" style="background-image: linear-gradient(to right, rgba(7, 59, 63, 0.8), rgba(7, 59, 63, 0.3)), url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80');"></div>
                <div class="hero-content-wrapper">
                    <div class="container text-start">
                        <h1 class="hero-title font-serif">Aruvi on the Cliff</h1>
                        <p class="hero-copy my-4 lead text-light-muted">Luxury rooms above a luminous shoreline.</p>
                        <a class="btn btn-teal btn-lg px-4 py-3" href="#contact">Contact Us</a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#home" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#home" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>

<!-- About Section -->
<section id="about" class="section section-glow scroll-mt">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-2 order-lg-1">
                <div class="pe-lg-4">
                    <div class="eyebrow-accent mb-2">resort legacy</div>
                    <h2 class="display-5 font-serif fw-bold text-ink mb-4">{{ $sections['about_preview']->title ?? 'Where sea breeze meets considered comfort' }}</h2>
                    <p class="lead text-muted mb-4">{{ $sections['about_preview']->body ?? 'Aruvi on the Cliff blends boutique luxury with the ease of beachside living: tranquil suites, warm service, curated dining, and coastal experiences.' }}</p>
                    <div class="row g-4 mb-4">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="feature-icon"><i class="bi bi-clock-history"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0">Check-in time</h6>
                                    <small class="text-muted">From 2:00 PM</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0">Secure Stay</h6>
                                    <small class="text-muted">Verified booking</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="#contact" class="btn btn-teal-outline px-4 py-2">Get in Touch <i class="bi bi-arrow-right ms-2"></i></a>
                </div>
            </div>
            <div class="col-lg-6 order-1 order-lg-2">
                <div class="position-relative">
                    <img class="img-fluid rounded-4 shadow-xl border border-light" src="{{ $settings['about_image'] ?? 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80' }}" alt="Luxury beach resort view">
                    <div class="badge-overlay-about bg-blur text-teal p-3 rounded-3 shadow">
                        <span class="fs-4 fw-bold block leading-none">5★ Stars</span>
                        <small class="text-muted d-block">Coastal Retreat</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Rooms & Suites Section -->
<section id="rooms" class="section section-soft scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">accommodation</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Rooms & Suites</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Each space is positioned to maximize ocean panoramas and capture the natural soundscape of the sea.</p>
        </div>
        
        <div class="row g-5 align-items-center">
            @if($rooms->isNotEmpty())
                @php $room = $rooms->first(); @endphp
                <!-- Cottage Gallery Carousel -->
                <div class="col-lg-6">
                    <div class="position-relative rounded-4 overflow-hidden shadow-lg border border-light">
                        <div id="roomImagesCarousel" class="carousel slide" data-bs-ride="carousel">
                            <!-- Indicators -->
                            <div class="carousel-indicators">
                                @if($room->images->isNotEmpty())
                                    @foreach($room->images as $index => $img)
                                        <button type="button" data-bs-target="#roomImagesCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
                                    @endforeach
                                @else
                                    <button type="button" data-bs-target="#roomImagesCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                @endif
                            </div>
                            
                            <!-- Slides -->
                            <div class="carousel-inner">
                                @if($room->images->isNotEmpty())
                                    @foreach($room->images as $index => $img)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ $img->image }}" class="d-block w-100" alt="{{ $img->alt_text }}" style="height: 480px; object-fit: cover;">
                                        </div>
                                    @endforeach
                                @else
                                    <div class="carousel-item active">
                                        <img src="{{ $room->cover_image ?: 'https://images.unsplash.com/photo-1583037189850-1921ae7c6c22?auto=format&fit=crop&w=1200&q=80' }}" class="d-block w-100" alt="{{ $room->name }}" style="height: 480px; object-fit: cover;">
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Controls -->
                            <button class="carousel-control-prev" type="button" data-bs-target="#roomImagesCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true" style="background-size: 50%;"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#roomImagesCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true" style="background-size: 50%;"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                        
                        <div class="card-badge bg-teal text-white px-3 py-2 fs-6 position-absolute" style="top: 20px; left: 20px; z-index: 10;">
                            {{ $room->type?->name ?: 'Premium Cottage' }}
                        </div>
                        @php
                            $homeBadgeText = null;
                            $homeBadgeClass = 'bg-danger text-white';
                            if ($room->discount_price && $room->discount_price < $room->price_per_night) {
                                $homeBadgeText = 'Special Offer';
                                $homeBadgeClass = 'bg-danger text-white';
                            } elseif ($room->discount_price && $room->discount_price > $room->price_per_night) {
                                $homeBadgeText = 'Peak Season';
                                $homeBadgeClass = 'bg-warning text-dark';
                            }
                        @endphp
                        @if($homeBadgeText)
                            <div class="offer-badge {{ $homeBadgeClass === 'bg-warning text-dark' ? 'offer-badge--peak' : 'offer-badge--offer' }}">
                                @if($homeBadgeClass === 'bg-warning text-dark')
                                    <i class="bi bi-calendar-event-fill"></i>
                                @else
                                    <i class="bi bi-tag-fill"></i>
                                @endif
                                <span>{{ $homeBadgeText }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Cottage Detail Specs & Actions -->
                <div class="col-lg-6">
                    <div class="ps-lg-4">
                        <span class="eyebrow-accent mb-2 d-inline-block">The Signature Stay</span>
                        <h2 class="display-6 font-serif fw-bold text-ink mb-3">{{ $room->name }}</h2>
                        <p class="text-muted mb-4 lead" style="font-size: 1.05rem; line-height: 1.6;">
                            {{ $room->description ?: $room->short_description }}
                        </p>
                        
                        <!-- Specs Grid -->
                        <div class="row g-4 mb-4 pb-3 border-bottom border-top border-light py-3">
                            <div class="col-6 col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-teal fs-4"><i class="bi bi-people"></i></span>
                                    <div>
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Capacity</small>
                                        <strong class="text-ink d-block" style="font-size: 0.9rem;">Up to {{ $room->max_adults + $room->max_children }} guests</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-teal fs-4"><i class="bi bi-arrows-fullscreen"></i></span>
                                    <div>
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Size</small>
                                        <strong class="text-ink d-block" style="font-size: 0.9rem;">{{ $room->size_sqft }} sqft</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-teal fs-4"><i class="bi bi-compass"></i></span>
                                    <div>
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">View</small>
                                        <strong class="text-ink d-block" style="font-size: 0.9rem;">Ocean View</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-teal fs-4"><i class="bi bi-house-door"></i></span>
                                    <div>
                                        <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Availability</small>
                                        <strong class="text-ink d-block" style="font-size: 0.9rem;">10 Cottages</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Features -->
                        <div class="mb-4">
                            <span class="text-ink fw-bold d-block mb-2">Cottage Features:</span>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($room->features ?? ['Sea View', 'Hammock', 'Private Deck', 'Breakfast Included'] as $feat)
                                    <span class="badge bg-teal-soft text-teal border border-teal-subtle px-3 py-2 rounded-pill" style="font-size: 0.85rem;">{{ $feat }}</span>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Booking & Rates -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-4 mt-4 pt-3 border-top border-light">
                            <div>
                                <span class="text-muted small d-block">Exclusive Rate</span>
                                @if($room->discount_price)
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="text-decoration-line-through text-muted fs-6">₹{{ number_format($room->price_per_night) }}</span>
                                        <strong class="fs-3 text-teal">₹{{ number_format($room->discount_price) }}<span class="text-muted fs-6 font-sans fw-normal">/night</span></strong>
                                    </div>
                                @else
                                    <strong class="fs-3 text-ink">₹{{ number_format($room->price_per_night) }}<span class="text-muted fs-6 font-sans fw-normal">/night</span></strong>
                                @endif
                            </div>
                            
                            <div class="d-flex gap-3">
                                <a class="btn btn-teal px-5 py-2 fw-semibold" href="{{ route('rooms.show', $room) }}">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No cottages currently featured.</p>
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Amenities Section -->
<section id="amenities" class="section scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">indulgence</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Resort Amenities</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Thoughtfully curated spaces and personalized services designed to ensure comfort and ease.</p>
        </div>
        
        <div class="row g-4">
            @foreach($amenities as $amenity)
                <div class="col-lg-4 col-md-6">
                    <div class="lux-card p-4 h-100 card-hover-effect text-center text-md-start">
                        <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                            @if($amenity->image)
                                <img src="{{ asset($amenity->image) }}" class="rounded-circle object-fit-cover shadow-sm border border-light" alt="{{ $amenity->name }}" style="width: 56px; height: 56px; min-width: 56px;">
                            @else
                                <span class="icon-badge-lg text-teal bg-teal-soft mb-3 mb-md-0">
                                    <i class="bi bi-check-circle fs-3"></i>
                                </span>
                            @endif
                            <div>
                                <h5 class="fw-bold text-ink mb-1">{{ $amenity->name }}</h5>
                                <p class="text-muted small mb-0">{{ $amenity->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Dynamic Admin Managed Sections -->
@foreach($sections as $section)
    @if($section->section_key !== 'about_preview' && $section->is_active)
        <section class="section @if($loop->odd) section-soft @endif scroll-mt">
            <div class="container">
                <div class="text-center">
                    <h2 class="display-5 font-serif fw-bold text-ink mb-4">{{ $section->title }}</h2>
                    <div class="text-muted mx-auto" style="max-width: 800px; line-height: 1.8;">
                        {!! nl2br(e($section->body)) !!}
                    </div>
                </div>
            </div>
        </section>
    @endif
@endforeach

<!-- Dining Section -->
<section id="dining" class="section section-soft scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">gastronomy</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Coastal Dining</h2>
            <p class="text-muted mx-auto mb-4" style="max-width: 600px;">Rooted in fresh coastal ingredients, local spices, and international culinary craft.</p>
        </div>

        <!-- Dining Category Tabs -->
        <ul class="nav nav-tabs dining-tabs justify-content-center mb-5" id="diningTabs" role="tablist">
            @foreach($menuCategories as $index => $category)
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if($index === 0) active @endif" id="cat-tab-{{ $category->id }}" data-bs-toggle="tab" data-bs-target="#cat-pane-{{ $category->id }}" type="button" role="tab" aria-controls="cat-pane-{{ $category->id }}" aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                        {{ $category->name }}
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- Category Dishes Tab Pane -->
        <div class="tab-content" id="diningTabContent">
            @foreach($menuCategories as $index => $category)
                <div class="tab-pane fade @if($index === 0) show active @endif" id="cat-pane-{{ $category->id }}" role="tabpanel" aria-labelledby="cat-tab-{{ $category->id }}" tabindex="0">
                    <div class="row g-4 justify-content-center">
                        @forelse($category->items as $item)
                            <div class="col-md-6 col-lg-4">
                                <div class="dish-card" 
                                     data-name="{{ $item->name }}" 
                                     data-price="₹{{ number_format($item->price) }}" 
                                     data-image="{{ $item->image ?: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=600&q=80' }}" 
                                     data-description="{{ $item->description }}" 
                                     data-signature="{{ $item->is_signature ? '1' : '0' }}"
                                     role="button"
                                     tabindex="0">
                                    <div class="dish-image-wrapper">
                                        @if($item->is_signature)
                                            <span class="dish-card-badge"><i class="bi bi-bookmark-star-fill me-1"></i>Chef Signature</span>
                                        @endif
                                        <img src="{{ $item->image ?: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=600&q=80' }}" class="dish-image" alt="{{ $item->name }}">
                                        <div class="dish-card-overlay"></div>
                                    </div>
                                    <div class="p-4 d-flex flex-column flex-grow-1 justify-content-between">
                                        <div>
                                            <h5 class="fw-bold text-ink mb-1 font-serif">{{ $item->name }}</h5>
                                            <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 38px;">{{ $item->description }}</p>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top border-light">
                                            <span class="dish-card-price">₹{{ number_format($item->price) }}</span>
                                            <span class="btn btn-outline-teal btn-sm rounded-pill px-3">View Details</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">No items available in this category currently.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section id="gallery" class="section scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">visual journey</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Resort Gallery</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Capture the essence of coastal design, golden sunsets, and premium rooms.</p>
            
            <!-- Category filter pills -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-4" id="gallery-filters">
                <button class="btn filter-btn active" data-filter="all">All Photos</button>
                @foreach($galleryCategories as $cat)
                    <button class="btn filter-btn" data-filter="cat-{{ $cat->id }}">{{ $cat->name }}</button>
                @endforeach
            </div>
        </div>
        
        <div class="row g-4" id="gallery-grid">
            @foreach($galleryCategories as $cat)
                @foreach($cat->items as $item)
                    <div class="col-lg-4 col-md-6 gallery-item cat-{{ $cat->id }}">
                        <div class="gallery-card rounded-4 overflow-hidden position-relative shadow">
                            <img class="room-image" src="{{ $item->image }}" alt="{{ $item->alt_text ?: $item->title }}">
                            <div class="gallery-overlay d-flex flex-column justify-content-end p-4">
                                <h5 class="text-white font-serif mb-1">{{ $item->title }}</h5>
                                <small class="text-light-muted">{{ $cat->name }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</section>

<!-- Attractions Section -->
<section id="attractions" class="section section-soft scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">explore</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Nearby Attractions</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Venture out and explore the local landscapes, backwater trails, and cultural hubs.</p>
        </div>
        
        <div class="row g-4">
            @foreach($attractions as $attraction)
                <div class="col-lg-4 col-md-6">
                    <div class="lux-card h-100 card-hover-effect">
                        @if($attraction->image)
                            <div class="overflow-hidden">
                                <img src="{{ $attraction->image }}" class="room-image" alt="{{ $attraction->name }}">
                            </div>
                        @endif
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h5 class="fw-bold font-serif text-ink mb-0">{{ $attraction->name }}</h5>
                                <span class="badge bg-teal text-white small"><i class="bi bi-geo-alt me-1"></i>{{ $attraction->distance }}</span>
                            </div>
                            <p class="text-muted small mb-0">{{ $attraction->description }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="section scroll-mt">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="faq-callout p-5 rounded-4 text-white shadow h-100 d-flex flex-column justify-content-between" style="background: linear-gradient(135deg, var(--deep) 0%, #0d5c63 100%);">
                    <div>
                        <div class="icon-badge-lg bg-light bg-opacity-10 text-white mb-4">
                            <i class="bi bi-patch-question fs-2 text-white"></i>
                        </div>
                        <h2 class="font-serif fw-bold mb-3 display-6">Frequently Asked Queries</h2>
                        <p class="opacity-75 small">Find quick answers about check-in, policies, dining, and custom stay queries at Aruvi on the Cliff.</p>
                    </div>
                    <div class="mt-4">
                        <p class="small mb-2 opacity-75">Still have questions?</p>
                        <a href="#contact" class="btn btn-light text-teal fw-bold btn-sm px-3 py-2 rounded-3">
                            <i class="bi bi-chat-dots-fill me-1"></i> Contact Concierge
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="accordion accordion-faq" id="faqAccordion">
                    @php $faqIndex = 0; @endphp
                    @foreach($faqs as $category => $categoryFaqs)
                        <div class="faq-category-group @if($faqIndex > 0) mt-4 @endif">
                            <span class="badge bg-teal-soft text-teal font-sans uppercase tracking-wider mb-3 px-3 py-2">{{ $category }}</span>
                            @foreach($categoryFaqs as $faq)
                                <div class="accordion-item faq-item mb-3 rounded-3 overflow-hidden shadow-sm border border-light">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed font-serif bg-white text-ink py-3 px-4 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq-{{ $faq->id }}">
                                            {{ $faq->question }}
                                        </button>
                                    </h2>
                                    <div id="faq-{{ $faq->id }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body bg-white text-muted pt-1 pb-4 px-4 small leading-relaxed">
                                            {{ $faq->answer }}
                                        </div>
                                    </div>
                                </div>
                                @php $faqIndex++; @endphp
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews & Testimonials Section -->
<section id="reviews" class="section section-soft scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">guest voices</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Reviews & Testimonials</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Read about other guest experiences or leave your own review.</p>
        </div>
        
        <div class="row g-5 align-items-start">
            <div class="col-lg-7">
                <div class="row g-4">
                    @foreach($reviews as $review)
                        <div class="col-md-6">
                            <div class="lux-card p-4 h-100 shadow-sm bg-white border-0">
                                <div class="text-warning mb-2">
                                    @for($i = 0; $i < 5; $i++)
                                        <i class="bi {{ $i < $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                                <h6 class="fw-bold text-ink font-serif mb-1">{{ $review->title ?: 'Wonderful Stay' }}</h6>
                                <p class="text-muted small mb-3">“{{ $review->comment }}”</p>
                                <div class="d-flex align-items-center gap-2 mt-auto">
                                    <span class="icon-avatar-small bg-teal-soft text-teal"><i class="bi bi-person"></i></span>
                                    <div>
                                        <strong class="small d-block text-ink">{{ $review->name }}</strong>
                                        <small class="text-muted extra-small">{{ $review->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="lux-card p-4 shadow-lg border-0 bg-white rounded-4">
                    <h4 class="fw-bold font-serif mb-3 text-ink">Write a Review</h4>
                    <p class="text-muted small">Share your thoughts on your stay. Your review will be published instantly.</p>
                    
                    <form action="{{ route('reviews.store') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email Address (Optional)</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Rating</label>
                                <select name="rating" class="form-select" required>
                                    <option value="5">5 Stars (Excellent)</option>
                                    <option value="4">4 Stars (Very Good)</option>
                                    <option value="3">3 Stars (Average)</option>
                                    <option value="2">2 Stars (Poor)</option>
                                    <option value="1">1 Star (Very Poor)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Review Title</label>
                                <input type="text" name="title" class="form-control" placeholder="e.g. Dream vacation" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Comments</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Detail your experience..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-teal w-100 py-2 mt-2">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Us Section -->
<section id="contact" class="section scroll-mt">
    <div class="container">
        <div class="text-center mb-5">
            <span class="eyebrow-accent">connect</span>
            <h2 class="display-5 font-serif fw-bold text-ink">Contact Us</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Have questions? Reach out to our front desk for planning assistance and customizations.</p>
        </div>
        
        <div class="row g-5">
            <div class="col-lg-6">
                <div class="lux-card p-4 h-100 bg-white border-0 shadow">
                    <h4 class="fw-bold font-serif mb-4 text-ink">Get in touch</h4>
                    
                    <form action="{{ route('contact.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Your Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Full name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="Mobile">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="Query topic">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Your Message</label>
                            <textarea name="message" class="form-control" rows="5" placeholder="Write message details..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-teal px-4 py-2 mt-2">Send Message <i class="bi bi-send-fill ms-2"></i></button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="position-relative rounded-4 overflow-hidden h-100 shadow" style="min-h: 400px;">
                    <iframe src="{{ $settings['google_maps_embed'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5204481078726!2d76.7004967!3d8.7403549!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b05efb512d38999%3A0x3521e5b9dfe724c8!2sAruvi%20Onthe%20Cliff!5e0!3m2!1sen!2sin!4v1718360000000!5m2!1sen!2sin' }}" 
                            class="w-100 h-100 position-absolute" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dish Detail Modal -->
<div class="modal fade" id="dishDetailModal" tabindex="-1" aria-labelledby="dishDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content modal-content-premium bg-white">
            <div class="modal-header border-0 pb-0">
            </div>
            <div class="modal-body pt-0">
                <div class="row g-4">
                    <div class="col-md-5">
                        <div class="dish-detail-image-wrapper">
                            <img id="modalDishImage" src="" class="dish-detail-image" alt="">
                        </div>
                    </div>
                    <div class="col-md-7 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span id="modalDishSignature" class="badge bg-teal text-white small px-2.5 py-1.5" style="display: none;"><i class="bi bi-bookmark-star-fill me-1"></i>Chef Choice</span>
                                <span class="badge bg-teal-soft text-teal small px-2.5 py-1.5"><i class="bi bi-egg-fried me-1"></i>Coastal Signature</span>
                            </div>
                            <h3 id="modalDishName" class="fw-bold font-serif text-ink mb-1"></h3>
                            <h4 id="modalDishPrice" class="fw-bold text-teal font-sans mb-3"></h4>
                            
                            <div class="mb-4">
                                <small class="text-muted text-uppercase tracking-wider d-block mb-1 fw-bold" style="font-size: 0.72rem;">Description</small>
                                <p id="modalDishDescription" class="text-muted leading-relaxed small"></p>
                            </div>
                            
                            <div class="mb-4 bg-light p-3 rounded-3">
                                <div class="d-flex align-items-center gap-2 text-ink mb-1 small fw-bold">
                                    <i class="bi bi-info-circle text-teal"></i> Dietary Information
                                </div>
                                <p class="text-muted extra-small mb-0">Contains fresh seafood/poultry, aromatic local herbs, coconut oils, and authentic Malabar seasoning. Please inform our servers of any food allergies.</p>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2 mt-3 pt-3 border-top border-light">
                            <a href="#contact" id="dishModalOrderBtn" class="btn btn-teal px-4 flex-grow-1"><i class="bi bi-telephone-outbound me-2"></i>Order via Front Desk</a>
                            <button type="button" id="dishModalCloseBtn2" data-bs-dismiss="modal" class="btn btn-outline-secondary">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Filter & Dining Modal Javascript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gallery filter
    const filterButtons = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            galleryItems.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                    item.classList.add('fade-in-item');
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Dining Detail Modal — populate data when a dish card is clicked
    document.querySelectorAll('.dish-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.closest('a, button')) return;

            document.getElementById('modalDishName').textContent        = card.dataset.name        || '';
            document.getElementById('modalDishPrice').textContent       = card.dataset.price       || '';
            document.getElementById('modalDishDescription').textContent = card.dataset.description || '';

            const imgEl = document.getElementById('modalDishImage');
            imgEl.src = card.dataset.image || '';
            imgEl.alt = card.dataset.name  || '';

            const sigEl = document.getElementById('modalDishSignature');
            sigEl.style.display = card.dataset.signature === '1' ? 'inline-block' : 'none';

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('dishDetailModal'));
            modal.show();
        });

        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); card.click(); }
        });
    });
});
</script>
@endsection
