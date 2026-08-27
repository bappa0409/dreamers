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

        if(!$path){
            throw new \RuntimeException('Document file could not be stored.');
        }

        try{
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
        }catch(\Throwable $e){
            $this->deleteStoredFile('local',$path);
            throw $e;
        }

        $this->forgetCaches();

        return $document;
    }

    public function update(Document $document,array $data): Document
    {
        $document->update($data);
        $this->forgetCaches();

        return $document->fresh();
    }

    public function updateWithFile(
        Document $document,
        array $data,
        UploadedFile $file
    ): Document{
        $filename=$this->generateFilename($file);

        $newPath=$file->storeAs(
            'documents',
            $filename,
            'local'
        );

        if(!$newPath){
            throw new \RuntimeException('Replacement document file could not be stored.');
        }

        $oldDisk=$document->disk;
        $oldPath=$document->path;

        $fileData=[
            'original_name'=>$file->getClientOriginalName(),
            'path'=>$newPath,
            'disk'=>'local',
            'mime_type'=>$file->getMimeType(),
            'extension'=>strtolower($file->getClientOriginalExtension()),
            'size'=>$file->getSize()
        ];

        try{
            $document->update([
                ...$data,
                ...$fileData
            ]);
        }catch(\Throwable $e){
            $this->deleteStoredFile('local',$newPath);
            throw $e;
        }

        if($oldPath&&($oldDisk!=='local'||$oldPath!==$newPath)){
            $this->deleteStoredFile($oldDisk,$oldPath);
        }

        $this->forgetCaches();

        return $document->fresh();
    }

    public function replaceFile(Document $document,UploadedFile $file): Document
    {
        return $this->updateWithFile($document,[],$file);
    }

    public function delete(Document $document): void
    {
        $disk=$document->disk;
        $path=$document->path;

        $document->delete();

        if($path){
            $this->deleteStoredFile($disk,$path);
        }

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

    protected function deleteStoredFile(?string $disk,?string $path): void
    {
        if(!$disk||!$path){
            return;
        }

        try{
            $storage=Storage::disk($disk);

            if($storage->exists($path)){
                $storage->delete($path);
            }
        }catch(\Throwable $e){
            report($e);
        }
    }

    public function forgetCaches(): void
    {
        Cache::forget('documents:filters');
        Cache::forget('documents:categories');
        Cache::forget('documents:statistics');
    }
}
