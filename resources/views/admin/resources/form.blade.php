@extends('layouts.admin')
@section('title', $config['title'])
@section('content')

@php
/* ── Shared setup ──────────────────────────────────────── */
$currentPlatform  = strtolower(old('platform', $item?->platform ?? ''));
$currentButtonUrl = old('button_url', $item?->button_url ?? '');
$usedSlideUrls    = $usedSlideUrls ?? [];

$platformMeta = [
    'facebook'  => ['icon' => 'bi-facebook',  'color' => '#1877f2'],
    'instagram' => ['icon' => 'bi-instagram', 'color' => '#e1306c'],
    'twitter'   => ['icon' => 'bi-twitter-x', 'color' => '#000000'],
    'x'         => ['icon' => 'bi-twitter-x', 'color' => '#000000'],
    'youtube'   => ['icon' => 'bi-youtube',   'color' => '#ff0000'],
    'linkedin'  => ['icon' => 'bi-linkedin',  'color' => '#0a66c2'],
    'pinterest' => ['icon' => 'bi-pinterest', 'color' => '#e60023'],
    'tiktok'    => ['icon' => 'bi-tiktok',    'color' => '#010101'],
    'whatsapp'  => ['icon' => 'bi-whatsapp',  'color' => '#25d366'],
];
$currentMeta = $platformMeta[$currentPlatform] ?? ['icon' => 'bi-share', 'color' => '#6c757d'];

$heroSections = [
    ['label' => 'Rooms & Suites', 'anchor' => '#rooms',       'icon' => 'bi-door-open'],
    ['label' => 'Amenities',      'anchor' => '#amenities',   'icon' => 'bi-stars'],
    ['label' => 'Coastal Dining', 'anchor' => '#dining',      'icon' => 'bi-egg-fried'],
    ['label' => 'Gallery',        'anchor' => '#gallery',     'icon' => 'bi-images'],
    ['label' => 'Attractions',    'anchor' => '#attractions', 'icon' => 'bi-compass'],
    ['label' => 'Reviews',        'anchor' => '#reviews',     'icon' => 'bi-chat-left-heart'],
    ['label' => 'About',          'anchor' => '#about',       'icon' => 'bi-info-circle'],
    ['label' => 'FAQ',            'anchor' => '#faq',         'icon' => 'bi-question-circle'],
    ['label' => 'Contact',        'anchor' => '#contact',     'icon' => 'bi-envelope'],
];
@endphp

<div class="mb-3">
    <a href="{{ route('admin.resources.index', $resource) }}"
       class="btn btn-sm btn-link text-teal text-decoration-none fw-semibold p-0">
        <i class="bi bi-arrow-left me-1"></i> Back to {{ $config['title'] }}
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="form-card-header d-flex align-items-start align-items-sm-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold font-serif mb-1 text-white">
                <i class="bi {{ $item ? 'bi-pencil-square' : 'bi-plus-circle-fill' }} me-2 opacity-75"></i>
                {{ $item ? 'Modify Existing Entry' : 'Create New Entry' }}
            </h5>
            <small class="text-white opacity-70">All required fields will be validated on save.</small>
        </div>
        <span class="badge px-3 py-2 text-uppercase tracking-wider extra-small fw-bold">
            {{ $resource }}
        </span>
    </div>

    <form method="post"
          action="{{ $item ? route('admin.resources.update',[$resource,$item->id]) : route('admin.resources.store',$resource) }}"
          class="p-3 p-sm-4 p-md-5 bg-white"
          enctype="multipart/form-data">
        @csrf
        @if($item) @method('patch') @endif

        <div class="row g-3 g-md-4">
        @foreach($config['fields'] as $field)
            @php
            [$name, $type] = array_pad(explode(':', $field, 2), 2, 'text');
            $label  = str($name)->headline();
            $value  = old($name, $item?->{$name} ?? '');
            $typeIcon = match($type) {
                'file'     => 'bi-image',
                'number'   => 'bi-hash',
                'checkbox' => 'bi-toggle-on',
                'textarea' => 'bi-text-paragraph',
                default    => 'bi-input-cursor-text',
            };
            @endphp

            {{-- ═══════════════════════════════════════════════════
                 social-links → platform dropdown
            ═══════════════════════════════════════════════════ --}}
            @if($resource === 'social-links' && $name === 'platform')
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-share text-teal me-1"></i> Platform
                </label>
                <div class="d-flex align-items-center gap-3">
                    <div id="platform-icon-box"
                         class="d-flex align-items-center justify-content-center flex-shrink-0 rounded-3"
                         style="width:52px;height:52px;background:{{ $currentMeta['color'] }};transition:background .2s;">
                        <i id="platform-icon-el" class="bi {{ $currentMeta['icon'] }} text-white" style="font-size:1.4rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <select class="form-select" name="platform" id="platform-select" required>
                            <option value="">Select Platform…</option>
                            @foreach(['Facebook','Instagram','Twitter','X','YouTube','LinkedIn','Pinterest','TikTok','WhatsApp'] as $p)
                                <option value="{{ strtolower($p) }}" @selected($currentPlatform === strtolower($p))>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 home-sections → section_key readonly
            ═══════════════════════════════════════════════════ --}}
            @elseif($resource === 'home-sections' && $name === 'section_key')
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-key text-teal me-1"></i> Section Key
                </label>
                <input class="form-control bg-light text-muted" type="text" name="section_key"
                       value="{{ old('section_key', $item?->section_key ?? 'about_preview') }}"
                       readonly style="cursor:not-allowed;">
                <small class="text-muted extra-small d-block mt-1">
                    <i class="bi bi-lock-fill me-1"></i>Section key is fixed and cannot be changed.
                </small>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 settings → file value override
            ═══════════════════════════════════════════════════ --}}
            @elseif($resource === 'settings' && $item && $item->type === 'file' && $name === 'value')
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-image text-teal me-1"></i> Value (File)
                </label>
                <input class="form-control" type="file" name="value">
                @if($item->value)
                    <div class="mt-2 p-2 bg-light rounded-3 d-inline-block">
                        <small class="text-muted d-block mb-1"><i class="bi bi-eye-fill me-1"></i>Current:</small>
                        <img src="{{ asset($item->value) }}" alt="Preview" class="img-thumbnail" style="max-height:70px;">
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════════════════
                 hero-slides → button_url with section picker
            ═══════════════════════════════════════════════════ --}}
            @elseif($resource === 'hero-slides' && $name === 'button_url')
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-link-45deg text-teal me-1"></i> Button URL
                </label>
                <div class="d-flex flex-wrap gap-2 mb-2" id="section-chips">
                    @php
                    foreach ($heroSections as $sec) {
                        $isUsed    = in_array($sec['anchor'], $usedSlideUrls, true);
                        $isCurrent = ($currentButtonUrl === $sec['anchor']);
                        $btnClass  = $isCurrent ? 'btn-teal' : ($isUsed ? 'btn-secondary opacity-50' : 'btn-outline-secondary');
                        $disabled  = ($isUsed && !$isCurrent) ? 'disabled' : '';
                        $lockHtml  = ($isUsed && !$isCurrent) ? ' <i class="bi bi-lock-fill" style="font-size:.6rem;"></i>' : '';
                        $title     = ($isUsed && !$isCurrent) ? 'Already used by another slide' : 'Link to ' . $sec['label'];
                        echo '<button type="button" class="btn btn-sm section-chip ' . $btnClass . '"'
                            . ' data-url="' . $sec['anchor'] . '"'
                            . ' title="' . htmlspecialchars($title) . '"'
                            . ($disabled ? ' disabled' : '') . '>'
                            . '<i class="bi ' . $sec['icon'] . ' me-1"></i>'
                            . htmlspecialchars($sec['label']) . $lockHtml
                            . '</button> ';
                    }
                    @endphp
                </div>
                <input class="form-control" type="text" name="button_url" id="button_url_input"
                       value="{{ $currentButtonUrl }}"
                       placeholder="e.g. #rooms  or  https://…">
                <small class="text-muted extra-small d-block mt-1">
                    <i class="bi bi-info-circle me-1"></i>Click a section above or type a custom URL.
                </small>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 textarea
            ═══════════════════════════════════════════════════ --}}
            @elseif($type === 'textarea')
            <div class="col-12">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-text-paragraph text-teal me-1"></i> {{ $label }}
                </label>
                <textarea class="form-control" rows="5" name="{{ $name }}"
                          placeholder="Provide {{ $label }} content…">{{ $value }}</textarea>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 checkbox
            ═══════════════════════════════════════════════════ --}}
            @elseif($type === 'checkbox')
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-toggle-on text-teal me-1"></i> {{ $label }}
                </label>
                <div class="form-check form-switch p-3 border rounded-3 bg-light bg-opacity-50">
                    <input class="form-check-input ms-0 me-2" type="checkbox"
                           name="{{ $name }}" value="1"
                           id="switch-{{ $name }}" @checked(old($name, $item?->{$name}))>
                    <label class="form-check-label text-ink fw-semibold small" for="switch-{{ $name }}">Enabled</label>
                    <small class="text-muted d-block extra-small mt-1">Check to make this entry live on the website.</small>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 file upload
            ═══════════════════════════════════════════════════ --}}
            @elseif($type === 'file')
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi bi-image text-teal me-1"></i> {{ $label }}
                </label>
                <div class="border border-dashed p-3 rounded-3 text-center bg-light bg-opacity-50">
                    <i class="bi bi-cloud-arrow-up text-teal fs-4 d-block mb-2"></i>
                    <input class="form-control" type="file" name="{{ $name }}">
                    <small class="text-muted d-block mt-1 extra-small">Recommended: 1200×800px (JPG/PNG/WEBP)</small>
                    @if($item?->{$name})
                        <div class="mt-2">
                            <img src="{{ asset($item->{$name}) }}" alt="Preview"
                                 class="img-thumbnail shadow-sm" style="max-height:100px;object-fit:cover;">
                        </div>
                    @endif
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════
                 generic: select (relation) or text/number input
            ═══════════════════════════════════════════════════ --}}
            @else
            <div class="col-12 col-sm-6">
                <label class="form-label fw-bold small text-ink mb-2">
                    <i class="bi {{ $typeIcon }} text-teal me-1"></i> {{ $label }}
                </label>
                @php
                $isRelation = str_ends_with($name, '_id') && isset($relations[$name]);
                @endphp
                @if($isRelation)
                    <select class="form-select" name="{{ $name }}">
                        <option value="">Select {{ str(substr($name,0,-3))->headline() }}…</option>
                        @foreach($relations[$name] as $rel)
                            <option value="{{ $rel->id }}" @selected(old($name,$item?->{$name}) == $rel->id)>
                                {{ $rel->name ?? $rel->title ?? $rel->key ?? $rel->email ?? 'Option #'.$rel->id }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input class="form-control"
                           type="{{ $type === 'number' ? 'number' : 'text' }}"
                           step="0.01" name="{{ $name }}"
                           value="{{ $value }}"
                           placeholder="Enter {{ $label }}…">
                @endif
            </div>
            @endif

        @endforeach
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end mt-4 pt-4 border-top">
            <a href="{{ route('admin.resources.index',$resource) }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold">
                Cancel
            </a>
            <button class="btn btn-teal px-4 py-2 shadow-sm fw-bold">
                <i class="bi bi-check-circle-fill me-2"></i>Save &amp; Publish
            </button>
        </div>
    </form>
</div>

@if($resource === 'hero-slides')
<script>
(function () {
    var input = document.getElementById('button_url_input');
    var chips = document.querySelectorAll('.section-chip:not([disabled])');

    function activateChip(url) {
        chips.forEach(function (c) {
            var active = c.dataset.url === url;
            c.classList.toggle('btn-teal', active);
            c.classList.toggle('btn-outline-secondary', !active);
        });
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            if (input) input.value = this.dataset.url;
            activateChip(this.dataset.url);
        });
    });

    if (input) {
        input.addEventListener('input', function () { activateChip(this.value); });
        activateChip(input.value); // highlight on page load if value already set
    }
})();
</script>
@endif

@if($resource === 'social-links')
<script>
(function () {
    var meta = {
        facebook:  { icon: 'bi-facebook',  color: '#1877f2' },
        instagram: { icon: 'bi-instagram', color: '#e1306c' },
        twitter:   { icon: 'bi-twitter-x', color: '#000000' },
        x:         { icon: 'bi-twitter-x', color: '#000000' },
        youtube:   { icon: 'bi-youtube',   color: '#ff0000' },
        linkedin:  { icon: 'bi-linkedin',  color: '#0a66c2' },
        pinterest: { icon: 'bi-pinterest', color: '#e60023' },
        tiktok:    { icon: 'bi-tiktok',    color: '#010101' },
        whatsapp:  { icon: 'bi-whatsapp',  color: '#25d366' },
    };

    var select  = document.getElementById('platform-select');
    var iconBox = document.getElementById('platform-icon-box');
    var iconEl  = document.getElementById('platform-icon-el');

    function syncPlatform(val) {
        var m = meta[val] || { icon: 'bi-share', color: '#6c757d' };
        if (iconBox) iconBox.style.background = m.color;
        if (iconEl)  iconEl.className = 'bi ' + m.icon + ' text-white';
    }

    if (select) {
        select.addEventListener('change', function () { syncPlatform(this.value); });
        syncPlatform(select.value);
    }
})();
</script>
@endif

@endsection
