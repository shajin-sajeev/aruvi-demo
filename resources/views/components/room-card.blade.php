<div class="lux-card h-100 position-relative overflow-hidden card-hover-effect">
    <div class="position-relative overflow-hidden">
        <img class="room-image w-100"
             src="{{ $room->cover_image ?: 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=80' }}"
             alt="{{ $room->name }}">
        @php
            $roomBadgeText  = null;
            $roomBadgeClass = 'offer-badge--offer';
            if ($room->discount_price && $room->discount_price < $room->price_per_night) {
                $roomBadgeText  = 'Special Offer';
                $roomBadgeClass = 'offer-badge--offer';
            } elseif ($room->discount_price && $room->discount_price > $room->price_per_night) {
                $roomBadgeText  = 'Peak Season';
                $roomBadgeClass = 'offer-badge--peak';
            }
        @endphp
        @if($roomBadgeText)
            <div class="offer-badge {{ $roomBadgeClass }}">
                <i class="bi {{ $roomBadgeClass === 'offer-badge--peak' ? 'bi-calendar-event-fill' : 'bi-tag-fill' }}"></i>
                <span>{{ $roomBadgeText }}</span>
            </div>
        @endif
    </div>
    <div class="p-4">
        <div class="eyebrow-accent small mb-1">{{ $room->type?->name }}</div>
        <h4 class="fw-bold font-serif text-ink mb-2">{{ $room->name }}</h4>
        <p class="text-muted small mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
            {{ $room->short_description }}
        </p>
        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light">
            <div>
                @if($room->discount_price)
                    <small class="text-decoration-line-through text-muted d-block" style="font-size:0.8rem;">
                        &#8377;{{ number_format($room->price_per_night) }}
                    </small>
                    <strong class="text-teal">&#8377;{{ number_format($room->discount_price) }}<span class="text-muted fw-normal" style="font-size:0.75rem;">/night</span></strong>
                @else
                    <strong class="text-ink">&#8377;{{ number_format($room->price_per_night) }}<span class="text-muted fw-normal" style="font-size:0.75rem;">/night</span></strong>
                @endif
            </div>
            <a class="btn btn-sm btn-teal px-3" href="{{ route('rooms.show', $room) }}">
                <i class="bi bi-eye me-1"></i>View Details
            </a>
        </div>
    </div>
</div>
