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
        if(
            $notice->attachment &&
            Storage::disk('public')->exists($notice->attachment)
        ){
            Storage::disk('public')->delete($notice->attachment);
        }

        $notice->delete();
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