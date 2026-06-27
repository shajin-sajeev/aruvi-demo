<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ThemeCustomizationController extends Controller
{
    public function show()
    {
        abort_unless(auth()->user()->hasPermission('theme-customization.view'), 403);
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.customization', compact('settings'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('theme-customization.edit'), 403);
        try {
            $request->validate([
                'site_logo'        => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,ico|max:2048',
                'admin_logo'       => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,ico|max:2048',
                'site_brand_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'about_image'      => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:4096',
                'dining_image'     => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ], [
                'site_brand_image.image' => 'The navbar brand image must be a valid image file.',
                'site_brand_image.mimes' => 'The navbar brand image must be a file of type: jpeg, png, jpg, gif, svg, webp.',
                'site_brand_image.max' => 'The navbar brand image may not be greater than 2 MB.',
            ]);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['errors' => $e->errors(), 'message' => 'Validation failed.'], 422);
            }
            throw $e;
        }

        // Maps each setting key → uploads sub-folder
        $folderMap = [
            'site_logo'        => 'branding',
            'admin_logo'       => 'branding',
            'site_brand_image' => 'branding',
            'about_image'      => 'sections',
            'dining_image'     => 'sections',
        ];

        try {
            foreach ($folderMap as $key => $folder) {
                if ($request->hasFile($key)) {
                    $file     = $request->file($key);
                    $filename = time() . '_' . $key . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
                    $destDir  = public_path('uploads/' . $folder);

                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    $file->move($destDir, $filename);

                    Setting::updateOrCreate(
                        ['key' => $key],
                        ['value' => '/uploads/' . $folder . '/' . $filename, 'group' => 'site', 'type' => 'file']
                    );
                }
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Theme and Logo customization settings updated successfully.',
                ]);
            }

            return back()->with('success', 'Theme and Logo customization settings updated successfully.');
        } catch (\Throwable $e) {
            $message = 'Failed to save theme customization. ' . $e->getMessage();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 500);
            }

            return back()->with('error', $message);
        }
    }
}
