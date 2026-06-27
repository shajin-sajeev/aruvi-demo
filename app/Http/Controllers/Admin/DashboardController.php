<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Attraction;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\Newsletter;
use App\Models\RestaurantCategory;
use App\Models\RestaurantItem;
use App\Models\Review;
use App\Models\Room;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // ── Content counts ─────────────────────────────────────────
        $galleryTotal   = \App\Models\GalleryItem::count();
        $menuTotal      = \App\Models\RestaurantItem::count();
        $reviewTotal    = \App\Models\Review::count();
        $approvedReviews= \App\Models\Review::where('is_approved', true)->count();
        $faqTotal       = \App\Models\Faq::where('is_active', true)->count();
        $messagesNew    = \App\Models\ContactMessage::where('status', 'new')->count();
        $messagesRead   = \App\Models\ContactMessage::where('status', 'read')->count();
        $attractionsTotal = \App\Models\Attraction::count();
        $roomsTotal     = \App\Models\Room::count();
        $roomsActive    = \App\Models\Room::where('is_active', true)->count();
        $amenitiesTotal = \App\Models\Amenity::count();
        $slidesActive   = \App\Models\HeroSlide::where('is_active', true)->count();
        $newsletterSubs = \App\Models\Newsletter::where('is_active', true)->count();

        // ── Rating distribution for donut chart ───────────────────
        $ratingDist = \App\Models\Review::where('is_approved', true)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        // ── Gallery items per category (bar chart) ─────────────────
        $galleryCats = \App\Models\GalleryCategory::withCount('items')
            ->orderByDesc('items_count')
            ->get()
            ->pluck('items_count', 'name')
            ->toArray();

        // ── Menu items per category ────────────────────────────────
        $menuCats = \App\Models\RestaurantCategory::withCount('items')
            ->orderByDesc('items_count')
            ->get()
            ->pluck('items_count', 'name')
            ->toArray();

        // ── Messages last 6 months (line chart) ───────────────────
        $msgMonthly = \App\Models\ContactMessage::selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total"
            )
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->pluck('total', 'ym')
            ->toArray();

        // Re-key as "Jan 2026" labels for the chart
        $msgMonthlyLabelled = [];
        foreach ($msgMonthly as $ym => $total) {
            $label = \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('M Y');
            $msgMonthlyLabelled[$label] = $total;
        }

        // ── Recent messages (inbox preview) ───────────────────────
        $recentMessages = \App\Models\ContactMessage::latest()->take(5)->get();

        // ── Recent reviews ─────────────────────────────────────────
        $recentReviews = \App\Models\Review::where('is_approved', true)
            ->latest()->take(4)->get();

        return view('admin.dashboard', compact(
            'galleryTotal', 'menuTotal', 'reviewTotal', 'approvedReviews',
            'faqTotal', 'messagesNew', 'messagesRead', 'attractionsTotal',
            'roomsTotal', 'roomsActive', 'amenitiesTotal', 'slidesActive',
            'newsletterSubs', 'ratingDist', 'galleryCats', 'menuCats',
            'msgMonthlyLabelled', 'recentMessages', 'recentReviews'
        ));
    }
}
