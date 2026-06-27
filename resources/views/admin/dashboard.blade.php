@extends('layouts.admin')
@section('title', 'Dashboard Analytics')
@section('content')

{{-- ══════════════════════════════════════════════════════════
     PAGE HEADER
══════════════════════════════════════════════════════════ --}}
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
    <div>
        <h1 class="fw-bold text-ink mb-0" style="font-size:1.45rem;">Dashboard Analytics</h1>
        <p class="text-muted small mb-0">Resort content, guest activity &amp; engagement at a glance.</p>
    </div>
    <span class="badge bg-teal-soft text-teal px-3 py-2 small fw-semibold">
        <i class="bi bi-clock me-1"></i>{{ now()->format('d M Y, H:i') }}
    </span>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 1 — 8 STAT CARDS
══════════════════════════════════════════════════════════ --}}
<div class="row g-2 g-md-3 mb-3">
    @php
    $cards = [
        ['label'=>'Active Rooms',   'val'=>$roomsActive,     'sub'=>"of {$roomsTotal} total",    'icon'=>'bi-door-open-fill',      'grad'=>'linear-gradient(135deg,#073b3f,#008C95)'],
        ['label'=>'Gallery Items',  'val'=>$galleryTotal,    'sub'=>'media published',            'icon'=>'bi-images',              'grad'=>'linear-gradient(135deg,#094a50,#157d87)'],
        ['label'=>'Menu Items',     'val'=>$menuTotal,       'sub'=>'dishes available',           'icon'=>'bi-egg-fried',           'grad'=>'linear-gradient(135deg,#6c4b18,#b38612)'],
        ['label'=>'Reviews',        'val'=>$approvedReviews, 'sub'=>"of {$reviewTotal} total",    'icon'=>'bi-chat-left-heart-fill','grad'=>'linear-gradient(135deg,#0b2224,#2fbf9f)'],
        ['label'=>'New Messages',   'val'=>$messagesNew,     'sub'=>'awaiting reply',             'icon'=>'bi-envelope-paper-fill', 'grad'=>'linear-gradient(135deg,#6b2e1e,#d45c43)'],
        ['label'=>'Active FAQs',    'val'=>$faqTotal,        'sub'=>'help entries',               'icon'=>'bi-question-circle-fill','grad'=>'linear-gradient(135deg,#2d4a1e,#5a9e3a)'],
        ['label'=>'Attractions',    'val'=>$attractionsTotal,'sub'=>'nearby spots listed',        'icon'=>'bi-compass-fill',        'grad'=>'linear-gradient(135deg,#3a1c71,#7f5a9a)'],
        ['label'=>'Subscribers',    'val'=>$newsletterSubs,  'sub'=>'newsletter list',            'icon'=>'bi-envelope-check-fill', 'grad'=>'linear-gradient(135deg,#073b3f,#00737b)'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="col-6 col-sm-4 col-md-3">
        <div class="d-flex align-items-center gap-3 rounded-3 p-3 text-white position-relative overflow-hidden h-100"
             style="background:{{ $c['grad'] }};min-height:76px;">
            <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                 style="width:44px;height:44px;background:rgba(255,255,255,.16);">
                <i class="bi {{ $c['icon'] }}" style="font-size:1.25rem;"></i>
            </div>
            <div class="min-w-0">
                <div class="fw-bold lh-1 mb-1" style="font-size:1.55rem;letter-spacing:-.02em;">{{ $c['val'] }}</div>
                <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;opacity:.9;">{{ $c['label'] }}</div>
                <div style="font-size:.65rem;opacity:.65;">{{ $c['sub'] }}</div>
            </div>
            <div style="position:absolute;right:-8px;bottom:-8px;width:56px;height:56px;background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;"></div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 2 — MESSAGES TREND (line) + RATING DONUT
══════════════════════════════════════════════════════════ --}}
<div class="row g-2 g-md-3 mb-3">

    {{-- Messages over 6 months --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-ink mb-0"><i class="bi bi-bar-chart-line text-teal me-2"></i>Contact Messages — Last 6 Months</h6>
                    <small class="text-muted">Monthly enquiry volume trend</small>
                </div>
                <span class="badge bg-teal-soft text-teal">{{ array_sum(array_values($msgMonthlyLabelled ?? [])) }} total</span>
            </div>
            <div class="card-body px-3 pb-3 pt-2">
                <canvas id="msgChart" style="max-height:210px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Rating distribution donut --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold text-ink mb-0"><i class="bi bi-star-half text-teal me-2"></i>Review Rating Breakdown</h6>
                <small class="text-muted">Approved reviews by star rating</small>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center gap-3 px-3 pb-3 pt-2 flex-wrap">
                <canvas id="ratingChart" style="max-height:200px;max-width:200px;"></canvas>
                <div id="rating-legend" class="d-flex flex-column gap-1"></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 3 — GALLERY BAR + MENU PIE
══════════════════════════════════════════════════════════ --}}
<div class="row g-2 g-md-3 mb-3">

    {{-- Gallery items per category --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold text-ink mb-0"><i class="bi bi-camera text-teal me-2"></i>Gallery by Category</h6>
                <small class="text-muted">Number of images per gallery category</small>
            </div>
            <div class="card-body px-3 pb-3 pt-2">
                <canvas id="galleryChart" style="max-height:200px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Menu items per category --}}
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4">
                <h6 class="fw-bold text-ink mb-0"><i class="bi bi-pie-chart text-teal me-2"></i>Menu by Category</h6>
                <small class="text-muted">Dish count per menu category</small>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center px-3 pb-3 pt-2">
                <canvas id="menuChart" style="max-height:200px;max-width:240px;"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 4 — RECENT MESSAGES + RECENT REVIEWS
══════════════════════════════════════════════════════════ --}}
<div class="row g-2 g-md-3 mb-3">

    {{-- Recent Messages --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-ink mb-0"><i class="bi bi-inbox text-teal me-2"></i>Recent Enquiries</h6>
                    <small class="text-muted">Latest contact messages</small>
                </div>
                <a href="{{ route('admin.messages.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="font-size:.75rem;">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($recentMessages as $msg)
                    <div class="d-flex align-items-start gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle fw-bold text-white"
                             style="width:38px;height:38px;font-size:.85rem;background:{{ $msg->status === 'new' ? '#d45c43' : '#008C95' }};">
                            {{ strtoupper(substr($msg->name,0,1)) }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="fw-semibold text-ink small text-truncate">{{ $msg->name }}</span>
                                <span class="badge {{ $msg->status === 'new' ? 'bg-danger' : 'bg-secondary' }} text-white flex-shrink-0" style="font-size:.6rem;">{{ $msg->status }}</span>
                            </div>
                            <div class="text-muted small text-truncate">{{ $msg->subject ?: 'No subject' }}</div>
                            <div class="extra-small text-muted">{{ $msg->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>No messages yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Reviews --}}
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-0 pt-3 pb-0 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-ink mb-0"><i class="bi bi-star text-teal me-2"></i>Recent Reviews</h6>
                    <small class="text-muted">Latest approved guest feedback</small>
                </div>
                <a href="{{ route('admin.resources.index','reviews') }}" class="btn btn-sm btn-outline-secondary px-3" style="font-size:.75rem;">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($recentReviews as $rev)
                    <div class="d-flex align-items-start gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom border-light' : '' }}">
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle bg-teal-soft text-teal fw-bold font-serif"
                             style="width:38px;height:38px;font-size:.85rem;">
                            {{ strtoupper(substr($rev->name,0,1)) }}
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="fw-semibold text-ink small text-truncate">{{ $rev->name }}</span>
                                <span class="text-warning flex-shrink-0" style="font-size:.75rem;">
                                    @for($i=1;$i<=5;$i++)
                                        <i class="bi bi-star{{ $i <= $rev->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </span>
                            </div>
                            <div class="text-muted small text-truncate">{{ $rev->title ?: $rev->comment }}</div>
                            <div class="extra-small text-muted">{{ $rev->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-chat-left-heart fs-3 d-block mb-2 opacity-50"></i>No reviews yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     ROW 5 — QUICK NAV SHORTCUTS
══════════════════════════════════════════════════════════ --}}
<div class="row g-2 g-md-3 mb-1">
    @php
    $shortcuts = [
        ['label'=>'Hero Slides',    'route'=>'admin.resources.index', 'param'=>'hero-slides',          'icon'=>'bi-images',              'color'=>'#008C95'],
        ['label'=>'Rooms & Suites', 'route'=>'admin.resources.index', 'param'=>'rooms',                'icon'=>'bi-door-open',           'color'=>'#073b3f'],
        ['label'=>'Menu Items',     'route'=>'admin.resources.index', 'param'=>'restaurant-items',     'icon'=>'bi-egg-fried',           'color'=>'#b38612'],
        ['label'=>'Gallery',        'route'=>'admin.resources.index', 'param'=>'gallery-items',        'icon'=>'bi-image',               'color'=>'#157d87'],
        ['label'=>'Attractions',    'route'=>'admin.resources.index', 'param'=>'attractions',          'icon'=>'bi-compass',             'color'=>'#7f5a9a'],
        ['label'=>'FAQs',           'route'=>'admin.resources.index', 'param'=>'faqs',                 'icon'=>'bi-question-circle',     'color'=>'#5a9e3a'],
        ['label'=>'Policies',       'route'=>'admin.policies.index',  'param'=>null,                   'icon'=>'bi-file-earmark-text',   'color'=>'#d45c43'],
        ['label'=>'Messages',       'route'=>'admin.messages.index',  'param'=>null,                   'icon'=>'bi-envelope',            'color'=>'#6b2e1e'],
    ];
    @endphp
    @foreach($shortcuts as $s)
    <div class="col-6 col-sm-4 col-md-3">
        <a href="{{ $s['param'] ? route($s['route'], $s['param']) : route($s['route']) }}"
           class="d-flex align-items-center gap-2 bg-white border rounded-3 px-3 py-2 text-decoration-none card-hover-effect shadow-sm">
            <span class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0"
                  style="width:34px;height:34px;background:{{ $s['color'] }}18;">
                <i class="bi {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:1rem;"></i>
            </span>
            <span class="fw-semibold text-ink small">{{ $s['label'] }}</span>
            <i class="bi bi-chevron-right ms-auto text-muted" style="font-size:.65rem;"></i>
        </a>
    </div>
    @endforeach
</div>


{{-- ══════════════════════════════════════════════════════════
     CHART.JS — all four charts
══════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // ── Shared palette ─────────────────────────────────────────────
    const teal    = '#008C95';
    const colors  = ['#008C95','#157d87','#2fbf9f','#b38612','#d45c43','#7f5a9a','#5a9e3a','#073b3f'];
    const ratings = {5:'#008C95', 4:'#2fbf9f', 3:'#b38612', 2:'#d45c43', 1:'#9b2335'};

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size   = 12;

    // ── 1. Messages Line Chart ──────────────────────────────────────
    const msgData  = @json(array_values($msgMonthlyLabelled ?? []));
    const msgLabels= @json(array_keys($msgMonthlyLabelled ?? []));

    // Fill in missing months so the chart always shows 6 points
    const fullLabels = [];
    const fullData   = [];
    const labelMap   = {};
    msgLabels.forEach((l, i) => labelMap[l] = msgData[i]);
    for (let m = 5; m >= 0; m--) {
        const d = new Date();
        d.setDate(1);
        d.setMonth(d.getMonth() - m);
        const label = d.toLocaleString('en-GB', {month:'short', year:'numeric'});
        fullLabels.push(label);
        fullData.push(labelMap[label] ?? 0);
    }

    const msgCtx = document.getElementById('msgChart');
    if (msgCtx) {
        new Chart(msgCtx, {
            type: 'line',
            data: {
                labels: fullLabels,
                datasets: [{
                    label: 'Messages',
                    data: fullData,
                    borderColor: teal,
                    backgroundColor: 'rgba(0,140,149,.08)',
                    pointBackgroundColor: teal,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.38,
                    borderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#6b8a8c' },
                        grid: { color: 'rgba(0,0,0,.05)' },
                    },
                    x: { ticks: { color: '#6b8a8c' }, grid: { display: false } }
                }
            }
        });
    }

    // ── 2. Rating Donut ─────────────────────────────────────────────
    const ratingRaw  = @json($ratingDist ?? []);
    const rLabels    = [5,4,3,2,1].map(n => n + ' Star' + (n !== 1 ? 's' : ''));
    const rData      = [5,4,3,2,1].map(n => ratingRaw[n] ?? 0);
    const rColors    = [5,4,3,2,1].map(n => ratings[n]);

    const rCtx = document.getElementById('ratingChart');
    if (rCtx && rData.some(v => v > 0)) {
        new Chart(rCtx, {
            type: 'doughnut',
            data: { labels: rLabels, datasets: [{ data: rData, backgroundColor: rColors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed} reviews` } }
                }
            }
        });
        // Custom legend
        const leg = document.getElementById('rating-legend');
        if (leg) {
            [5,4,3,2,1].forEach((n, i) => {
                if (!rData[i]) return;
                const el = document.createElement('div');
                el.style.cssText = 'display:flex;align-items:center;gap:6px;font-size:.78rem;color:#4a5d5e;';
                el.innerHTML = `<span style="width:10px;height:10px;border-radius:50%;background:${rColors[i]};flex-shrink:0;"></span>`
                             + `<span>${rLabels[i]}</span><strong style="margin-left:auto;padding-left:12px;">${rData[i]}</strong>`;
                leg.appendChild(el);
            });
        }
    } else if (rCtx) {
        rCtx.parentElement.innerHTML += '<p class="text-muted small text-center mt-2">No approved reviews yet.</p>';
    }

    // ── 3. Gallery Bar Chart ────────────────────────────────────────
    const gCats   = @json(array_keys($galleryCats ?? []));
    const gCounts = @json(array_values($galleryCats ?? []));

    const gCtx = document.getElementById('galleryChart');
    if (gCtx) {
        new Chart(gCtx, {
            type: 'bar',
            data: {
                labels: gCats,
                datasets: [{
                    label: 'Images',
                    data: gCounts,
                    backgroundColor: gCounts.map((_, i) => colors[i % colors.length] + 'cc'),
                    borderColor: gCounts.map((_, i) => colors[i % colors.length]),
                    borderWidth: 1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color:'#6b8a8c' }, grid: { color:'rgba(0,0,0,.05)' } },
                    x: { ticks: { color:'#6b8a8c' }, grid: { display: false } }
                }
            }
        });
    }

    // ── 4. Menu Pie Chart ───────────────────────────────────────────
    const mCats   = @json(array_keys($menuCats ?? []));
    const mCounts = @json(array_values($menuCats ?? []));

    const mCtx = document.getElementById('menuChart');
    if (mCtx && mCounts.length) {
        new Chart(mCtx, {
            type: 'pie',
            data: {
                labels: mCats,
                datasets: [{
                    data: mCounts,
                    backgroundColor: mCounts.map((_, i) => colors[i % colors.length]),
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, padding: 10, color:'#4a5d5e', font:{ size: 11 } } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed} items` } }
                }
            }
        });
    }
})();
</script>

@endsection
