<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\Investment;
use App\Models\Member;
use App\Models\Notice;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function summary(?string $from=null,?string $to=null): array
    {
        $key='reports:summary:'.md5(($from??'').':'.($to??''));

        return Cache::remember($key,now()->addMinutes(10),function()use($from,$to){
            return [
                'members'=>$this->memberSummary($from,$to),
                'investments'=>$this->investmentSummary($from,$to),
                'projects'=>$this->projectSummary($from,$to),
                'approvals'=>$this->approvalSummary($from,$to),
                'notices'=>$this->noticeSummary($from,$to),
            ];
        });
    }

    public function memberSummary(?string $from=null,?string $to=null): array
    {
        $query=Member::query();

        $this->applyDateFilter($query,$from,$to);

        $row=(clone $query)->selectRaw("
            COUNT(*) total,
            SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
            SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) rejected,
            SUM(CASE WHEN status='suspended' THEN 1 ELSE 0 END) suspended
        ")->first();

        return [
            'total'=>(int)($row->total??0),
            'active'=>(int)($row->active??0),
            'pending'=>(int)($row->pending??0),
            'rejected'=>(int)($row->rejected??0),
            'suspended'=>(int)($row->suspended??0),
        ];
    }

    public function investmentSummary(?string $from=null,?string $to=null): array
    {
        $query=Investment::query();

        $this->applyDateFilter($query,$from,$to);

        $row=(clone $query)->selectRaw("
            COUNT(*) total_records,
            COALESCE(SUM(amount),0) total_amount
        ")->first();

        return [
            'total_records'=>(int)($row->total_records??0),
            'total_amount'=>(float)($row->total_amount??0),
        ];
    }

    public function projectSummary(?string $from=null,?string $to=null): array
    {
        $query=Project::query();

        $this->applyDateFilter($query,$from,$to);

        $row=(clone $query)->selectRaw("
            COUNT(*) total,
            SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active,
            SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) completed,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending
        ")->first();

        return [
            'total'=>(int)($row->total??0),
            'active'=>(int)($row->active??0),
            'completed'=>(int)($row->completed??0),
            'pending'=>(int)($row->pending??0),
        ];
    }

    public function approvalSummary(?string $from=null,?string $to=null): array
    {
        $query=ApprovalRequest::query();

        $this->applyDateFilter($query,$from,$to);

        $row=(clone $query)->selectRaw("
            COUNT(*) total,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
            SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) approved,
            SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) rejected,
            SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled
        ")->first();

        return [
            'total'=>(int)($row->total??0),
            'pending'=>(int)($row->pending??0),
            'approved'=>(int)($row->approved??0),
            'rejected'=>(int)($row->rejected??0),
            'cancelled'=>(int)($row->cancelled??0),
        ];
    }

    public function noticeSummary(?string $from=null,?string $to=null): array
    {
        $query=Notice::query();

        $this->applyDateFilter($query,$from,$to);

        $row=(clone $query)->selectRaw("
            COUNT(*) total,
            SUM(CASE WHEN is_published=1 THEN 1 ELSE 0 END) published,
            SUM(CASE WHEN is_published=0 THEN 1 ELSE 0 END) draft,
            SUM(CASE WHEN priority='urgent' THEN 1 ELSE 0 END) urgent
        ")->first();

        return [
            'total'=>(int)($row->total??0),
            'published'=>(int)($row->published??0),
            'draft'=>(int)($row->draft??0),
            'urgent'=>(int)($row->urgent??0),
        ];
    }

    protected function applyDateFilter($query,?string $from,?string $to): void
    {
        if($from){
            $query->whereDate('created_at','>=',$from);
        }

        if($to){
            $query->whereDate('created_at','<=',$to);
        }
    }

    public function forgetCaches(): void
    {
        Cache::flush();
    }
}