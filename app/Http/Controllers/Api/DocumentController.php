<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List Documents
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Document::with('uploader:id,name')
            ->latest();

        if ($request->filled('document_type')) {
            $query->where(
                'document_type',
                $request->document_type
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere(
                        'document_type',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20)
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Upload Document
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',

            'document_type' =>
                'nullable|string|max:100',

            'description' =>
                'nullable|string',

            'file' =>
                'required|file|max:10240',

        ]);

        $file = $request->file('file');

        $path = $file->store(
            'documents',
            'public'
        );

        $document = Document::create([
            'title' =>
                $validated['title'],

            'document_type' =>
                $validated['document_type'] ?? null,

            'description' =>
                $validated['description'] ?? null,

            'file_path' =>
                $path,

            'file_name' =>
                $file->getClientOriginalName(),

            'file_extension' =>
                $file->getClientOriginalExtension(),

            'file_size' =>
                $file->getSize(),

            'uploaded_by' =>
                auth()->id(),

            'status' =>
                'active',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Document uploaded successfully.',

            'data' => $document
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Document
    |--------------------------------------------------------------------------
    */

    public function show(Document $document)
    {
        return response()->json([
            'success' => true,
            'data' => $document->load(
                'uploader:id,name'
            )
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Document
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Document $document
    ) {
        $validated = $request->validate([
            'title' =>
                'sometimes|required|string|max:255',

            'document_type' =>
                'nullable|string|max:100',

            'description' =>
                'nullable|string',

            'status' =>
                'sometimes|in:active,archived',
        ]);

        $document->update($validated);

        return response()->json([
            'success' => true,
            'message' =>
                'Document updated successfully.',

            'data' => $document
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Download Document
    |--------------------------------------------------------------------------
    */

    public function download(Document $document)
    {
        if (
            !Storage::disk('public')
                ->exists($document->file_path)
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'File not found.'
            ], 404);
        }

        return Storage::disk('public')
            ->download(
                $document->file_path,
                $document->file_name
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete Document
    |--------------------------------------------------------------------------
    */

    public function destroy(Document $document)
    {
        if (
            Storage::disk('public')
                ->exists($document->file_path)
        ) {
            Storage::disk('public')
                ->delete($document->file_path);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' =>
                'Document deleted successfully.'
        ]);
    }
}