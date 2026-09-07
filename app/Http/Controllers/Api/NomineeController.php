<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberNominee;
use App\Models\NomineeDocument;
use App\Services\NomineeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NomineeController extends Controller
{
    public function __construct(
        protected NomineeService $nomineeService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'member_id'=>'nullable|integer|exists:members,id',
            'verification_status'=>
                'nullable|in:unverified,pending,verified,rejected',
            'active'=>'nullable|boolean',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=MemberNominee::query()
            ->with([
                'member:id,user_id,member_code',
                'member.user:id,name,email',
                'verifier:id,name'
            ])
            ->withCount('documents')
            ->when(
                $validated['member_id']??null,
                fn($q,$id)=>$q->where(
                    'member_id',
                    $id
                )
            )
            ->when(
                $validated['verification_status']??null,
                fn($q,$status)=>$q->where(
                    'verification_status',
                    $status
                )
            )
            ->when(
                array_key_exists('active',$validated),
                fn($q)=>$q->where(
                    'is_active',
                    (bool)$validated['active']
                )
            )
            ->when(
                $validated['search']??null,
                function($q,$search){
                    $search=trim($search);

                    $q->where(function($q)use($search){
                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'identity_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'member',
                            function($m)use($search){
                                $m->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    fn($u)=>$u->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                            }
                        );
                    });
                }
            )
            ->orderByDesc('id');

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                $validated['per_page']??15
            )->withQueryString()
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'total'=>MemberNominee::count(),

                'active'=>MemberNominee::where(
                    'is_active',
                    true
                )->count(),

                'verified'=>MemberNominee::where(
                    'verification_status',
                    'verified'
                )->count(),

                'pending'=>MemberNominee::where(
                    'verification_status',
                    'pending'
                )->count()
            ]
        ]);
    }

    public function options(Request $request)
    {
        $search=trim(
            (string)$request->query(
                'member_search',
                ''
            )
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'members'=>Member::query()
                    ->select([
                        'id',
                        'user_id',
                        'member_code',
                        'status'
                    ])
                    ->with('user:id,name')
                    ->whereNotIn(
                        'status',
                        ['rejected','exited','deceased']
                    )
                    ->when(
                        $search,
                        function($q)use($search){
                            $q->where(function($q)use($search){
                                $q->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    fn($u)=>$u->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                            });
                        }
                    )
                    ->limit(30)
                    ->get()
            ]
        ]);
    }

    public function memberSummary(Member $member)
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->nomineeService->summary(
                $member
            )
        ]);
    }

    public function show(MemberNominee $nominee)
    {
        return response()->json([
            'success'=>true,
            'data'=>$nominee->load([
                'member.user',
                'verifier:id,name',
                'creator:id,name',
                'updater:id,name',
                'documents.uploader:id,name'
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated=$this->validateNominee(
            $request
        );

        $photo=$request->file('photo');
        $identityDocument=$request->file('identity_document');

        unset($validated['photo'],$validated['identity_document']);

        $member=Member::findOrFail(
            $validated['member_id']
        );

        return response()->json([
            'success'=>true,
            'message'=>'Nominee created successfully.',
            'data'=>$this->nomineeService->create(
                $member,
                $validated,
                $request->user()->id,
                $photo,
                $identityDocument
            )
        ],201);
    }

    public function update(
        Request $request,
        MemberNominee $nominee
    ){
        $validated=$this->validateNominee(
            $request,
            true
        );

        unset($validated['member_id']);

        $photo=$request->file('photo');
        $identityDocument=$request->file('identity_document');

        unset($validated['photo'],$validated['identity_document']);

        return response()->json([
            'success'=>true,
            'message'=>'Nominee updated successfully.',
            'data'=>$this->nomineeService->update(
                $nominee,
                $validated,
                $request->user()->id,
                $photo,
                $identityDocument
            )
        ]);
    }

    public function verify(
        Request $request,
        MemberNominee $nominee
    ){
        $validated=$request->validate([
            'verification_note'=>
                'nullable|string|max:3000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Nominee verified successfully.',
            'data'=>$this->nomineeService->verify(
                $nominee,
                $validated['verification_note']??null,
                $request->user()->id
            )
        ]);
    }

    public function reject(
        Request $request,
        MemberNominee $nominee
    ){
        $validated=$request->validate([
            'rejection_reason'=>
                'required|string|max:3000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Nominee verification rejected.',
            'data'=>$this->nomineeService
                ->rejectVerification(
                    $nominee,
                    $validated['rejection_reason'],
                    $request->user()->id
                )
        ]);
    }

    public function toggle(
        Request $request,
        MemberNominee $nominee
    ){
        $validated=$request->validate([
            'is_active'=>'required|boolean'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Nominee status updated.',
            'data'=>$this->nomineeService
                ->toggleActive(
                    $nominee,
                    (bool)$validated['is_active'],
                    $request->user()->id
                )
        ]);
    }

    public function uploadDocument(
        Request $request,
        MemberNominee $nominee
    ){
        $validated=$request->validate([
            'document_type'=>
                'required|string|max:80',

            'file'=>
                'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Document uploaded successfully.',
            'data'=>$this->nomineeService
                ->uploadDocument(
                    $nominee,
                    $request->file('file'),
                    $validated['document_type'],
                    $request->user()->id
                )
        ],201);
    }

    public function document(NomineeDocument $document)
    {
        $disk=$this->nomineeService->documentDisk($document);

        abort_unless($disk,404);

        return Storage::disk($disk)->download(
            $document->file_path,
            $document->original_name,
            ['Content-Type'=>$document->mime_type?:'application/octet-stream']
        );
    }

    public function deleteDocument(
        Request $request,
        NomineeDocument $document
    ){
        $this->nomineeService->removeDocument(
            $document,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Document removed successfully.'
        ]);
    }

    public function destroy(
        Request $request,
        MemberNominee $nominee
    ){
        $this->nomineeService->delete(
            $nominee,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Nominee removed successfully.'
        ]);
    }

    protected function validateNominee(
        Request $request,
        bool $update=false
    ): array{
        $prefix=$update?'sometimes|':'';
        $fileRule=$update?'nullable':'required';

        return $request->validate([
            'member_id'=>$update
                ?'sometimes|integer|exists:members,id'
                :'required|integer|exists:members,id',

            'name'=>
                $prefix.'required|string|max:150',

            'relationship'=>
                $prefix.'required|string|max:80',

            'father_or_husband_name'=>
                $prefix.'required|string|max:150',

            'mother_name'=>
                $prefix.'required|string|max:150',

            'phone'=>
                'nullable|string|max:30',

            'identity_type'=>
                $prefix.'required|in:nid,birth_certificate,passport,other',

            'identity_number'=>
                $prefix.'required|string|max:100',

            'date_of_birth'=>
                $prefix.'required|date|before_or_equal:today',

            'gender'=>
                $prefix.'required|in:male,female,other',

            'profession'=>
                'nullable|string|max:150',

            'address'=>
                $prefix.'required|string|max:3000',

            'permanent_address'=>
                $prefix.'required|string|max:3000',

            'allocation_percentage'=>
                $prefix.'required|numeric|min:0.01|max:100',

            'priority'=>
                'nullable|integer|min:1|max:999',

            'is_active'=>
                'nullable|boolean',

            'notes'=>
                'nullable|string|max:3000',

            'photo'=>
                $fileRule.'|image|mimes:jpg,jpeg,png,webp|max:2048',

            'identity_document'=>
                $fileRule.'|file|mimes:pdf,jpg,jpeg,png,webp|max:5120'
        ]);
    }
}