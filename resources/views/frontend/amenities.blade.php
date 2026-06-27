@extends('layouts.frontend')
@section('title', 'Resort Overview & Amenities - Aruvi on the Cliff')
@section('content')
<section class="section section-soft"><div class="container"><div class="eyebrow">Resort Overview</div><h1 class="display-5 fw-bold">Amenities shaped for calm, comfort, and the coast</h1></div></section>
<section class="section"><div class="container"><div class="row g-4">@foreach($amenities as $amenity)<div class="col-md-4"><div class="lux-card p-4 h-100">@if($amenity->image)<img src="{{ asset($amenity->image) }}" class="rounded-circle object-fit-cover shadow-sm border border-light mb-3" alt="{{ $amenity->name }}" style="width: 56px; height: 56px;">@else<span class="icon-badge">A</span>@endif<h4 class="mt-3">{{ $amenity->name }}</h4><p>{{ $amenity->description }}</p></div></div>@endforeach</div></div></section>
@endsection
