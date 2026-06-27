@extends('layouts.frontend')
@section('title', 'Gallery - Aruvi on the Cliff')
@section('content')
<section class="section section-soft"><div class="container"><div class="eyebrow">Gallery</div><h1 class="display-5 fw-bold">A glimpse of the cliff, coast, and calm</h1></div></section>
<section class="section"><div class="container">@foreach($categories as $category)<h3 class="fw-bold mt-4">{{ $category->name }}</h3><div class="row g-4">@foreach($category->items as $item)<div class="col-md-4"><a href="{{ $item->image }}" target="_blank" class="lux-card d-block"><img class="room-image" src="{{ $item->image }}" alt="{{ $item->alt_text ?: $item->title }}"><div class="p-3">{{ $item->title }}</div></a></div>@endforeach</div>@endforeach</div></section>
@endsection
