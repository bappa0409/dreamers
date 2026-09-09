<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebsiteSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WebsiteSectionController extends Controller
{
    /**
     * All known landing page sections, in display order. Any section that
     * has not been saved to the database yet is returned with sensible
     * defaults so the admin UI always has something to edit.
     */
    protected array $defaults = [
        'hero' => ['sort_order' => 1, 'label' => 'Hero'],
        'stats' => ['sort_order' => 2, 'label' => 'Stats Bar'],
        'about' => ['sort_order' => 3, 'label' => 'About'],
        'activities' => ['sort_order' => 4, 'label' => 'Activities'],
        'transparency' => ['sort_order' => 5, 'label' => 'Transparency'],
        'faq' => ['sort_order' => 6, 'label' => 'FAQ'],
        'cta' => ['sort_order' => 7, 'label' => 'Call To Action'],
        'contact' => ['sort_order' => 8, 'label' => 'Contact'],
    ];

    public function index()
    {
        $sections = WebsiteSection::all()->keyBy('section_key');

        $data = collect($this->defaults)->map(function ($meta, $key) use ($sections) {
            $section = $sections->get($key);

            return [
                'id' => $section->id ?? null,
                'section_key' => $key,
                'label' => $meta['label'],
                'title' => $section->title ?? null,
                'subtitle' => $section->subtitle ?? null,
                'content' => $section->content ?? null,
                'image' => $section->image ?? null,
                'image_url' => ($section && $section->image)
                    ? Storage::disk('public')->url($section->image)
                    : null,
                'button_text' => $section->button_text ?? null,
                'button_url' => $section->button_url ?? null,
                'sort_order' => $section->sort_order ?? $meta['sort_order'],
                'is_active' => $section->is_active ?? true,
                'settings' => $section->settings ?? null,
            ];
        })->sortBy('sort_order')->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function update(Request $request, string $key)
    {
        if (!array_key_exists($key, $this->defaults)) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown landing page section.',
            ], 404);
        }

        $validated = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:5000',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'settings' => 'nullable|array',
        ])->validate();

        $section = WebsiteSection::firstOrNew(['section_key' => $key]);
        $section->section_key = $key;
        $section->title = $validated['title'] ?? null;
        $section->subtitle = $validated['subtitle'] ?? null;
        $section->content = $validated['content'] ?? null;
        $section->button_text = $validated['button_text'] ?? null;
        $section->button_url = $validated['button_url'] ?? null;
        $section->is_active = $request->boolean('is_active', true);
        $section->settings = $validated['settings'] ?? null;

        if (!$section->exists) {
            $section->sort_order = $this->defaults[$key]['sort_order'];
        }

        $section->save();

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully.',
            'data' => $section,
        ]);
    }

    public function uploadImage(Request $request, string $key)
    {
        if (!array_key_exists($key, $this->defaults)) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown landing page section.',
            ], 404);
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $section = WebsiteSection::firstOrNew(['section_key' => $key]);
        $oldPath = $section->image;

        $path = $request->file('file')->store('website-page', 'public');

        $section->section_key = $key;
        $section->image = $path;

        if (!$section->exists) {
            $section->sort_order = $this->defaults[$key]['sort_order'];
            $section->is_active = true;
        }

        $section->save();

        if ($oldPath && $oldPath !== $path && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'data' => [
                'image' => $section->image,
                'image_url' => Storage::disk('public')->url($section->image),
            ],
        ]);
    }
}
