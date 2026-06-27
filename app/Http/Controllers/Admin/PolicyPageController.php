<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;

class PolicyPageController extends Controller
{
    private static array $allowedPolicies = [
        'privacy-policy'       => 'Privacy Policy',
        'terms-and-conditions' => 'Terms & Conditions',
        'cancellation-policy'  => 'Cancellation Policy',
        'resort-policies'      => 'Resort Policies',
    ];

    public function index()
    {
        abort_unless(auth()->user()->hasPermission('policies.view'), 403);

        $pages = CmsPage::whereIn('slug', array_keys(self::$allowedPolicies))->get()->keyBy('slug');

        return view('admin.policies.index', [
            'pages'       => $pages,
            'policyNames' => self::$allowedPolicies,
        ]);
    }

    public function create(string $slug)
    {
        abort_unless(auth()->user()->hasPermission('policies.create'), 403);
        abort_unless(isset(self::$allowedPolicies[$slug]), 404);

        if (CmsPage::where('slug', $slug)->exists()) {
            return redirect()->route('admin.policies.edit', CmsPage::where('slug', $slug)->first());
        }

        return view('admin.policies.form', [
            'page'        => new CmsPage(['slug' => $slug, 'title' => self::$allowedPolicies[$slug]]),
            'policyNames' => self::$allowedPolicies,
            'editing'     => false,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('policies.create'), 403);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'slug'         => ['required', 'string', 'in:' . implode(',', array_keys(self::$allowedPolicies))],
            'content'      => ['required', 'string', 'max:100000'],
            'is_published' => ['boolean'],
        ]);

        $data['content']      = HtmlSanitizer::clean($data['content']);
        $data['is_published'] = $request->boolean('is_published');

        CmsPage::create($data);

        if ($request->ajax()) {
            return response()->json(['redirect' => route('admin.policies.index')]);
        }

        return redirect()->route('admin.policies.index')->with('success', 'Policy page created successfully.');
    }

    public function edit(CmsPage $page)
    {
        abort_unless(auth()->user()->hasPermission('policies.edit'), 403);
        abort_unless(array_key_exists($page->slug, self::$allowedPolicies), 404);

        return view('admin.policies.form', [
            'page'        => $page,
            'policyNames' => self::$allowedPolicies,
            'editing'     => true,
        ]);
    }

    public function update(Request $request, CmsPage $page)
    {
        abort_unless(auth()->user()->hasPermission('policies.edit'), 403);
        abort_unless(array_key_exists($page->slug, self::$allowedPolicies), 404);

        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string', 'max:100000'],
            'is_published' => ['boolean'],
        ]);

        $data['content']      = HtmlSanitizer::clean($data['content']);
        $data['is_published'] = $request->boolean('is_published');

        $page->update($data);

        if ($request->ajax()) {
            return response()->json(['redirect' => route('admin.policies.index')]);
        }

        return back()->with('success', 'Policy page updated successfully.');
    }

    public function destroy(CmsPage $page)
    {
        abort_unless(auth()->user()->hasPermission('policies.delete'), 403);
        abort_unless(array_key_exists($page->slug, self::$allowedPolicies), 404);

        $page->delete();

        return redirect()->route('admin.policies.index')->with('success', 'Policy page deleted successfully.');
    }
}
