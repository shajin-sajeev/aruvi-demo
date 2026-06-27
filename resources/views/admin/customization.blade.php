@extends('layouts.admin')
@section('title', 'Theme & Logo Customization')
@section('content')

<div class="admin-page-header">
    <div>
        <h1>Theme &amp; Logo Customization</h1>
        <p class="text-muted small mb-0">Upload logos and section images for the website and admin panel.</p>
    </div>
</div>

<form method="post" action="{{ route('admin.theme.customization.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3 g-md-4">

        {{-- Branding Logos --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="mb-0 fw-bold text-ink font-serif">
                        <i class="bi bi-slack text-teal me-2"></i>Branding Logos
                    </h5>
                </div>
                <div class="card-body p-4">

                    {{-- Website Logo --}}
                    <div class="mb-4 pb-4 border-bottom border-light">
                        <label class="form-label fw-bold small text-ink">Website Logo</label>
                        <input class="form-control mb-2" type="file" name="site_logo" accept="image/*,.ico">
                        <div class="d-inline-flex align-items-center gap-3 bg-light px-3 py-2 rounded-3 customization-logo-preview">
                            <img src="{{ asset($settings['site_logo'] ?? 'images/default/logo.ico') }}"
                                 alt="Website Logo" style="height:40px;max-width:120px;object-fit:contain;">
                            <small class="text-muted">Current logo</small>
                        </div>
                    </div>

                    {{-- Navbar Brand Image --}}
                    <div class="mb-4 pb-4 border-bottom border-light">
                        <label class="form-label fw-bold small text-ink">Navbar Brand Image</label>
                        <input class="form-control mb-2" type="file" name="site_brand_image" accept="image/*">
                        <div class="d-inline-flex align-items-center gap-3 bg-light px-3 py-2 rounded-3 customization-logo-preview">
                            <img src="{{ asset($settings['site_brand_image'] ?? 'images/default/brand.png') }}"
                                 alt="Navbar Brand" style="height:40px;max-width:120px;object-fit:contain;">
                            <small class="text-muted">Current navbar image</small>
                        </div>
                    </div>

                    {{-- Admin Logo --}}
                    <div>
                        <label class="form-label fw-bold small text-ink">Admin Panel Logo</label>
                        <input class="form-control mb-2" type="file" name="admin_logo" accept="image/*,.ico">
                        <div class="d-inline-flex align-items-center gap-3 bg-dark px-3 py-2 rounded-3 customization-logo-preview">
                            <img src="{{ asset($settings['admin_logo'] ?? 'images/default/logo.ico') }}"
                                 alt="Admin Logo" style="height:40px;max-width:120px;object-fit:contain;">
                            <small class="text-white opacity-50">Current admin logo</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Images --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="mb-0 fw-bold text-ink font-serif">
                        <i class="bi bi-images text-teal me-2"></i>Section Background Images
                    </h5>
                </div>
                <div class="card-body p-4">

                    {{-- About Image --}}
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-ink">About Us Section Image</label>
                        <input class="form-control mb-2" type="file" name="about_image" accept="image/*">
                        <img src="{{ $settings['about_image'] ?? 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=600&q=80' }}"
                             class="img-thumbnail rounded-3 d-block"
                             alt="About image"
                             style="max-height:120px;width:auto;object-fit:cover;">
                        <small class="text-muted extra-small mt-1 d-block">Current about section image</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-teal px-4 py-2 shadow-sm fw-bold">
            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Update Logos &amp; Images
        </button>
    </div>
</form>
@endsection
