<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::with('creator')
            ->latest();

        // Public/member side only published notices
        if ($request->boolean('published_only')) {
            $query->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('publish_at')
                        ->orWhere('publish_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        return response()->json(
            $query->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',

            'content' => 'required|string',

            'type' => 'required|in:notice,announcement,event,urgent',

            'priority' => 'required|in:low,normal,high,urgent',

            'is_published' => 'sometimes|boolean',

            'publish_at' => 'nullable|date',

            'expires_at' => 'nullable|date|after:publish_at',

            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] =
                $request->file('attachment')
                    ->store('notices', 'public');
        }

        $validated['created_by'] = $request->user()->id;

        $notice = Notice::create($validated);

        return response()->json([
            'message' => 'Notice created successfully.',
            'notice' => $notice->load('creator'),
        ], 201);
    }

    public function show(Notice $notice)
    {
        return response()->json(
            $notice->load('creator')
        );
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',

            'content' => 'sometimes|required|string',

            'type' => 'sometimes|required|in:notice,announcement,event,urgent',

            'priority' => 'sometimes|required|in:low,normal,high,urgent',

            'is_published' => 'sometimes|boolean',

            'publish_at' => 'nullable|date',

            'expires_at' => 'nullable|date|after:publish_at',

            'attachment' => 'nullable|file|max:5120',
        ]);

        if ($request->hasFile('attachment')) {

            if ($notice->attachment) {
                Storage::disk('public')
                    ->delete($notice->attachment);
            }

            $validated['attachment'] =
                $request->file('attachment')
                    ->store('notices', 'public');
        }

        $notice->update($validated);

        return response()->json([
            'message' => 'Notice updated successfully.',
            'notice' => $notice->fresh()->load('creator'),
        ]);
    }

    public function destroy(Notice $notice)
    {
        if ($notice->attachment) {
            Storage::disk('public')
                ->delete($notice->attachment);
        }

        $notice->delete();

        return response()->json([
            'message' => 'Notice deleted successfully.',
        ]);
    }

    public function togglePublish(Notice $notice)
{
    $notice->update([
        'is_published' => !$notice->is_published,
    ]);

    return response()->json([
        'message' => $notice->is_published
            ? 'Notice published successfully.'
            : 'Notice unpublished successfully.',

        'notice' => $notice,
    ]);
}
}