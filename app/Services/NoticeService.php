<?php

namespace App\Services;

use App\Models\Notice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class NoticeService
{
    public function create(array $data): Notice
    {
        $notice=Notice::create($data);
        $this->forgetCaches();

        return $notice;
    }

    public function update(Notice $notice,array $data): Notice
    {
        $notice->update($data);
        $this->forgetCaches();

        return $notice->fresh();
    }

    public function delete(Notice $notice): void
    {
        $attachment=$notice->attachment;

        $notice->delete();

        if($attachment){
            try{
                $disk=Storage::disk('public');

                if($disk->exists($attachment)){
                    $disk->delete($attachment);
                }
            }catch(\Throwable $e){
                report($e);
            }
        }

        $this->forgetCaches();
    }

    public function togglePublish(Notice $notice): Notice
    {
        $notice->update([
            'is_published'=>!$notice->is_published
        ]);

        $this->forgetCaches();

        return $notice->fresh();
    }

    public function forgetCaches(): void
    {
        Cache::forget('notices:published');
        Cache::forget('notices:latest');
        Cache::forget('dashboard:notices');
    }
}
