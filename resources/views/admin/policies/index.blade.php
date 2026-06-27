@extends('layouts.admin')
@section('title', 'Website Policies')
@section('content')

<div class="admin-page-header">
    <div>
        <h1>Website Policies</h1>
        <p class="text-muted small mb-0">Manage the core resort policy pages displayed on the website.</p>
    </div>
</div>

<div class="row g-3 g-md-4">
    @foreach($policyNames as $slug => $label)
        @php
            $page = $pages->get($slug);
            $isPublished = $page?->is_published ? true : false;
        @endphp
        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                        <div class="min-w-0">
                            <h5 class="fw-bold mb-1 text-ink">{{ $label }}</h5>
                            <small class="text-muted">Slug: <code>{{ $slug }}</code></small>
                        </div>
                        <span class="badge {{ $isPublished ? 'bg-success' : 'bg-secondary' }} text-white text-uppercase flex-shrink-0 py-2 px-3" style="font-size:0.65rem;">
                            {{ $isPublished ? 'Published' : 'Draft' }}
                        </span>
                    </div>

                    <p class="text-muted small mb-4">
                        {{ $page ? \Illuminate\Support\Str::limit(strip_tags($page->content),110) : 'Policy page not yet created. Add content to display it on the website.' }}
                    </p>

                    <div class="d-flex gap-2 policy-card-actions flex-wrap">
                        @if($page)
                            <a href="{{ route('admin.policies.edit',$page) }}" class="btn btn-outline-teal px-4 py-2 fw-semibold flex-grow-1">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </a>
                            <button class="btn btn-outline-danger px-4 py-2 fw-semibold flex-grow-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteConfirmModal"
                                    data-action="{{ route('admin.policies.destroy',$page) }}">
                                <i class="bi bi-trash3-fill me-1"></i>Delete
                            </button>
                        @else
                            <a href="{{ route('admin.policies.create',$slug) }}" class="btn btn-teal px-4 py-2 fw-semibold w-100">
                                <i class="bi bi-plus-circle me-1"></i>Create Page
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
