<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResourceController extends Controller
{
    public const RESOURCES = [
        'hero-slides' => ['model' => \App\Models\HeroSlide::class, 'title' => 'Hero Slider Management', 'fields' => ['eyebrow', 'title', 'subtitle:textarea', 'image:file', 'button_label', 'button_url', 'sort_order:number', 'is_active:checkbox']],
        'home-sections' => ['model' => \App\Models\HomeSection::class, 'title' => 'Homepage Management', 'fields' => ['section_key', 'title', 'body:textarea', 'is_active:checkbox']],
        'room-types' => ['model' => \App\Models\RoomType::class, 'title' => 'Room Type Management', 'fields' => ['name', 'slug', 'description:textarea', 'image:file']],
        'rooms' => ['model' => \App\Models\Room::class, 'title' => 'Rooms & Suites Management', 'fields' => ['room_type_id:number', 'name', 'slug', 'room_number', 'short_description:textarea', 'description:textarea', 'max_adults:number', 'max_children:number', 'size_sqft:number', 'cover_image:file', 'is_featured:checkbox', 'is_active:checkbox', 'seo_title', 'seo_description:textarea']],
        'amenities' => ['model' => \App\Models\Amenity::class, 'title' => 'Amenities Management', 'fields' => ['name', 'slug', 'image:file', 'description:textarea', 'is_featured:checkbox', 'is_active:checkbox']],
        'restaurant-categories' => ['model' => \App\Models\RestaurantCategory::class, 'title' => 'Restaurant Categories', 'fields' => ['name', 'slug', 'sort_order:number']],
        'restaurant-items' => ['model' => \App\Models\RestaurantItem::class, 'title' => 'Restaurant Menu Management', 'fields' => ['restaurant_category_id:number', 'name', 'description:textarea', 'price:number', 'image:file', 'is_signature:checkbox', 'is_available:checkbox', 'sort_order:number']],
        'gallery-categories' => ['model' => \App\Models\GalleryCategory::class, 'title' => 'Gallery Categories', 'fields' => ['name', 'slug']],
        'gallery-items' => ['model' => \App\Models\GalleryItem::class, 'title' => 'Gallery Management', 'fields' => ['gallery_category_id:number', 'title', 'image:file', 'alt_text', 'is_featured:checkbox', 'sort_order:number']],
        'attractions' => ['model' => \App\Models\Attraction::class, 'title' => 'Attractions Management', 'fields' => ['name', 'slug', 'description:textarea', 'image:file', 'distance', 'map_url', 'is_featured:checkbox']],
        'reviews' => ['model' => \App\Models\Review::class, 'title' => 'Reviews Management', 'fields' => ['name', 'email', 'rating:number', 'title', 'comment:textarea', 'is_approved:checkbox']],
        'testimonials' => ['model' => \App\Models\Testimonial::class, 'title' => 'Testimonials Management', 'fields' => ['name', 'location', 'rating:number', 'quote:textarea', 'image:file', 'is_featured:checkbox', 'is_active:checkbox']],
        'contact-messages' => ['model' => \App\Models\ContactMessage::class, 'title' => 'Contact Message Management', 'fields' => ['name', 'email', 'phone', 'subject', 'message:textarea', 'status']],
        'faqs' => ['model' => \App\Models\Faq::class, 'title' => 'FAQ Management', 'fields' => ['category', 'question', 'answer:textarea', 'sort_order:number', 'is_active:checkbox']],
        'cms-pages' => ['model' => \App\Models\CmsPage::class, 'title' => 'CMS Page Management', 'fields' => ['title', 'slug', 'content:textarea', 'seo_title', 'seo_description:textarea', 'is_published:checkbox']],
        'social-links' => ['model' => \App\Models\SocialLink::class, 'title' => 'Social Media Management', 'fields' => ['platform', 'url', 'is_active:checkbox']],
        'settings' => ['model' => \App\Models\Setting::class, 'title' => 'Site Settings / SEO Management', 'fields' => ['group', 'key', 'value:textarea', 'type']],
        'users' => ['model' => \App\Models\User::class, 'title' => 'User Management', 'fields' => ['role_id:number', 'name', 'email', 'phone', 'password', 'status']],
        'roles' => ['model' => \App\Models\Role::class, 'title' => 'Role Management', 'fields' => ['name', 'slug', 'is_system:checkbox']],
        'permissions' => ['model' => \App\Models\Permission::class, 'title' => 'Permission Management', 'fields' => ['name', 'slug']],
    ];

    public function index(string $resource)
    {
        abort_unless(auth()->user()->hasPermission("{$resource}.view"), 403);
        $config = $this->config($resource);

        $items = $config['model']::latest()->paginate(15);

        // Hero slides: pass current count so the view can show a limit warning
        $heroSlideCount = $resource === 'hero-slides' ? \App\Models\HeroSlide::count() : null;

        return view('admin.resources.index', [
            'resource'       => $resource,
            'config'         => $config,
            'items'          => $items,
            'heroSlideCount' => $heroSlideCount,
        ]);
    }

    public function create(string $resource)
    {
        abort_unless(auth()->user()->hasPermission("{$resource}.create"), 403);
        $config = $this->config($resource);
        $relations = $this->getRelations($config);

        // Hero slides: enforce max 5 limit
        if ($resource === 'hero-slides' && \App\Models\HeroSlide::count() >= 5) {
            return redirect()->route('admin.resources.index', $resource)
                ->with('error', 'Maximum of 5 hero slides allowed. Delete an existing slide before adding a new one.');
        }

        // Hero slides: collect already-used button_urls so the form can disable them
        $usedSlideUrls = $resource === 'hero-slides'
            ? \App\Models\HeroSlide::whereNotNull('button_url')->pluck('button_url')->filter()->values()->toArray()
            : [];

        return view('admin.resources.form', [
            'resource'      => $resource,
            'config'        => $config,
            'item'          => null,
            'relations'     => $relations,
            'usedSlideUrls' => $usedSlideUrls,
        ]);
    }

    public function store(Request $request, string $resource)
    {
        abort_unless(auth()->user()->hasPermission("{$resource}.create"), 403);
        $config = $this->config($resource);

        // Hero slides: enforce max 5 limit on store too
        if ($resource === 'hero-slides' && \App\Models\HeroSlide::count() >= 5) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Maximum of 5 hero slides allowed.'], 422);
            }
            return redirect()->route('admin.resources.index', $resource)
                ->with('error', 'Maximum of 5 hero slides allowed.');
        }

        try {
            $this->validateResource($request, $config);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors(), 'message' => 'Validation failed.'], 422);
            }
            throw $e;
        }

        $config['model']::create($this->payload($request, $config));

        if ($request->ajax()) {
            return response()->json(['redirect' => route('admin.resources.index', $resource)]);
        }

        return redirect()->route('admin.resources.index', $resource)->with('success', 'Content saved.');
    }

    public function edit(string $resource, int $id)
    {
        abort_unless(auth()->user()->hasPermission("{$resource}.edit"), 403);
        $config = $this->config($resource);
        $relations = $this->getRelations($config);

        $item = $config['model']::findOrFail($id);

        // Hero slides: collect already-used button_urls excluding the current item's own url
        $usedSlideUrls = $resource === 'hero-slides'
            ? \App\Models\HeroSlide::whereNotNull('button_url')
                ->where('id', '!=', $id)
                ->pluck('button_url')->filter()->values()->toArray()
            : [];

        return view('admin.resources.form', [
            'resource'      => $resource,
            'config'        => $config,
            'item'          => $item,
            'relations'     => $relations,
            'usedSlideUrls' => $usedSlideUrls,
        ]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        abort_unless(auth()->user()->hasPermission("{$resource}.edit"), 403);
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);

        try {
            $this->validateResource($request, $config, $item);
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors(), 'message' => 'Validation failed.'], 422);
            }
            throw $e;
        }

        $item->update($this->payload($request, $config, $item));

        if ($request->ajax()) {
            return response()->json(['redirect' => route('admin.resources.index', $resource)]);
        }

        return redirect()->route('admin.resources.index', $resource)->with('success', 'Content updated.');
    }

    public function destroy(string $resource, int $id)
    {
        abort_unless(auth()->user()->hasPermission("{$resource}.delete"), 403);
        $config = $this->config($resource);
        $config['model']::findOrFail($id)->delete();

        return back()->with('success', 'Content deleted.');
    }

    private function validateResource(Request $request, array $config, $item = null)
    {
        $rules = [];
        foreach ($config['fields'] as $field) {
            [$name, $type] = array_pad(explode(':', $field, 2), 2, 'text');
            
            $fieldRules = [];
            
            if ($type === 'checkbox') {
                $fieldRules[] = 'boolean';
            } elseif ($type === 'number') {
                if (in_array($name, ['discount_price'], true)) {
                    $fieldRules[] = 'nullable';
                } else {
                    $fieldRules[] = 'required';
                }
                $fieldRules[] = 'numeric';
            } elseif ($type === 'file') {
                $fieldRules[] = $item ? 'nullable' : 'required';
                $fieldRules[] = 'file';
                $fieldRules[] = 'mimes:jpeg,png,jpg,gif,webp';
                $fieldRules[] = 'max:10240';
            } else {
                if (in_array($name, ['slug', 'short_description', 'description', 'seo_title', 'seo_description', 'discount_price', 'special_requests', 'distance', 'map_url', 'phone', 'icon'], true)) {
                    $fieldRules[] = 'nullable';
                } else {
                    if ($name === 'password') {
                        $fieldRules[] = $item ? 'nullable' : 'required';
                    } else {
                        $fieldRules[] = 'required';
                    }
                }
                
                if ($name === 'email') {
                    $fieldRules[] = 'email';
                }
                
                $fieldRules[] = 'string';
            }

            // Unique rule for section_key in home-sections
            if ($name === 'section_key' && $request->route('resource') === 'home-sections') {
                $ignoreId = $item ? $item->id : 'NULL';
                $fieldRules[] = "unique:home_sections,section_key,{$ignoreId}";
            }
            
            $rules[$name] = $fieldRules;
        }

        if ($request->route('resource') === 'settings' && $item && $item->type === 'file') {
            // Allow standard images and .ico files for site logo/admin logo settings
            $rules['value'] = ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,svg,webp,ico', 'max:10240'];
        }

        return $request->validate($rules);
    }

    private function getRelations(array $config): array
    {
        $relations = [];
        foreach ($config['fields'] as $field) {
            [$name] = explode(':', $field, 2);
            if (str_ends_with($name, '_id')) {
                $relationName = substr($name, 0, -3);
                $modelName = str($relationName)->studly()->toString();
                $modelClass = "App\\Models\\" . $modelName;
                
                if (class_exists($modelClass)) {
                    $relations[$name] = $modelClass::all();
                }
            }
        }
        return $relations;
    }

    private function config(string $resource): array
    {
        abort_unless(isset(self::RESOURCES[$resource]), 404);

        return self::RESOURCES[$resource];
    }

    /**
     * Maps resource slug → upload sub-folder under public/uploads/
     */
    private const UPLOAD_FOLDERS = [
        'hero-slides'        => 'hero_slider',
        'room-types'         => 'rooms',
        'rooms'              => 'rooms',
        'amenities'          => 'amenities',
        'restaurant-items'   => 'restaurant',
        'gallery-items'      => 'gallery',
        'attractions'        => 'attractions',
        'testimonials'       => 'testimonials',
    ];

    private function payload(Request $request, array $config, $item = null): array
    {
        $payload = [];

        foreach ($config['fields'] as $field) {
            [$name, $type] = array_pad(explode(':', $field, 2), 2, 'text');
            if ($type === 'file') {
                if ($request->hasFile($name)) {
                    $file       = $request->file($name);
                    $filename   = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $resource   = $request->route('resource') ?? '';
                    $folder     = self::UPLOAD_FOLDERS[$resource] ?? 'uploads';
                    $destDir    = $folder === 'uploads'
                        ? public_path('uploads')
                        : public_path('uploads/' . $folder);

                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    $file->move($destDir, $filename);
                    $payload[$name] = ($folder === 'uploads' ? '/uploads/' : '/uploads/' . $folder . '/') . $filename;
                } else {
                    // Do not overwrite existing value if updating
                    if (!$item) {
                        $payload[$name] = null;
                    }
                }
            } else {
                $payload[$name] = $type === 'checkbox' ? $request->boolean($name) : $request->input($name);
            }
            if ($name === 'slug' && blank($payload[$name]) && $request->filled('name')) {
                $payload[$name] = Str::slug($request->input('name'));
            }
        }

        // Special handling for settings resource value (site logo file upload)
        if ($request->route('resource') === 'settings' && $item && $item->type === 'file') {
            if ($request->hasFile('value')) {
                $file     = $request->file('value');
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $destDir  = public_path('uploads/branding');

                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                $file->move($destDir, $filename);
                $payload['value'] = '/uploads/branding/' . $filename;
            } else {
                unset($payload['value']);
            }
        }

        if (Arr::has($payload, 'password')) {
            if (blank($payload['password'])) {
                unset($payload['password']);
            } else {
                $payload['password'] = Hash::make($payload['password']);
            }
        }

        return $payload;
    }
}
