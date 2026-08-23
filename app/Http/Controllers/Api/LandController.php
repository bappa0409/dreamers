<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Land;
use App\Models\LandDocument;
use App\Services\LandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandController extends Controller
{
    public function __construct(
        protected LandService $landService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:planned,negotiating,purchased,sold,cancelled',
            'district'=>'nullable|string|max:100',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $perPage=min(
            max((int)($validated['per_page']??15),5),
            100
        );

        $query=Land::query()
            ->select([
                'id',
                'land_code',
                'title',
                'district',
                'upazila',
                'mouza',
                'khatian_no',
                'dag_no',
                'land_area',
                'area_unit',
                'purchase_price',
                'current_value',
                'purchase_date',
                'status',
                'created_at',
            ])
            ->when(
                !empty($validated['search']),
                function($query)use($validated){
                    $search=trim($validated['search']);

                    $query->where(function($q)use($search){
                        $q->where(
                            'land_code',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'district',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'upazila',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'mouza',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'khatian_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'dag_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'deed_no',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'registration_no',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                !empty($validated['status']),
                fn($q)=>$q->where(
                    'status',
                    $validated['status']
                )
            )
            ->when(
                !empty($validated['district']),
                fn($q)=>$q->where(
                    'district',
                    trim($validated['district'])
                )
            )
            ->latest('id');

        return response()->json([
            'success'=>true,
            'data'=>$query
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->landService
                ->statistics(),
        ]);
    }

    public function options()
    {
        $accounts=Account::query()
            ->active()
            ->posting()
            ->where('type','asset')
            ->whereIn('sub_type',[
                'cash',
                'bank',
                'cash_bank',
            ])
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'name',
                'sub_type',
            ]);

        return response()->json([
            'success'=>true,
            'data'=>[
                'accounts'=>$accounts,
                'statuses'=>[
                    'planned',
                    'negotiating',
                    'purchased',
                    'sold',
                    'cancelled',
                ],
                'area_units'=>[
                    'decimal',
                    'katha',
                    'bigha',
                    'acre',
                    'sqft',
                    'hectare',
                ],
                'document_types'=>[
                    'deed',
                    'registration',
                    'mutation',
                    'khatian',
                    'tax_receipt',
                    'survey',
                    'valuation',
                    'agreement',
                    'map',
                    'other',
                ],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated=$this->validateLand(
            $request
        );

        $land=$this->landService->create(
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land created successfully.',
            'data'=>$land,
        ],201);
    }

    public function show(Land $land)
    {
        $land->load([
            'paymentAccount:id,code,name,type,sub_type',
            'creator:id,name,email',
            'financeTransaction.entries.account',
            'documents.uploader:id,name,email',
            'valuations'=>fn($q)=>
                $q->latest('valuation_date')
                    ->latest('id'),
            'valuations.creator:id,name,email',
            'disposal.receiveAccount:id,code,name,type,sub_type',
            'disposal.creator:id,name,email',
            'disposal.financeTransaction.entries.account',
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$land,
        ]);
    }

    public function update(
        Request $request,
        Land $land
    ){
        $validated=$this->validateLand(
            $request,
            true
        );

        $land=$this->landService->update(
            $land,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land updated successfully.',
            'data'=>$land,
        ]);
    }

    public function addValuation(
        Request $request,
        Land $land
    ){
        $validated=$request->validate([
            'valuation_date'=>'required|date_format:Y-m-d',
            'current_value'=>'required|numeric|min:0|max:9999999999999.99',
            'valued_by'=>'nullable|string|max:255',
            'notes'=>'nullable|string|max:3000',
        ]);

        $valuation=$this->landService
            ->addValuation(
                $land,
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Land valuation added successfully.',
            'data'=>$valuation,
        ],201);
    }

    public function sell(
        Request $request,
        Land $land
    ){
        $validated=$request->validate([
            'sale_price'=>'required|numeric|min:0.01|max:9999999999999.99',
            'selling_expense'=>'nullable|numeric|min:0|max:9999999999999.99',
            'sale_date'=>'required|date_format:Y-m-d',
            'receive_account_id'=>'required|integer|exists:accounts,id',
            'buyer_name'=>'nullable|string|max:255',
            'buyer_phone'=>'nullable|string|max:30',
            'reference_no'=>'nullable|string|max:150',
            'notes'=>'nullable|string|max:3000',
        ]);

        $disposal=$this->landService->sell(
            $land,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land sold successfully.',
            'data'=>$disposal,
        ]);
    }

    public function uploadDocument(
        Request $request,
        Land $land
    ){
        $validated=$request->validate([
            'document_type'=>'required|string|max:100',
            'document_number'=>'nullable|string|max:150',
            'document_date'=>'nullable|date_format:Y-m-d',
            'description'=>'nullable|string|max:3000',
            'file'=>'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $file=$request->file('file');

        $path=$file->store(
            "lands/{$land->id}",
            'public'
        );

        $document=$land->documents()->create([
            'document_type'=>$validated['document_type'],
            'document_number'=>
                isset($validated['document_number'])
                    ?trim($validated['document_number'])
                    :null,
            'file_path'=>$path,
            'file_name'=>$file->getClientOriginalName(),
            'document_date'=>$validated['document_date']??null,
            'description'=>
                isset($validated['description'])
                    ?trim($validated['description'])
                    :null,
            'uploaded_by'=>$request->user()->id,
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Land document uploaded successfully.',
            'data'=>$document->fresh([
                'uploader:id,name,email',
            ]),
        ],201);
    }

    public function deleteDocument(
        Land $land,
        LandDocument $landDocument
    ){
        abort_unless(
            (int)$landDocument->land_id===(int)$land->id,
            404
        );

        if(
            $landDocument->file_path&&
            Storage::disk('public')->exists(
                $landDocument->file_path
            )
        ){
            Storage::disk('public')->delete(
                $landDocument->file_path
            );
        }

        $landDocument->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Land document deleted successfully.',
        ]);
    }

    public function destroy(Land $land)
    {
        $this->landService->delete(
            $land
        );

        return response()->json([
            'success'=>true,
            'message'=>'Land deleted successfully.',
        ]);
    }

    protected function validateLand(
        Request $request,
        bool $update=false
    ): array{
        $required=$update
            ?'sometimes|required'
            :'required';

        return $request->validate([
            'title'=>"{$required}|string|max:255",

            'description'=>'nullable|string|max:5000',

            'district'=>'nullable|string|max:100',
            'upazila'=>'nullable|string|max:100',
            'mouza'=>'nullable|string|max:150',

            'khatian_no'=>'nullable|string|max:100',
            'dag_no'=>'nullable|string|max:100',

            'land_area'=>'nullable|numeric|min:0.0001|max:999999999999.9999',

            'area_unit'=>'nullable|in:decimal,katha,bigha,acre,sqft,hectare',

            'purchase_price'=>'nullable|numeric|min:0|max:9999999999999.99',

            'current_value'=>'nullable|numeric|min:0|max:9999999999999.99',

            'purchase_date'=>'nullable|date_format:Y-m-d',

            'seller_name'=>'nullable|string|max:255',
            'seller_phone'=>'nullable|string|max:30',

            'deed_no'=>'nullable|string|max:100',
            'registration_no'=>'nullable|string|max:100',

            'payment_account_id'=>'nullable|integer|exists:accounts,id',

            'status'=>'nullable|in:planned,negotiating,purchased,sold,cancelled',

            'notes'=>'nullable|string|max:5000',
        ]);
    }
}