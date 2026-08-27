<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'category'=>'nullable|string|max:100',
            'extension'=>'nullable|string|max:20',
            'visibility'=>'nullable|in:public,members,internal,private',
            'active'=>'nullable|boolean',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=Document::query()
            ->with('uploader:id,name,email')
            ->visibleTo($request->user())
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhere('original_name','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhere('category','like',"%{$search}%");
            });
        }

        if(!empty($validated['category'])){
            $query->where('category',$validated['category']);
        }

        if(!empty($validated['extension'])){
            $query->where('extension',$validated['extension']);
        }

        if(!empty($validated['visibility'])){
            $query->where('visibility',$validated['visibility']);
        }

        if(array_key_exists('active',$validated)){
            $query->where('is_active',(bool)$validated['active']);
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min((int)($validated['per_page']??15),100)
            )
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'title'=>'required|string|max:255',
            'category'=>'nullable|string|max:100',
            'description'=>'nullable|string|max:3000',
            'visibility'=>'required|in:public,members,internal,private',
            'is_active'=>'nullable|boolean',
            'file'=>'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,zip'
        ]);

        $document=$this->documentService->create(
            $validated,
            $request->file('file'),
            auth()->id()
        );

        return response()->json([
            'success'=>true,
            'message'=>'Document uploaded successfully.',
            'data'=>$document->load('uploader:id,name,email')
        ],201);
    }

    public function show(Request $request,Document $document)
    {
        abort_unless(
            $this->canAccess($request,$document),
            403
        );

        return response()->json([
            'success'=>true,
            'data'=>$document->load('uploader:id,name,email')
        ]);
    }

    public function update(Request $request,Document $document)
    {
        $validated=$request->validate([
            'title'=>'sometimes|required|string|max:255',
            'category'=>'nullable|string|max:100',
            'description'=>'nullable|string|max:3000',
            'visibility'=>'sometimes|required|in:public,members,internal,private',
            'is_active'=>'sometimes|boolean',
            'file'=>'nullable|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,jpg,jpeg,png,webp,zip'
        ]);

        $file=$request->file('file');
        unset($validated['file']);

        $document=$file
            ?$this->documentService->updateWithFile(
                $document,
                $validated,
                $file
            )
            :$this->documentService->update(
                $document,
                $validated
            );

        return response()->json([
            'success'=>true,
            'message'=>'Document updated successfully.',
            'data'=>$document->load('uploader:id,name,email')
        ]);
    }

    public function download(Request $request,Document $document)
    {
        abort_unless(
            $this->canAccess($request,$document),
            403
        );

        return $this->documentService->download($document);
    }

    public function destroy(Document $document)
    {
        $this->documentService->delete($document);

        return response()->json([
            'success'=>true,
            'message'=>'Document deleted successfully.'
        ]);
    }

    public function filters()
    {
        return response()->json([
            'success'=>true,
            'data'=>Cache::remember(
                'documents:filters',
                now()->addMinutes(30),
                fn()=>[
                    'categories'=>Document::query()
                        ->whereNotNull('category')
                        ->where('category','!=','')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category'),

                    'extensions'=>Document::query()
                        ->whereNotNull('extension')
                        ->distinct()
                        ->orderBy('extension')
                        ->pluck('extension')
                ]
            )
        ]);
    }

    protected function canAccess(
        Request $request,
        Document $document
    ): bool{
        $user=$request->user();

        if(
            $user->isSystemAnalyst() ||
            $user->hasPermission('Document.manage')
        ){
            return true;
        }

        if(!$document->is_active){
            return false;
        }

        return in_array(
            $document->visibility,
            ['public','members'],
            true
        );
    }

    public function preview(Request $request,Document $document)
{
    abort_unless(
        $this->canAccess($request,$document),
        403
    );

    abort_unless(
        $document->path&&
        Storage::disk($document->disk)->exists($document->path),
        404,
        'Document file not found.'
    );

    abort_unless(
        str_starts_with(
            strtolower((string)$document->mime_type),
            'image/'
        ),
        422,
        'Preview is available for image files only.'
    );

    $content=Storage::disk($document->disk)
        ->get($document->path);

    return response($content,200,[
        'Content-Type'=>$document->mime_type,
        'Content-Disposition'=>'inline',
        'Cache-Control'=>'private, max-age=3600',
        'X-Content-Type-Options'=>'nosniff'
    ]);
}
}