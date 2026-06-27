@extends('layouts.frontend')
@section('title', 'Location & Nearby Attractions - Aruvi on the Cliff')
@section('content')
<section class="section section-soft"><div class="container"><div class="eyebrow">Location</div><h1 class="display-5 fw-bold">Close to the sea, connected to the coast</h1></div></section>
<section class="section"><div class="container"><div class="ratio ratio-21x9 lux-card mb-5"><iframe src="{{ $settings['google_maps_embed'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5204481078726!2d76.7004967!3d8.7403549!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b05efb512d38999%3A0x3521e5b9dfe724c8!2sAruvi%20Onthe%20Cliff!5e0!3m2!1sen!2sin!4v1718360000000!5m2!1sen!2sin' }}" allowfullscreen loading="lazy"></iframe></div><div class="row g-4">@foreach($attractions as $attraction)<div class="col-md-4"><div class="lux-card p-4 h-100"><h4>{{ $attraction->name }}</h4><p>{{ $attraction->description }}</p><strong>{{ $attraction->distance }}</strong></div></div>@endforeach</div></div></section>
@endsection
