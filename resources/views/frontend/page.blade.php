@extends('layouts.frontend')
@section('title', ($page?->seo_title ?: $title).' - Aruvi on the Cliff')
@section('meta_description', $page?->seo_description ?: 'Aruvi on the Cliff resort information.')
@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="eyebrow">Aruvi on the Cliff</div>
        <h1 class="display-5 fw-bold">{{ $page?->title ?? $title }}</h1>
        <div class="lux-card p-4 p-lg-5 mt-4">{!! $page?->content ?? '<p>This page is ready to be managed from the admin CMS.</p>' !!}</div>
    </div>
</section>
@endsection
