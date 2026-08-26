<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemberNominee;
use App\Models\NomineeDocument;
use App\Services\NomineeService;
use Illuminate\Http\Request;

class MemberNomineeController extends Controller
{
    public function __construct(
        protected NomineeService $nomineeService
    ){}

    public function index(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        $validated=$request->validate([
            'page'=>'nullable|integer|min:1',
            'per_page'=>'nullable|integer|min:5|max:50',
            'verification_status'=>
                'nullable|in:unverified,pending,verified,rejected'
        ]);

        $perPage=(int)($validated['per_page']??10);
        $verificationStatus=
            $validated['verification_status']??null;

        return response()->json([
            'success'=>true,
            'data'=>[
                'summary'=>
                    $this->nomineeService
                        ->summary($member),

                'nominees'=>$this->nomineeService
                    ->memberNomineesPaginated(
                        $member,
                        $verificationStatus,
                        $perPage
                    )
            ]
        ]);
    }

    public function store(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        $validated=$this->rules($request);

        return response()->json([
            'success'=>true,
            'message'=>'Nominee added successfully.',
            'data'=>$this->nomineeService->create(
                $member,
                $validated,
                $request->user()->id
            )
        ],201);
    }

    public function update(
        Request $request,
        MemberNominee $nominee
    ){
        $this->authorizeOwnership(
            $request,
            $nominee
        );

        $validated=$this->rules(
            $request,
            true
        );

        return response()->json([
            'success'=>true,
            'message'=>'Nominee updated successfully.',
            'data'=>$this->nomineeService->update(
                $nominee,
                $validated,
                $request->user()->id
            )
        ]);
    }

    public function submit(
        Request $request,
        MemberNominee $nominee
    ){
        $this->authorizeOwnership(
            $request,
            $nominee
        );

        return response()->json([
            'success'=>true,
            'message'=>
                'Nominee submitted for verification.',

            'data'=>$this->nomineeService
                ->submitForVerification(
                    $nominee,
                    $request->user()->id
                )
        ]);
    }

    public function toggle(
        Request $request,
        MemberNominee $nominee
    ){
        $this->authorizeOwnership(
            $request,
            $nominee
        );

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
        $this->authorizeOwnership(
            $request,
            $nominee
        );

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

    public function deleteDocument(
        Request $request,
        NomineeDocument $document
    ){
        $document->loadMissing('nominee');

        abort_unless(
            $document->nominee?->member_id===
            $request->user()->member?->id,
            403
        );

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
        $this->authorizeOwnership(
            $request,
            $nominee
        );

        $this->nomineeService->delete(
            $nominee,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Nominee removed successfully.'
        ]);
    }

    protected function authorizeOwnership(
        Request $request,
        MemberNominee $nominee
    ): void{
        abort_unless(
            $nominee->member_id===
            $request->user()->member?->id,
            403
        );
    }

    protected function rules(
        Request $request,
        bool $update=false
    ): array{
        $prefix=$update?'sometimes|':'';

        return $request->validate([
            'name'=>
                $prefix.'required|string|max:150',

            'relationship'=>
                $prefix.'required|string|max:80',

            'phone'=>'nullable|string|max:30',

            'identity_type'=>
                'nullable|in:nid,birth_certificate,passport,other',

            'identity_number'=>
                'nullable|string|max:100',

            'date_of_birth'=>
                'nullable|date|before_or_equal:today',

            'address'=>'nullable|string|max:3000',

            'allocation_percentage'=>
                $prefix.'required|numeric|min:0.01|max:100',

            'priority'=>
                'nullable|integer|min:1|max:999',

            'is_active'=>'nullable|boolean',

            'notes'=>'nullable|string|max:3000'
        ]);
    }
}