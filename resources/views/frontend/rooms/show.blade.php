@extends('layouts.frontend')
@section('title', $room->seo_title ?: $room->name.' - Aruvi on the Cliff')
@section('meta_description', $room->seo_description ?: $room->short_description)
@section('content')

<section class="section section-soft" style="padding-top:100px;">
    <div class="container">
        <div class="row g-5 align-items-start">

            {{-- Image gallery --}}
            <div class="col-lg-6">
                @if($room->images->isNotEmpty())
                    <div class="rounded-4 overflow-hidden shadow-lg border border-light">
                        <div id="roomDetailCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                @foreach($room->images as $i => $img)
                                    <button type="button" data-bs-target="#roomDetailCarousel"
                                            data-bs-slide-to="{{ $i }}"
                                            class="{{ $i === 0 ? 'active' : '' }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach($room->images as $i => $img)
                                    <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                        <img src="{{ $img->image }}" class="d-block w-100"
                                             alt="{{ $img->alt_text ?: $room->name }}"
                                             style="height:420px;object-fit:cover;">
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#roomDetailCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#roomDetailCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="rounded-4 overflow-hidden shadow-lg border border-light">
                        <img src="{{ $room->cover_image ?: 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80' }}"
                             alt="{{ $room->name }}" class="img-fluid w-100" style="height:420px;object-fit:cover;">
                    </div>
                @endif
            </div>

            {{-- Room details --}}
            <div class="col-lg-6">
                <div class="eyebrow-accent mb-2">{{ $room->type?->name }}</div>
                <h1 class="display-5 fw-bold font-serif text-ink mb-3">{{ $room->name }}</h1>
                <p class="lead text-muted mb-4">{{ $room->short_description }}</p>

                {{-- Specs --}}
                <div class="row g-3 mb-4 pb-4 border-bottom border-light">
                    @if($room->max_adults)
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-teal fs-5"><i class="bi bi-people"></i></span>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size:0.68rem;letter-spacing:.05em;">Capacity</small>
                                <strong class="text-ink small">Up to {{ $room->max_adults + $room->max_children }} guests</strong>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($room->size_sqft)
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-teal fs-5"><i class="bi bi-arrows-fullscreen"></i></span>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size:0.68rem;letter-spacing:.05em;">Size</small>
                                <strong class="text-ink small">{{ number_format($room->size_sqft) }} sqft</strong>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Full description --}}
                @if($room->description)
                    <div class="text-muted mb-4" style="line-height:1.8;">{!! nl2br(e($room->description)) !!}</div>
                @endif

                {{-- Features --}}
                @if($room->features)
                    <div class="mb-4">
                        <h6 class="fw-bold text-ink mb-2">Room Features</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($room->features as $feat)
                                <span class="badge bg-teal-soft text-teal px-3 py-2 rounded-pill" style="font-size:0.82rem;">{{ $feat }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Contact CTA instead of booking --}}
                <div class="d-flex flex-wrap gap-3 mt-4 pt-3 border-top border-light align-items-center">
                    <div>
                        <span class="text-muted small d-block">Starting from</span>
                        @if($room->discount_price)
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-decoration-line-through text-muted">&#8377;{{ number_format($room->price_per_night) }}</span>
                                <strong class="fs-3 text-teal">&#8377;{{ number_format($room->discount_price) }}<span class="text-muted fs-6 fw-normal">/night</span></strong>
                            </div>
                        @else
                            <strong class="fs-3 text-ink">&#8377;{{ number_format($room->price_per_night) }}<span class="text-muted fs-6 fw-normal">/night</span></strong>
                        @endif
                    </div>
                    <a href="{{ route('home') }}#contact" class="btn btn-teal px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-telephone me-2"></i>Enquire Now
                    </a>
                    <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-left me-1"></i>All Rooms
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Amenities --}}
@if($room->amenities->isNotEmpty())
<section class="section section-soft">
    <div class="container">
        <h3 class="fw-bold font-serif text-ink mb-4">Room Amenities</h3>
        <div class="row g-3">
            @foreach($room->amenities as $amenity)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="lux-card p-3 d-flex align-items-center gap-3">
                        @if($amenity->image)
                            <img src="{{ asset($amenity->image) }}" alt="{{ $amenity->name }}"
                                 class="rounded-circle" style="width:40px;height:40px;object-fit:cover;">
                        @else
                            <span class="icon-badge-lg bg-teal-soft text-teal" style="width:40px;height:40px;min-width:40px;border-radius:8px;">
                                <i class="bi bi-check-circle"></i>
                            </span>
                        @endif
                        <span class="fw-semibold text-ink small">{{ $amenity->name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Related rooms --}}
@if($relatedRooms->isNotEmpty())
<section class="section">
    <div class="container">
        <h3 class="fw-bold font-serif text-ink mb-4">Other Rooms &amp; Suites</h3>
        <div class="row g-4">
            @foreach($relatedRooms as $related)
                <div class="col-sm-6 col-lg-4">
                    <x-room-card :room="$related" />
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
