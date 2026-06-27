@extends('layouts.frontend')
@section('title', 'FAQ - Aruvi on the Cliff')
@section('content')
<section class="section section-soft"><div class="container"><div class="eyebrow">FAQ</div><h1 class="display-5 fw-bold">Answers before arrival</h1></div></section>
<section class="section"><div class="container">@foreach($faqs as $category => $items)<h3>{{ $category }}</h3><div class="accordion mb-4" id="faq{{ $loop->index }}">@foreach($items as $faq)<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q{{ $faq->id }}">{{ $faq->question }}</button></h2><div id="q{{ $faq->id }}" class="accordion-collapse collapse"><div class="accordion-body">{{ $faq->answer }}</div></div></div>@endforeach</div>@endforeach</div></section>
@endsection
