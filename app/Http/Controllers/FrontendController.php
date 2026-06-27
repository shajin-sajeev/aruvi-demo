<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Attraction;
use App\Models\CmsPage;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\HeroSlide;
use App\Models\HomeSection;
use App\Models\Newsletter;
use App\Models\RestaurantCategory;
use App\Models\Review;
use App\Models\Room;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function home()
    {
        return view('frontend.home', [
            'slides'           => HeroSlide::where('is_active', true)->orderBy('sort_order')->get(),
            'sections'         => HomeSection::where('is_active', true)->get()->keyBy('section_key'),
            'rooms'            => Room::with('type', 'amenities', 'images')->where('is_active', true)->where('is_featured', true)->latest()->get(),
            'amenities'        => Amenity::where('is_active', true)->orderByDesc('is_featured')->orderBy('name')->get(),
            'testimonials'     => Testimonial::where('is_active', true)->where('is_featured', true)->take(6)->get(),
            'menuCategories'   => RestaurantCategory::with(['items' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order')])->orderBy('sort_order')->get(),
            'galleryCategories'=> GalleryCategory::with('items')->get(),
            'attractions'      => Attraction::latest()->get(),
            'reviews'          => Review::where('is_approved', true)->latest()->take(6)->get(),
            'faqs'             => Faq::where('is_active', true)->orderBy('category')->orderBy('sort_order')->get()->groupBy('category'),
            'policies'         => CmsPage::whereIn('slug', ['privacy-policy', 'terms-and-conditions', 'cancellation-policy', 'resort-policies'])->where('is_published', true)->get()->keyBy('slug'),
            'socialLinks'      => SocialLink::where('is_active', true)->get(),
            'settings'         => Setting::all()->pluck('value', 'key'),
        ]);
    }

    public function rooms()
    {
        return view('frontend.rooms.index', [
            'rooms' => Room::with('type', 'amenities')->where('is_active', true)->latest()->paginate(9),
        ]);
    }

    public function room(Room $room)
    {
        abort_unless($room->is_active, 404);

        return view('frontend.rooms.show', [
            'room'         => $room->load('type', 'images', 'amenities'),
            'relatedRooms' => Room::where('is_active', true)->whereKeyNot($room->id)->take(3)->get(),
        ]);
    }

    public function about()
    {
        return $this->cms('about-us', 'frontend.page', ['fallbackTitle' => 'About Aruvi on the Cliff']);
    }

    public function amenities()
    {
        return view('frontend.amenities', [
            'amenities' => Amenity::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function restaurant()
    {
        return view('frontend.restaurant', [
            'categories' => RestaurantCategory::with(['items' => fn ($q) => $q->where('is_available', true)])->orderBy('sort_order')->get(),
        ]);
    }

    public function gallery()
    {
        return view('frontend.gallery', [
            'categories' => GalleryCategory::with('items')->get(),
        ]);
    }

    public function location()
    {
        return view('frontend.location', [
            'attractions' => Attraction::latest()->get(),
        ]);
    }

    public function reviews()
    {
        return view('frontend.reviews', [
            'reviews' => Review::where('is_approved', true)->latest()->paginate(10),
        ]);
    }

    public function storeReview(Request $request)
    {
        Review::create($request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'title'   => ['nullable', 'string', 'max:255'],
            'comment' => ['required', 'string', 'max:5000'],
        ]) + ['user_id' => $request->user()?->id, 'is_approved' => false]); // Admin must approve

        return back()->with('success', 'Thank you. Your review has been submitted and is pending approval.');
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function storeContact(Request $request)
    {
        ContactMessage::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]));

        return back()->with('success', 'Your message has reached our reservations desk.');
    }

    public function faq()
    {
        return view('frontend.faq', ['faqs' => Faq::where('is_active', true)->orderBy('category')->orderBy('sort_order')->get()->groupBy('category')]);
    }

    public function subscribe(Request $request)
    {
        Newsletter::updateOrCreate(
            ['email' => $request->validate(['email' => ['required', 'email', 'max:255']])['email']],
            ['is_active' => true, 'subscribed_at' => now()]
        );

        return back()->with('success', 'You are on the Aruvi insider list.');
    }

    public function policy(string $slug)
    {
        abort_unless(in_array($slug, ['terms-and-conditions', 'privacy-policy', 'cancellation-policy', 'resort-policies'], true), 404);

        return $this->cms($slug, 'frontend.page');
    }

    private function cms(string $slug, string $view, array $extra = [])
    {
        $page = CmsPage::where('slug', $slug)->where('is_published', true)->first();

        return view($view, $extra + ['page' => $page, 'title' => $page?->title ?? ($extra['fallbackTitle'] ?? str($slug)->headline())]);
    }
}
