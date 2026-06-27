@extends('layouts.admin')
@section('title', $editing ? 'Edit Policy Page' : 'Create Policy Page')
@section('content')

<div class="mb-3">
    <a href="{{ route('admin.policies.index') }}" class="btn btn-sm btn-link text-teal text-decoration-none fw-semibold p-0">
        <i class="bi bi-arrow-left me-1"></i> Back to Website Policies
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="form-card-header d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1 font-serif text-white">
                {{ $editing ? 'Update Policy Content' : 'Create New Policy Page' }}
            </h5>
            <small class="text-white opacity-70">Manage the policy content shown on the public website.</small>
        </div>
        <span class="badge px-3 py-2 text-uppercase tracking-wider extra-small fw-bold">
            {{ $page->slug }}
        </span>
    </div>

    <form method="post"
          action="{{ $editing ? route('admin.policies.update',$page) : route('admin.policies.store') }}"
          class="p-3 p-sm-4 p-md-5 bg-white"
          enctype="multipart/form-data">
        @csrf
        @if($editing) @method('patch') @endif
        <input type="hidden" name="slug" value="{{ $page->slug }}">

        <div class="row g-3 g-md-4">
            <div class="col-12">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-type text-teal me-1"></i>Page Title
                </label>
                <input type="text" name="title" class="form-control"
                       value="{{ old('title',$page->title) }}"
                       placeholder="Policy Page Title" required>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-text-paragraph text-teal me-1"></i>Page Content
                </label>
                <textarea id="policy-content-editor" name="content" class="form-control" rows="10"
                          placeholder="Enter the policy text here…">{{ old('content',$page->content) }}</textarea>
            </div>

            <div class="col-12">
                <div class="form-check form-switch p-3 border rounded-3 bg-light bg-opacity-50">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_published" value="1"
                           id="switch-is-published" @checked(old('is_published',$page->is_published))>
                    <label class="form-check-label text-ink fw-semibold small" for="switch-is-published">Published</label>
                    <small class="text-muted d-block extra-small mt-1">Enable this to make the policy page available publicly.</small>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4 pt-4 border-top">
            <a href="{{ route('admin.policies.index') }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold">Cancel</a>
            <button class="btn btn-teal px-4 py-2 shadow-sm fw-bold">
                {{ $editing ? 'Update Policy' : 'Create Policy' }}
            </button>
        </div>
    </form>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editorEl = document.querySelector('#policy-content-editor');
    if (!editorEl) return;
    ClassicEditor.create(editorEl, {
        toolbar: ['heading','|','bold','italic','underline','link','bulletedList','numberedList','blockQuote','undo','redo'],
    }).then(editor => {
        const form = editorEl.closest('form');
        if (form) form.addEventListener('submit', () => editor.updateSourceElement());
    }).catch(console.error);
});
</script>
@endsection
