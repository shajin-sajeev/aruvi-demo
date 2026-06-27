@extends('layouts.frontend')
@section('title', 'Restaurant Menu - Aruvi on the Cliff')
@section('content')
<section class="section section-soft"><div class="container"><div class="eyebrow">Dining</div><h1 class="display-5 fw-bold">Coastal cuisine, plated with restraint</h1></div></section>
<section class="section"><div class="container">@foreach($categories as $category)<h3 class="fw-bold mt-4">{{ $category->name }}</h3><div class="row g-4">@foreach($category->items as $item)<div class="col-md-6"><div class="lux-card p-4 h-100"><div class="d-flex justify-content-between"><h5>{{ $item->name }}</h5><strong>₹{{ number_format($item->price) }}</strong></div><p>{{ $item->description }}</p>@if($item->is_signature)<span class="badge text-bg-success">Signature</span>@endif</div></div>@endforeach</div>@endforeach</div></section>
@endsection
