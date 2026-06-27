@extends('layouts.admin')
@section('title', $config['title'])
@section('content')

@php
// Bootstrap icon map for social platforms (used when no uploaded image)
$platformIcons = [
    'facebook'  => ['icon' => 'bi-facebook',   'bg' => '#1877f2'],
    'instagram' => ['icon' => 'bi-instagram',  'bg' => '#e1306c'],
    'twitter'   => ['icon' => 'bi-twitter-x',  'bg' => '#000000'],
    'x'         => ['icon' => 'bi-twitter-x',  'bg' => '#000000'],
    'youtube'   => ['icon' => 'bi-youtube',    'bg' => '#ff0000'],
    'linkedin'  => ['icon' => 'bi-linkedin',   'bg' => '#0a66c2'],
    'pinterest' => ['icon' => 'bi-pinterest',  'bg' => '#e60023'],
    'tiktok'    => ['icon' => 'bi-tiktok',     'bg' => '#010101'],
    'whatsapp'  => ['icon' => 'bi-whatsapp',   'bg' => '#25d366'],
];
@endphp

<div class="admin-page-header">
    <div>
        <h1>{{ $config['title'] }}</h1>
        <p class="text-muted small mb-0">Manage content entries, view details, and perform record updates.</p>
        @if($resource === 'hero-slides')
            <div class="mt-2 d-flex align-items-center gap-2">
                @php $atLimit = ($heroSlideCount ?? 0) >= 5; @endphp
                <div class="d-flex gap-1">
                    @for($s = 1; $s <= 5; $s++)
                        <span class="rounded-pill d-inline-block" style="width:22px;height:6px;background:{{ $s <= ($heroSlideCount ?? 0) ? '#008C95' : '#dee2e6' }};"></span>
                    @endfor
                </div>
                <span class="small {{ $atLimit ? 'text-danger fw-semibold' : 'text-muted' }}">
                    {{ $heroSlideCount ?? 0 }}/5 slides used{{ $atLimit ? ' — limit reached' : '' }}
                </span>
            </div>
        @endif
    </div>
    @php $canAdd = !($resource === 'hero-slides' && ($heroSlideCount ?? 0) >= 5); @endphp
    @if($canAdd)
        <a class="btn btn-teal fw-semibold shadow-sm" href="{{ route('admin.resources.create', $resource) }}">
            <i class="bi bi-plus-lg me-2"></i>Add New Entry
        </a>
    @else
        <button class="btn btn-secondary fw-semibold shadow-sm" disabled title="Maximum 5 slides reached">
            <i class="bi bi-slash-circle me-2"></i>Limit Reached
        </button>
    @endif
</div>

{{-- ── Desktop Table ─────────────────────────────────────────── --}}
<div class="listing-desktop-table">
    <div class="table-card table-responsive">
        <table class="table align-middle mb-0 table-hover">
            <thead>
                <tr>
                    <th class="ps-4" style="width:70px;">ID</th>
                    <th>Record Details</th>
                    <th>Last Updated</th>
                    <th class="pe-4 text-end" style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $isSocialLinks = $resource === 'social-links';

                        if ($isSocialLinks) {
                            $platform    = strtolower($item->platform ?? '');
                            $titleText   = ucfirst($item->platform ?? 'Unknown');
                            $subText     = $item->url ?? null;
                            $iconValue   = $item->icon ?? '';
                            // Detect uploaded file vs old text value
                            $hasUploadedImage = $iconValue && (
                                str_starts_with($iconValue, '/uploads/') ||
                                str_starts_with($iconValue, 'uploads/')
                            );
                            $platformMeta = $platformIcons[$platform] ?? ['icon' => 'bi-link-45deg', 'bg' => '#6c757d'];
                            $thumb = null;
                        } else {
                            $platform    = '';
                            $titleText   = $item->title ?? $item->name ?? $item->key ?? $item->email ?? 'Record #'.$item->id;
                            $subText     = null;
                            $hasUploadedImage = false;
                            $platformMeta = null;
                            $thumb = $item->image ?? $item->cover_image ?? null;
                        }
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-secondary small">#{{ $item->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($isSocialLinks)
                                    @if($hasUploadedImage)
                                        {{-- Uploaded custom image --}}
                                        <div class="admin-thumb-avatar me-3 d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
                                             style="padding:5px;">
                                            <img src="{{ asset($iconValue) }}"
                                                 alt="{{ $titleText }}"
                                                 style="width:28px;height:28px;object-fit:contain;">
                                        </div>
                                    @else
                                        {{-- Bootstrap icon in brand colour circle --}}
                                        <div class="admin-thumb-avatar me-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="background:{{ $platformMeta['bg'] }};border-radius:10px;">
                                            <i class="bi {{ $platformMeta['icon'] }} text-white"
                                               style="font-size:1.15rem;"></i>
                                        </div>
                                    @endif
                                @elseif($thumb)
                                    <img src="{{ asset($thumb) }}" class="admin-thumb-avatar me-3" alt="Thumbnail">
                                @else
                                    <div class="admin-thumb-avatar me-3 d-flex align-items-center justify-content-center bg-teal-soft text-teal fw-bold font-serif"
                                         style="font-size:1.1rem;">
                                        {{ strtoupper(substr($titleText,0,1)) }}
                                    </div>
                                @endif

                                <div>
                                    <span class="fw-bold d-block text-ink">{{ $titleText }}</span>
                                    @if($isSocialLinks && $subText)
                                        <small class="text-muted extra-small">{{ \Illuminate\Support\Str::limit($subText, 50) }}</small>
                                        <span class="badge ms-1 {{ $item->is_active ? 'bg-success' : 'bg-secondary' }} text-white"
                                              style="font-size:0.6rem;">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    @elseif(isset($item->category))
                                        <small class="text-teal extra-small fw-semibold text-uppercase">{{ $item->category }}</small>
                                    @elseif(isset($item->room_number))
                                        <small class="text-teal extra-small fw-semibold text-uppercase">Room {{ $item->room_number }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small">
                            {{ optional($item->updated_at)->format('d M Y') }}
                            <small class="d-block extra-small">{{ optional($item->updated_at)->format('H:i A') }}</small>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-2">
                                <a class="btn btn-action-circle btn-edit"
                                   href="{{ route('admin.resources.edit',[$resource,$item->id]) }}"
                                   title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button class="btn btn-action-circle btn-delete"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteConfirmModal"
                                        data-action="{{ route('admin.resources.destroy',[$resource,$item->id]) }}"
                                        title="Delete">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                            No records found for {{ $config['title'] }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Mobile Card List ──────────────────────────────────────── --}}
<div class="listing-mobile-cards">
    @forelse($items as $item)
        @php
            $isSocialLinks = $resource === 'social-links';

            if ($isSocialLinks) {
                $platform    = strtolower($item->platform ?? '');
                $titleText   = ucfirst($item->platform ?? 'Unknown');
                $subText     = $item->url ?? null;
                $iconValue   = $item->icon ?? '';
                $hasUploadedImage = $iconValue && (
                    str_starts_with($iconValue, '/uploads/') ||
                    str_starts_with($iconValue, 'uploads/')
                );
                $platformMeta = $platformIcons[$platform] ?? ['icon' => 'bi-link-45deg', 'bg' => '#6c757d'];
                $thumb = null;
            } else {
                $platform    = '';
                $titleText   = $item->title ?? $item->name ?? $item->key ?? $item->email ?? 'Record #'.$item->id;
                $subText     = null;
                $hasUploadedImage = false;
                $platformMeta = null;
                $thumb = $item->image ?? $item->cover_image ?? null;
            }
        @endphp
        <div class="admin-record-card">
            <div class="admin-record-card-header">
                @if($isSocialLinks)
                    @if($hasUploadedImage)
                        <div class="admin-thumb-avatar d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
                             style="padding:5px;">
                            <img src="{{ asset($iconValue) }}" alt="{{ $titleText }}"
                                 style="width:28px;height:28px;object-fit:contain;">
                        </div>
                    @else
                        <div class="admin-thumb-avatar d-flex align-items-center justify-content-center flex-shrink-0"
                             style="background:{{ $platformMeta['bg'] }};border-radius:10px;">
                            <i class="bi {{ $platformMeta['icon'] }} text-white" style="font-size:1rem;"></i>
                        </div>
                    @endif
                @elseif($thumb)
                    <img src="{{ asset($thumb) }}" class="admin-thumb-avatar" alt="Thumbnail" style="flex-shrink:0;">
                @else
                    <div class="admin-thumb-avatar d-flex align-items-center justify-content-center bg-teal-soft text-teal fw-bold font-serif"
                         style="font-size:1rem;flex-shrink:0;">
                        {{ strtoupper(substr($titleText,0,1)) }}
                    </div>
                @endif

                <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-ink" style="font-size:0.9rem;">{{ $titleText }}</div>
                    @if($isSocialLinks && $subText)
                        <small class="text-muted extra-small text-truncate d-block">{{ \Illuminate\Support\Str::limit($subText, 45) }}</small>
                        <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }} text-white"
                              style="font-size:0.6rem;">
                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    @elseif(isset($item->category))
                        <small class="text-teal extra-small fw-semibold text-uppercase">{{ $item->category }}</small>
                    @elseif(isset($item->room_number))
                        <small class="text-teal extra-small fw-semibold text-uppercase">Room {{ $item->room_number }}</small>
                    @endif
                </div>
                <span class="extra-small text-muted flex-shrink-0">#{{ $item->id }}</span>
            </div>

            <div class="admin-record-card-meta">
                <div class="admin-record-card-meta-item">
                    <i class="bi bi-clock"></i>
                    <span>{{ optional($item->updated_at)->format('d M Y, H:i A') ?? '—' }}</span>
                </div>
            </div>

            <div class="admin-record-card-actions">
                <a class="btn btn-sm btn-outline-secondary"
                   href="{{ route('admin.resources.edit',[$resource,$item->id]) }}">
                    <i class="bi bi-pencil-square me-1"></i>Edit
                </a>
                <button class="btn btn-sm btn-outline-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteConfirmModal"
                        data-action="{{ route('admin.resources.destroy',[$resource,$item->id]) }}">
                    <i class="bi bi-trash3-fill me-1"></i>Delete
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
            No records found for {{ $config['title'] }}.
        </div>
    @endforelse
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
