<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberNominee;
use App\Models\NomineeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class NomineeService
{
    public function __construct(
        protected NotificationService $notificationService
    ){}

    public function create(
        Member $member,
        array $data,
        int $userId
    ): MemberNominee{
        return DB::transaction(function()use(
            $member,
            $data,
            $userId
        ){
            $member=Member::query()
                ->whereKey($member->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(in_array(
                $member->status,
                ['rejected'],
                true
            )){
                throw ValidationException::withMessages([
                    'member_id'=>[
                        'Rejected member cannot have nominees.'
                    ]
                ]);
            }

            $allocation=round(
                (float)$data['allocation_percentage'],
                2
            );

            $priority=(int)($data['priority']??1);

            $this->validateAllocation(
                $member,
                $allocation
            );

            if(($data['is_active']??true)){
                $this->validatePriority(
                    $member,
                    $priority
                );
            }

            $nominee=MemberNominee::create([
                'member_id'=>$member->id,
                'name'=>trim($data['name']),
                'relationship'=>trim($data['relationship']),
                'phone'=>$data['phone']??null,
                'identity_type'=>$data['identity_type']??null,
                'identity_number'=>$data['identity_number']??null,
                'date_of_birth'=>$data['date_of_birth']??null,
                'address'=>$data['address']??null,
                'allocation_percentage'=>$allocation,
                'priority'=>$priority,
                'is_active'=>$data['is_active']??true,
                'verification_status'=>'unverified',
                'notes'=>$data['notes']??null,
                'created_by'=>$userId,
                'updated_by'=>$userId
            ]);

            $this->forgetCaches($member->id);

            return $this->freshNominee($nominee);
        });
    }

    public function update(
        MemberNominee $nominee,
        array $data,
        int $userId
    ): MemberNominee{
        return DB::transaction(function()use(
            $nominee,
            $data,
            $userId
        ){
            $nominee=MemberNominee::query()
                ->whereKey($nominee->id)
                ->lockForUpdate()
                ->firstOrFail();

            $member=Member::query()
                ->whereKey($nominee->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            $allocation=round(
                (float)($data['allocation_percentage']
                    ??$nominee->allocation_percentage),
                2
            );

            $priority=(int)($data['priority']
                ??$nominee->priority);

            $isActive=array_key_exists('is_active',$data)
                ?(bool)$data['is_active']
                :$nominee->is_active;

            if($isActive){
                $this->validateAllocation(
                    $member,
                    $allocation,
                    $nominee->id
                );

                $this->validatePriority(
                    $member,
                    $priority,
                    $nominee->id
                );
            }

            $verificationSensitiveFields=[
                'name',
                'relationship',
                'identity_type',
                'identity_number',
                'date_of_birth',
                'allocation_percentage'
            ];

            $requiresReverification=false;

            foreach($verificationSensitiveFields as $field){
                if(
                    array_key_exists($field,$data)&&
                    (string)$nominee->{$field}!==
                    (string)$data[$field]
                ){
                    $requiresReverification=true;
                    break;
                }
            }

            $nominee->fill([
                'name'=>isset($data['name'])
                    ?trim($data['name'])
                    :$nominee->name,

                'relationship'=>isset($data['relationship'])
                    ?trim($data['relationship'])
                    :$nominee->relationship,

                'phone'=>$data['phone']
                    ??$nominee->phone,

                'identity_type'=>$data['identity_type']
                    ??$nominee->identity_type,

                'identity_number'=>$data['identity_number']
                    ??$nominee->identity_number,

                'date_of_birth'=>$data['date_of_birth']
                    ??$nominee->date_of_birth,

                'address'=>$data['address']
                    ??$nominee->address,

                'allocation_percentage'=>$allocation,
                'priority'=>$priority,
                'is_active'=>$isActive,

                'notes'=>$data['notes']
                    ??$nominee->notes,

                'updated_by'=>$userId
            ]);

            if(
                $requiresReverification&&
                $nominee->verification_status==='verified'
            ){
                $nominee->verification_status='pending';
                $nominee->verified_by=null;
                $nominee->verified_at=null;
                $nominee->verification_note=null;
                $nominee->rejection_reason=null;
            }

            $nominee->save();

            $this->forgetCaches($member->id);

            return $this->freshNominee($nominee);
        });
    }

    public function submitForVerification(
        MemberNominee $nominee,
        int $userId
    ): MemberNominee{
        return DB::transaction(function()use(
            $nominee,
            $userId
        ){
            $nominee=MemberNominee::query()
                ->whereKey($nominee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!$nominee->is_active){
                throw ValidationException::withMessages([
                    'nominee'=>[
                        'Inactive nominee cannot be submitted for verification.'
                    ]
                ]);
            }

            if(
                !$nominee->identity_type||
                !$nominee->identity_number
            ){
                throw ValidationException::withMessages([
                    'identity_number'=>[
                        'Identity type and identity number are required before verification.'
                    ]
                ]);
            }

            if($nominee->verification_status==='verified'){
                return $this->freshNominee($nominee);
            }

            $nominee->update([
                'verification_status'=>'pending',
                'verified_by'=>null,
                'verified_at'=>null,
                'verification_note'=>null,
                'rejection_reason'=>null,
                'updated_by'=>$userId
            ]);

            $this->forgetCaches($nominee->member_id);

            return $this->freshNominee($nominee);
        });
    }

    public function verify(
        MemberNominee $nominee,
        ?string $note,
        int $userId
    ): MemberNominee{
        return DB::transaction(function()use(
            $nominee,
            $note,
            $userId
        ){
            $nominee=MemberNominee::query()
                ->whereKey($nominee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if(!$nominee->is_active){
                throw ValidationException::withMessages([
                    'nominee'=>[
                        'Inactive nominee cannot be verified.'
                    ]
                ]);
            }

            if(
                !$nominee->identity_type||
                !$nominee->identity_number
            ){
                throw ValidationException::withMessages([
                    'identity_number'=>[
                        'Nominee identity information is incomplete.'
                    ]
                ]);
            }

            $nominee->update([
                'verification_status'=>'verified',
                'verified_by'=>$userId,
                'verified_at'=>now(),
                'verification_note'=>$note,
                'rejection_reason'=>null,
                'updated_by'=>$userId
            ]);

            $this->notifyMember(
                $nominee,
                'Nominee Verified',
                "{$nominee->name} has been verified as your nominee.",
                'success',
                $userId
            );

            $this->forgetCaches($nominee->member_id);

            return $this->freshNominee($nominee);
        });
    }

    public function rejectVerification(
        MemberNominee $nominee,
        string $reason,
        int $userId
    ): MemberNominee{
        return DB::transaction(function()use(
            $nominee,
            $reason,
            $userId
        ){
            $nominee=MemberNominee::query()
                ->whereKey($nominee->id)
                ->lockForUpdate()
                ->firstOrFail();

            $reason=trim($reason);

            if($reason===''){
                throw ValidationException::withMessages([
                    'rejection_reason'=>[
                        'Rejection reason is required.'
                    ]
                ]);
            }

            $nominee->update([
                'verification_status'=>'rejected',
                'verified_by'=>$userId,
                'verified_at'=>now(),
                'verification_note'=>null,
                'rejection_reason'=>$reason,
                'updated_by'=>$userId
            ]);

            $this->notifyMember(
                $nominee,
                'Nominee Verification Rejected',
                "{$nominee->name}'s nominee verification was rejected.",
                'warning',
                $userId
            );

            $this->forgetCaches($nominee->member_id);

            return $this->freshNominee($nominee);
        });
    }

    public function toggleActive(
        MemberNominee $nominee,
        bool $active,
        int $userId
    ): MemberNominee{
        return DB::transaction(function()use(
            $nominee,
            $active,
            $userId
        ){
            $nominee=MemberNominee::query()
                ->whereKey($nominee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($active){
                $member=Member::query()
                    ->whereKey($nominee->member_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->validateAllocation(
                    $member,
                    (float)$nominee->allocation_percentage,
                    $nominee->id
                );

                $this->validatePriority(
                    $member,
                    (int)$nominee->priority,
                    $nominee->id
                );
            }

            $nominee->update([
                'is_active'=>$active,
                'updated_by'=>$userId
            ]);

            $this->forgetCaches($nominee->member_id);

            return $this->freshNominee($nominee);
        });
    }

    public function delete(
        MemberNominee $nominee,
        int $userId
    ): void{
        DB::transaction(function()use(
            $nominee,
            $userId
        ){
            $nominee=MemberNominee::query()
                ->whereKey($nominee->id)
                ->lockForUpdate()
                ->firstOrFail();

            $memberId=$nominee->member_id;

            $nominee->update([
                'is_active'=>false,
                'updated_by'=>$userId
            ]);

            $nominee->delete();

            $this->forgetCaches($memberId);
        });
    }

    public function uploadDocument(
        MemberNominee $nominee,
        UploadedFile $file,
        string $documentType,
        int $userId
    ): NomineeDocument{
        $path=null;

        try{
            $path=$file->store(
                "nominees/{$nominee->member_id}/{$nominee->id}",
                'public'
            );

            $document=NomineeDocument::create([
                'member_nominee_id'=>$nominee->id,
                'document_type'=>$documentType,
                'file_path'=>$path,
                'original_name'=>$file->getClientOriginalName(),
                'mime_type'=>$file->getMimeType(),
                'file_size'=>$file->getSize(),
                'uploaded_by'=>$userId
            ]);

            if($nominee->verification_status==='verified'){
                $nominee->update([
                    'verification_status'=>'pending',
                    'verified_by'=>null,
                    'verified_at'=>null,
                    'verification_note'=>null,
                    'rejection_reason'=>null,
                    'updated_by'=>$userId
                ]);
            }

            $this->forgetCaches($nominee->member_id);

            return $document->fresh('uploader:id,name');

        }catch(\Throwable $e){
            if(
                $path&&
                Storage::disk('public')->exists($path)
            ){
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    public function removeDocument(
        NomineeDocument $document,
        int $userId
    ): void{
        DB::transaction(function()use(
            $document,
            $userId
        ){
            $document=NomineeDocument::query()
                ->with('nominee')
                ->whereKey($document->id)
                ->lockForUpdate()
                ->firstOrFail();

            $memberId=$document->nominee->member_id;

            $document->delete();

            if(
                $document->nominee->verification_status==='verified'
            ){
                $document->nominee->update([
                    'verification_status'=>'pending',
                    'verified_by'=>null,
                    'verified_at'=>null,
                    'verification_note'=>null,
                    'rejection_reason'=>null,
                    'updated_by'=>$userId
                ]);
            }

            $this->forgetCaches($memberId);
        });
    }

    public function summary(Member $member): array
    {
        return Cache::remember(
            "nominees:member:{$member->id}:summary",
            now()->addMinutes(5),
            function()use($member){
                $query=$member->nominees()
                    ->where('is_active',true);

                $allocation=round(
                    (float)(clone $query)
                        ->sum('allocation_percentage'),
                    2
                );

                return[
                    'total'=>(clone $query)->count(),
                    'verified'=>(clone $query)
                        ->where(
                            'verification_status',
                            'verified'
                        )
                        ->count(),

                    'pending'=>(clone $query)
                        ->where(
                            'verification_status',
                            'pending'
                        )
                        ->count(),

                    'allocation_total'=>$allocation,
                    'allocation_remaining'=>max(
                        0,
                        round(100-$allocation,2)
                    ),

                    'allocation_complete'=>
                        abs($allocation-100)<0.009
                ];
            }
        );
    }

    protected function validateAllocation(
        Member $member,
        float $newAllocation,
        ?int $ignoreId=null
    ): void{
        if(
            $newAllocation<=0||
            $newAllocation>100
        ){
            throw ValidationException::withMessages([
                'allocation_percentage'=>[
                    'Allocation must be greater than 0 and not more than 100%.'
                ]
            ]);
        }

        $existing=(float)$member->nominees()
            ->where('is_active',true)
            ->when(
                $ignoreId,
                fn($q)=>$q->whereKeyNot($ignoreId)
            )
            ->lockForUpdate()
            ->sum('allocation_percentage');

        if(round($existing+$newAllocation,2)>100){
            $remaining=max(
                0,
                round(100-$existing,2)
            );

            throw ValidationException::withMessages([
                'allocation_percentage'=>[
                    "Total nominee allocation cannot exceed 100%. Remaining allocation is {$remaining}%."
                ]
            ]);
        }
    }

    protected function validatePriority(
        Member $member,
        int $priority,
        ?int $ignoreId=null
    ): void{
        if($priority<1){
            throw ValidationException::withMessages([
                'priority'=>[
                    'Priority must be at least 1.'
                ]
            ]);
        }

        $exists=$member->nominees()
            ->where('is_active',true)
            ->where('priority',$priority)
            ->when(
                $ignoreId,
                fn($q)=>$q->whereKeyNot($ignoreId)
            )
            ->exists();

        if($exists){
            throw ValidationException::withMessages([
                'priority'=>[
                    "Another active nominee already has priority {$priority}."
                ]
            ]);
        }
    }

    protected function notifyMember(
        MemberNominee $nominee,
        string $title,
        string $message,
        string $type,
        int $senderId
    ): void{
        $nominee->loadMissing('member.user');

        $userId=$nominee->member?->user_id;

        if(!$userId){
            return;
        }

        DB::afterCommit(function()use(
            $title,
            $message,
            $type,
            $senderId,
            $userId
        ){
            $this->notificationService->sendSystem([
                'title'=>$title,
                'message'=>$message,
                'type'=>$type,
                'audience_type'=>'users',
                'user_ids'=>[$userId],
                'action_url'=>route('member.nominees'),
                'sent_by'=>$senderId
            ]);
        });
    }

    protected function freshNominee(
        MemberNominee $nominee
    ): MemberNominee{
        return $nominee->fresh([
            'member.user:id,name,email',
            'verifier:id,name',
            'creator:id,name',
            'documents'=>fn($q)=>$q
                ->with('uploader:id,name')
                ->latest()
        ]);
    }

    protected function forgetCaches(int $memberId): void
    {
        Cache::forget(
            "nominees:member:{$memberId}:summary"
        );
    }
}