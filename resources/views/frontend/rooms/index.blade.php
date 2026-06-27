@extends('layouts.frontend')
@section('title', 'Rooms & Suites - Aruvi on the Cliff')
@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="eyebrow-accent mb-2">Rooms &amp; Suites</div>
        <h1 class="display-5 fw-bold font-serif text-ink">Private stays above the shoreline</h1>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="row g-4">
            @foreach($rooms as $room)
                <div class="col-sm-6 col-lg-4">
                    <x-room-card :room="$room" />
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $rooms->links() }}</div>
    </div>
</section>
@endsection
