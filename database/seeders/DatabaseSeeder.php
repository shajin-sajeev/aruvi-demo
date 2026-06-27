<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Attraction;
use App\Models\CmsPage;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\HeroSlide;
use App\Models\HomeSection;
use App\Models\Permission;
use App\Models\RestaurantCategory;
use App\Models\RestaurantItem;
use App\Models\Review;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * All admin sections with CRUD operations.
     * Format: 'resource-slug' => 'Human Name'
     */
    private const SECTIONS = [
        'dashboard'              => 'Dashboard',
        'theme-customization'    => 'Theme Customization',
        'hero-slides'            => 'Hero Slider',
        'home-sections'          => 'Homepage Layout',
        'social-links'           => 'Social Links',
        'room-types'             => 'Room Types',
        'rooms'                  => 'Rooms & Suites',
        'amenities'              => 'Amenities',
        'restaurant-categories'  => 'Restaurant Categories',
        'restaurant-items'       => 'Restaurant Items',
        'gallery-categories'     => 'Gallery Categories',
        'gallery-items'          => 'Gallery Items',
        'attractions'            => 'Attractions',
        'reviews'                => 'Reviews',
        'testimonials'           => 'Testimonials',
        'contact-messages'       => 'Contact Messages',
        'policies'               => 'Policies',
        'faqs'                   => 'FAQs',
        'cms-pages'              => 'CMS Pages',
        'settings'               => 'Site Settings',
        'users'                  => 'Users',
        'roles'                  => 'Roles',
        'permissions'            => 'Permissions',
        'approvals'              => 'User Approvals',
        'role-permissions'       => 'Role & Permission Manager',
    ];

    /** Sections that only have view + manage (no full CRUD) */
    private const VIEW_ONLY_SECTIONS = [
        'dashboard', 'approvals', 'role-permissions',
    ];

    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────────
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'is_system' => true]
        );
        Role::updateOrCreate(
            ['slug' => 'guest'],
            ['name' => 'Guest', 'is_system' => true]
        );

        // ── Permissions ────────────────────────────────────────────
        $allPermissions = [];

        foreach (self::SECTIONS as $resource => $label) {
            if (in_array($resource, self::VIEW_ONLY_SECTIONS, true)) {
                // Only view permission for non-CRUD sections
                $p = Permission::updateOrCreate(
                    ['slug' => "{$resource}.view"],
                    ['name' => "View {$label}"]
                );
                $allPermissions[] = $p->id;
            } else {
                foreach (['view', 'create', 'edit', 'delete'] as $action) {
                    $verb = match ($action) {
                        'view'   => 'View',
                        'create' => 'Create',
                        'edit'   => 'Edit',
                        'delete' => 'Delete',
                    };
                    $p = Permission::updateOrCreate(
                        ['slug' => "{$resource}.{$action}"],
                        ['name' => "{$verb} {$label}"]
                    );
                    $allPermissions[] = $p->id;
                }
            }
        }

        // Super Admin gets ALL permissions
        $superAdmin->permissions()->sync($allPermissions);

        // ── Super Admin user ───────────────────────────────────────
        User::updateOrCreate(['email' => 'admin@aruvi.resort'], [
            'role_id'     => $superAdmin->id,
            'name'        => 'Aruvi Super Admin',
            'phone'       => '+91 90000 00000',
            'password'    => Hash::make('1@AruviResort'),
            'status'      => 'active',
            'approved_at' => now(),
        ]);

        // ── Guest user ─────────────────────────────────────────────
        $guestRole = Role::where('slug', 'guest')->first();
        User::updateOrCreate(['email' => 'guest@aruvi.test'], [
            'role_id'  => $guestRole->id,
            'name'     => 'Sample Guest',
            'phone'    => '+91 98888 88888',
            'password' => Hash::make('password'),
            'status'   => 'active',
        ]);

        // ── Settings ───────────────────────────────────────────────
        foreach ([
            ['site', 'site_name',        'Aruvi on the Cliff', 'text'],
            ['site', 'site_logo',        '/images/default/logo.ico',  'file'],
            ['site', 'admin_logo',       '/images/default/logo.ico',  'file'],
            ['site', 'site_brand_image', '/images/default/brand.png', 'file'],
            ['site', 'about_image',      'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80', 'file'],
            ['site', 'dining_image',     'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1200&q=80', 'file'],
            ['site', 'contact_email',    'aruvionthecliff@gmail.com', 'email'],
            ['site', 'contact_phone',    '+91 9497473320', 'text'],
            ['seo',  'meta_title',       'Aruvi on the Cliff - Luxury Beachside Resort', 'text'],
            ['payments', 'razorpay_key',    '', 'text'],
            ['payments', 'stripe_key',      '', 'text'],
            ['payments', 'paypal_client_id','', 'text'],
            ['maps', 'google_maps_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5204481078726!2d76.7004967!3d8.7403549!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3b05efb512d38999%3A0x3521e5b9dfe724c8!2sAruvi%20Onthe%20Cliff!5e0!3m2!1sen!2sin!4v1718360000000!5m2!1sen!2sin', 'text'],
        ] as [$group, $key, $value, $type]) {
            Setting::updateOrCreate(['key' => $key], compact('group', 'value', 'type'));
        }

        // ── Hero Slides ────────────────────────────────────────────
        HeroSlide::upsert([
            ['id' => 1, 'eyebrow' => 'Luxury Beachside Resort', 'title' => 'Aruvi on the Cliff', 'subtitle' => 'Wake above turquoise water, dine with sea air, and surrender to an elegant coastal rhythm.', 'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80', 'button_label' => 'Reserve Your Stay', 'button_url' => '/booking', 'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'eyebrow' => 'Private Suites', 'title' => 'Rooms Open to the Horizon', 'subtitle' => 'Balconies, soft linens, refined service, and views designed for unhurried mornings.', 'image' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1800&q=80', 'button_label' => 'Explore Rooms', 'button_url' => '/rooms-suites', 'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['id']);

        HomeSection::updateOrCreate(['section_key' => 'about_preview'], [
            'title'     => 'Where sea breeze meets considered comfort',
            'body'      => 'Aruvi on the Cliff blends boutique luxury with the ease of beachside living: tranquil suites, warm service, curated dining, and coastal experiences.',
            'is_active' => true,
        ]);

        // ── Rooms ──────────────────────────────────────────────────
        $cottageType = RoomType::updateOrCreate(
            ['slug' => 'premium-cottages'],
            ['name' => 'Premium Cottage', 'description' => 'Private cliffside cottages with open verandas overlooking the ocean.', 'image' => 'https://images.unsplash.com/photo-1583037189850-1921ae7c6c22?auto=format&fit=crop&w=1200&q=80']
        );

        $amenities = collect([
            ['Infinity Pool',       'infinity-pool',       'https://images.unsplash.com/photo-1576013551627-0cc20b96c2a7?auto=format&fit=crop&w=120&h=120&q=80', 'Cliff-edge pool with uninterrupted ocean views.'],
            ['Sea Spa',             'sea-spa',             'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=120&h=120&q=80', 'Therapies inspired by mineral salts, warm stones, and coastal botanicals.'],
            ['Fine Dining',         'fine-dining',         'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=120&h=120&q=80', 'Chef-led seasonal menus rooted in local seafood and garden produce.'],
            ['Airport Transfer',    'airport-transfer',    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=120&h=120&q=80', 'Pre-arrival transfers and concierge travel assistance.'],
            ['High-Speed Wi-Fi',    'high-speed-wifi',     'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=120&h=120&q=80', 'Reliable connectivity across rooms and shared spaces.'],
            ['Private Beach Access','private-beach-access','https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=120&h=120&q=80', 'A quiet path to a sheltered stretch of shoreline.'],
        ])->map(fn ($a) => Amenity::updateOrCreate(['slug' => $a[1]], ['name' => $a[0], 'image' => $a[2], 'description' => $a[3], 'is_featured' => true, 'is_active' => true]));

        for ($i = 1; $i <= 10; $i++) {
            $name  = "Premium Cottage {$i}";
            $slug  = "premium-cottage-{$i}";
            $image = 'https://images.unsplash.com/photo-1583037189850-1921ae7c6c22?auto=format&fit=crop&w=1200&q=80';

            $room = Room::updateOrCreate(['slug' => $slug], [
                'room_type_id'    => $cottageType->id,
                'name'            => $name,
                'room_number'     => 'COT-' . sprintf('%02d', $i),
                'short_description' => 'A private beachside cottage with an open veranda overlooking the cliff edge.',
                'description'     => 'Experience absolute tranquility in our premium cliffside cottages.',
                'max_adults'      => 3,
                'max_children'    => 2,
                'size_sqft'       => 650,
                'price_per_night' => 14500,
                'cover_image'     => $image,
                'features'        => ['Private Deck', 'Sea view', 'Breakfast included', 'Hammock'],
                'is_featured'     => $i === 1,
                'is_active'       => true,
                'seo_title'       => "{$name} at Aruvi on the Cliff",
                'seo_description' => 'A private beachside cottage with an open veranda.',
            ]);
            $room->amenities()->sync($amenities->pluck('id')->all());
            RoomImage::updateOrCreate(['room_id' => $room->id, 'sort_order' => 1], ['image' => $image, 'alt_text' => "{$name} Exterior"]);
            RoomImage::updateOrCreate(['room_id' => $room->id, 'sort_order' => 2], ['image' => 'https://images.unsplash.com/photo-1590490359683-658d3d23f972?auto=format&fit=crop&w=1200&q=80', 'alt_text' => "{$name} Interior"]);
            RoomImage::updateOrCreate(['room_id' => $room->id, 'sort_order' => 3], ['image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80', 'alt_text' => "{$name} Ocean View"]);
        }

        // ── Dining ─────────────────────────────────────────────────
        $dining   = RestaurantCategory::updateOrCreate(['slug' => 'coastal-signatures'], ['name' => 'Coastal Signatures', 'sort_order' => 1]);
        $desserts = RestaurantCategory::updateOrCreate(['slug' => 'desserts-drinks'],    ['name' => 'Desserts & Drinks',   'sort_order' => 2]);

        foreach ([
            [$dining->id,   'Malabar Reef Curry',         'Fresh catch simmered with coconut, curry leaf, and toasted spice.', 1450, true,  'https://images.unsplash.com/photo-1626132647523-66f5bf380027?auto=format&fit=crop&w=600&q=80'],
            [$dining->id,   'Cliff Herb Risotto',          'Creamy arborio rice with garden herbs and parmesan.',              1180, false, 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?auto=format&fit=crop&w=600&q=80'],
            [$desserts->id, 'Tender Coconut Panna Cotta',  'Silky coconut custard with palm sugar caramel.',                   620,  true,  'https://images.unsplash.com/photo-1488477181946-6428a0291777?auto=format&fit=crop&w=600&q=80'],
        ] as [$cat, $name, $desc, $price, $sig, $img]) {
            RestaurantItem::updateOrCreate(
                ['restaurant_category_id' => $cat, 'name' => $name],
                ['description' => $desc, 'price' => $price, 'is_signature' => $sig, 'image' => $img, 'is_available' => true]
            );
        }

        // ── Gallery ────────────────────────────────────────────────
        $gallery = GalleryCategory::updateOrCreate(['slug' => 'resort'], ['name' => 'Resort']);
        foreach ([
            ['Cliff Pool',    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80'],
            ['Ocean Dining',  'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=1200&q=80'],
            ['Suite Balcony', 'https://images.unsplash.com/photo-1590490359683-658d3d23f972?auto=format&fit=crop&w=1200&q=80'],
        ] as [$title, $image]) {
            GalleryItem::updateOrCreate(['gallery_category_id' => $gallery->id, 'title' => $title], ['image' => $image, 'alt_text' => $title, 'is_featured' => true]);
        }

        // ── Attractions ────────────────────────────────────────────
        foreach ([
            ['Sunset Point',    'sunset-point',    'A quiet overlook for golden-hour photographs and evening walks.', '1.2 km'],
            ['Fisherman Cove',  'fisherman-cove',  'A working coastal hamlet with morning boats and local craft.',    '3.5 km'],
            ['Backwater Jetty', 'backwater-jetty', 'Private boat experiences through calm, palm-framed waterways.',  '12 km'],
        ] as [$name, $slug, $desc, $dist]) {
            Attraction::updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $desc, 'distance' => $dist, 'is_featured' => true]);
        }

        // ── Reviews & Testimonials ─────────────────────────────────
        foreach ([
            ['Mira Kapoor', 'Mumbai',    5, 'The suite felt private, polished, and completely connected to the sea.'],
            ['Arjun Menon', 'Bengaluru', 5, 'Dinner on the cliff deck was the best meal of our coastal trip.'],
            ['Leah Thomas', 'Kochi',     5, 'Everything was calm, attentive, and beautifully maintained.'],
        ] as [$name, $loc, $rating, $quote]) {
            Testimonial::updateOrCreate(['name' => $name], ['location' => $loc, 'rating' => $rating, 'quote' => $quote, 'is_featured' => true, 'is_active' => true]);
            Review::updateOrCreate(['name' => $name, 'title' => 'A beautiful stay'], ['rating' => $rating, 'comment' => $quote, 'is_approved' => true]);
        }

        // ── FAQs ───────────────────────────────────────────────────
        foreach ([
            ['Booking', 'Can I pay online?',                    'The platform is ready for Razorpay, Stripe, and PayPal keys.'],
            ['Stay',    'What are check-in and check-out times?','Standard check-in is 2:00 PM and check-out is 11:00 AM.'],
            ['Dining',  'Do you support dietary preferences?',   'Yes. Share preferences while booking or contact the concierge before arrival.'],
        ] as [$cat, $q, $a]) {
            Faq::updateOrCreate(['question' => $q], ['category' => $cat, 'answer' => $a, 'is_active' => true]);
        }

        // ── CMS Pages ──────────────────────────────────────────────
        foreach ([
            ['About Us',           'about-us',            '<p>Aruvi on the Cliff is a luxury beachside resort designed for travelers who want refined comfort without losing the character of the coast.</p>'],
            ['Terms & Conditions', 'terms-and-conditions','<p>Bookings are subject to room availability, guest verification, and resort operating policies.</p>'],
            ['Privacy Policy',     'privacy-policy',      '<p>Guest data is collected only for reservations, communication, compliance, and service personalization.</p>'],
            ['Cancellation Policy','cancellation-policy', '<p>Cancellation windows and refund rules can be edited from this CMS page.</p>'],
            ['Resort Policies',    'resort-policies',     '<p>Guests are requested to respect quiet hours, safety guidance, and occupancy limits.</p>'],
        ] as [$title, $slug, $content]) {
            CmsPage::updateOrCreate(['slug' => $slug], ['title' => $title, 'content' => $content, 'seo_title' => "{$title} - Aruvi on the Cliff", 'seo_description' => strip_tags($content), 'is_published' => true]);
        }

        // ── Social Links ───────────────────────────────────────────
        $socialLinks = [
            ['platform' => 'Facebook',  'url' => 'https://www.facebook.com/share/18o4HeYhbZ/'],
            ['platform' => 'Threads',   'url' => 'https://www.threads.com/@aruvi_onthecliff'],
            ['platform' => 'Twitter',   'url' => 'https://x.com/Aruviontheclirf'],
            ['platform' => 'Instagram', 'url' => 'https://www.instagram.com/aruvi_onthecliff?igsh=MTBzOHFyb2c1YnozdQ=='],
        ];

        foreach ($socialLinks as $link) {
            SocialLink::updateOrCreate(
                ['platform' => $link['platform']],
                ['url' => $link['url'], 'is_active' => true]
            );
        }
    }
}
