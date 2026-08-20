<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    private const TYPES=[
        'asset',
        'liability',
        'equity',
        'income',
        'expense'
    ];

    public function paginate(array $filters=[]): LengthAwarePaginator
    {
        return Account::query()
            ->with([
                'parent:id,code,name,type'
            ])
            ->withCount([
                'children',
                'entries'
            ])
            ->withSum('entries as total_debit','debit')
            ->withSum('entries as total_credit','credit')
            ->when(
                $filters['search']??null,
                function($query,$search){
                    $search=trim($search);

                    $query->where(function($q)use($search){
                        $q->where('code','like',"%{$search}%")
                            ->orWhere('name','like',"%{$search}%")
                            ->orWhere('sub_type','like',"%{$search}%")
                            ->orWhere('description','like',"%{$search}%");
                    });
                }
            )
            ->when(
                $filters['type']??null,
                fn($query,$type)=>$query->where('type',$type)
            )
            ->when(
                array_key_exists('is_active',$filters)&&
                $filters['is_active']!=='',
                fn($query)=>$query->where(
                    'is_active',
                    filter_var(
                        $filters['is_active'],
                        FILTER_VALIDATE_BOOLEAN
                    )
                )
            )
            ->when(
                $filters['parent_id']??null,
                fn($query,$parentId)=>$query->where(
                    'parent_id',
                    $parentId
                )
            )
            ->orderBy('code')
            ->paginate(
                min((int)($filters['per_page']??25),100)
            );
    }

    public function summary(): array
    {
        $rows=Account::query()
            ->selectRaw("
                COUNT(*) total,
                SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) active,
                SUM(CASE WHEN is_active=0 THEN 1 ELSE 0 END) inactive,
                SUM(CASE WHEN is_system=1 THEN 1 ELSE 0 END) system_accounts
            ")
            ->first();

        $posting=Account::query()
            ->whereDoesntHave('children')
            ->count();

        return[
            'total'=>(int)($rows->total??0),
            'active'=>(int)($rows->active??0),
            'inactive'=>(int)($rows->inactive??0),
            'system_accounts'=>(int)($rows->system_accounts??0),
            'posting_accounts'=>$posting
        ];
    }

    public function options(?Account $exclude=null): array
    {
        $query=Account::query()
            ->orderBy('code');

        if($exclude){
            $excludedIds=$this->descendantIds($exclude);
            $excludedIds[]=$exclude->id;

            $query->whereNotIn(
                'id',
                array_unique($excludedIds)
            );
        }

        return[
            'types'=>self::TYPES,
            'parents'=>$query->get([
                'id',
                'parent_id',
                'code',
                'name',
                'type',
                'is_active',
                'is_system'
            ])
        ];
    }

    public function create(array $data): Account
    {
        return DB::transaction(function()use($data){
            $this->validateParent(
                $data['parent_id']??null,
                $data['type']
            );

            if(!empty($data['parent_id'])){
                $parent=Account::query()
                    ->lockForUpdate()
                    ->findOrFail($data['parent_id']);

                if($parent->entries()->exists()){
                    throw ValidationException::withMessages([
                        'parent_id'=>[
                            'An account with posted journal entries cannot be converted into a parent account.'
                        ]
                    ]);
                }
            }

            $account=Account::create([
                'parent_id'=>$data['parent_id']??null,
                'code'=>$data['code'],
                'name'=>$data['name'],
                'type'=>$data['type'],
                'sub_type'=>$data['sub_type']??null,
                'opening_balance'=>$data['opening_balance']??0,
                'is_system'=>false,
                'is_active'=>$data['is_active']??true,
                'description'=>$data['description']??null
            ]);

            return $this->fresh($account);
        });
    }

    public function update(
        Account $account,
        array $data
    ): Account{
        return DB::transaction(function()use($account,$data){
            $account=Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($account->is_system){
                foreach([
                    'code',
                    'type',
                    'sub_type',
                    'parent_id',
                    'opening_balance'
                ] as $field){
                    if(
                        array_key_exists($field,$data)&&
                        $this->changed(
                            $account->{$field},
                            $data[$field]
                        )
                    ){
                        throw ValidationException::withMessages([
                            $field=>[
                                'Core structure of a system account cannot be changed.'
                            ]
                        ]);
                    }
                }

                if(
                    array_key_exists('is_active',$data)&&
                    !$data['is_active']
                ){
                    throw ValidationException::withMessages([
                        'is_active'=>[
                            'System accounts cannot be deactivated.'
                        ]
                    ]);
                }
            }

            if(
                $account->entries()->exists()&&
                array_key_exists(
                    'opening_balance',
                    $data
                )&&
                $this->changed(
                    $account->opening_balance,
                    $data['opening_balance']
                )
            ){
                throw ValidationException::withMessages([
                    'opening_balance'=>[
                        'Opening balance cannot be changed after journal entries have been posted.'
                    ]
                ]);
            }

            $newType=$data['type']??$account->type;
            $newParent=$data['parent_id']
                ??$account->parent_id;

            if(
                array_key_exists('parent_id',$data)||
                array_key_exists('type',$data)
            ){
                $this->validateParent(
                    $newParent,
                    $newType,
                    $account
                );
            }

            if(
                array_key_exists('type',$data)&&
                $data['type']!==$account->type
            ){
                if(
                    $account->entries()->exists()||
                    $account->children()->exists()
                ){
                    throw ValidationException::withMessages([
                        'type'=>[
                            'Account type cannot be changed after the account has entries or child accounts.'
                        ]
                    ]);
                }
            }

            if(
                array_key_exists('parent_id',$data)&&
                $this->changed(
                    $account->parent_id,
                    $data['parent_id']
                )&&
                $account->entries()->exists()
            ){
                throw ValidationException::withMessages([
                    'parent_id'=>[
                        'Posted account cannot be moved to another parent.'
                    ]
                ]);
            }

            $account->update($data);

            return $this->fresh($account);
        });
    }

    public function toggle(Account $account): Account
    {
        return DB::transaction(function()use($account){
            $account=Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($account->is_system){
                throw ValidationException::withMessages([
                    'account'=>[
                        'System accounts cannot be deactivated.'
                    ]
                ]);
            }

            if(
                $account->is_active&&
                $account->children()
                    ->where('is_active',true)
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'account'=>[
                        'Deactivate active child accounts first.'
                    ]
                ]);
            }

            if(
                !$account->is_active&&
                $account->parent_id
            ){
                $parent=$account->parent;

                if(
                    !$parent||
                    !$parent->is_active
                ){
                    throw ValidationException::withMessages([
                        'account'=>[
                            'Parent account must be active before this account can be activated.'
                        ]
                    ]);
                }
            }

            $account->update([
                'is_active'=>!$account->is_active
            ]);

            return $this->fresh($account);
        });
    }

    public function delete(Account $account): void
    {
        DB::transaction(function()use($account){
            $account=Account::query()
                ->whereKey($account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if($account->is_system){
                throw ValidationException::withMessages([
                    'account'=>[
                        'System accounts cannot be deleted.'
                    ]
                ]);
            }

            if($account->children()->exists()){
                throw ValidationException::withMessages([
                    'account'=>[
                        'Account with child accounts cannot be deleted.'
                    ]
                ]);
            }

            if($account->entries()->exists()){
                throw ValidationException::withMessages([
                    'account'=>[
                        'Account with journal history cannot be deleted. Deactivate it instead.'
                    ]
                ]);
            }

            $account->delete();
        });
    }

    private function validateParent(
        ?int $parentId,
        string $type,
        ?Account $account=null
    ): void{
        if(!$parentId){
            return;
        }

        $parent=Account::query()
            ->whereKey($parentId)
            ->first();

        if(!$parent){
            throw ValidationException::withMessages([
                'parent_id'=>[
                    'Selected parent account does not exist.'
                ]
            ]);
        }

        if(!$parent->is_active){
            throw ValidationException::withMessages([
                'parent_id'=>[
                    'Parent account must be active.'
                ]
            ]);
        }

        if($parent->type!==$type){
            throw ValidationException::withMessages([
                'parent_id'=>[
                    'Parent and child account must have the same account type.'
                ]
            ]);
        }

        if(
            $account&&
            $parent->id===$account->id
        ){
            throw ValidationException::withMessages([
                'parent_id'=>[
                    'Account cannot be its own parent.'
                ]
            ]);
        }

        if(
            $account&&
            in_array(
                $parent->id,
                $this->descendantIds($account),
                true
            )
        ){
            throw ValidationException::withMessages([
                'parent_id'=>[
                    'A child account cannot be selected as parent.'
                ]
            ]);
        }
    }

    private function descendantIds(Account $account): array
    {
        $ids=[];
        $pending=[$account->id];

        while($pending){
            $children=Account::query()
                ->whereIn('parent_id',$pending)
                ->pluck('id')
                ->map(fn($id)=>(int)$id)
                ->all();

            if(!$children){
                break;
            }

            $ids=array_merge(
                $ids,
                $children
            );

            $pending=$children;
        }

        return $ids;
    }

    private function fresh(Account $account): Account
    {
        return $account->fresh([
            'parent:id,code,name,type'
        ])->loadCount([
            'children',
            'entries'
        ]);
    }

    private function changed(
        mixed $current,
        mixed $new
    ): bool{
        if(
            is_numeric($current)&&
            is_numeric($new)
        ){
            return round(
                (float)$current,
                2
            )!==round(
                (float)$new,
                2
            );
        }

        return (string)($current??'')!==
            (string)($new??'');
    }
}