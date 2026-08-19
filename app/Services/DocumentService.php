<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function create(array $data,UploadedFile $file,int $userId): Document
    {
        $filename=$this->generateFilename($file);

        $path=$file->storeAs(
            'documents',
            $filename,
            'local'
        );

        $document=Document::create([
            'title'=>$data['title'],
            'original_name'=>$file->getClientOriginalName(),
            'path'=>$path,
            'disk'=>'local',
            'mime_type'=>$file->getMimeType(),
            'extension'=>strtolower($file->getClientOriginalExtension()),
            'size'=>$file->getSize(),
            'category'=>$data['category']??null,
            'description'=>$data['description']??null,
            'visibility'=>$data['visibility']??'internal',
            'is_active'=>$data['is_active']??true,
            'uploaded_by'=>$userId
        ]);

        $this->forgetCaches();

        return $document;
    }

    public function update(Document $document,array $data): Document
    {
        $document->update($data);
        $this->forgetCaches();

        return $document->fresh();
    }

    public function replaceFile(Document $document,UploadedFile $file): Document
    {
        if(
            $document->path &&
            Storage::disk($document->disk)->exists($document->path)
        ){
            Storage::disk($document->disk)->delete($document->path);
        }

        $filename=$this->generateFilename($file);

        $path=$file->storeAs(
            'documents',
            $filename,
            'local'
        );

        $document->update([
            'original_name'=>$file->getClientOriginalName(),
            'path'=>$path,
            'disk'=>'local',
            'mime_type'=>$file->getMimeType(),
            'extension'=>strtolower($file->getClientOriginalExtension()),
            'size'=>$file->getSize()
        ]);

        $this->forgetCaches();

        return $document->fresh();
    }

    public function delete(Document $document): void
    {
        if(
            $document->path &&
            Storage::disk($document->disk)->exists($document->path)
        ){
            Storage::disk($document->disk)->delete($document->path);
        }

        $document->delete();
        $this->forgetCaches();
    }

    public function download(Document $document)
    {
        abort_unless(
            $document->path &&
            Storage::disk($document->disk)->exists($document->path),
            404,
            'Document file not found.'
        );

        return Storage::disk($document->disk)->download(
            $document->path,
            $document->original_name,
            [
                'Content-Type'=>$document->mime_type??'application/octet-stream',
                'X-Content-Type-Options'=>'nosniff',
                'Cache-Control'=>'private, no-store'
            ]
        );
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension=strtolower(
            $file->getClientOriginalExtension()
        );

        return now()->format('Ymd_His')
            .'_'
            .Str::lower(Str::random(12))
            .($extension?".{$extension}":'');
    }

    public function forgetCaches(): void
    {
        Cache::forget('documents:filters');
        Cache::forget('documents:categories');
        Cache::forget('documents:statistics');
    }
}