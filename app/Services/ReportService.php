<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\Land;
use App\Models\Member;
use App\Models\Notice;
use App\Models\Poll;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportService
{
    public const MODULES=[
        'members',
        'finance',
        'investments',
        'land',
        'projects',
        'polls',
        'notices',
    ];

    public function query(string $module,Request $request): Builder
    {
        [$from,$to]=$this->dates($request);
        $search=trim((string)$request->input('search',''));
        $status=$request->input('status');

        return match($module){
            'members'=>$this->membersQuery($search,$status,$from,$to),
            'finance'=>$this->financeQuery($search,$status,$from,$to),
            'investments'=>$this->investmentsQuery($search,$status,$from,$to),
            'land'=>$this->landQuery($search,$status,$from,$to),
            'projects'=>$this->projectsQuery($search,$status,$from,$to),
            'polls'=>$this->pollsQuery($search,$status,$from,$to),
            'notices'=>$this->noticesQuery($search,$status,$from,$to),
            default=>abort(404,'Invalid report module.'),
        };
    }

    public function summary(Request $request): array
    {
        [$from,$to]=$this->dates($request);

        $members=Member::query();
        $investments=Investment::query();
        $lands=Land::query();
        $projects=Project::query();
        $polls=Poll::query();
        $notices=Notice::query();
        $transactions=Transaction::query()->where('status','posted');

        $this->dateFilter($members,'created_at',$from,$to);
        $this->dateFilter($investments,'investment_date',$from,$to);
        $this->dateFilter($lands,'purchase_date',$from,$to);
        $this->dateFilter($projects,'start_date',$from,$to);
        $this->dateFilter($polls,'created_at',$from,$to);
        $this->dateFilter($notices,'created_at',$from,$to);
        $this->dateFilter($transactions,'transaction_date',$from,$to);

        $incomeTransactions=(clone $transactions)
            ->where('type','income')
            ->with('entries')
            ->get();

        $expenseTransactions=(clone $transactions)
            ->where('type','expense')
            ->with('entries')
            ->get();

        $income=(float)$incomeTransactions->sum(
            fn($transaction)=>(float)$transaction->entries->sum('credit')
        );

        $expense=(float)$expenseTransactions->sum(
            fn($transaction)=>(float)$transaction->entries->sum('debit')
        );

        return[
            'members'=>[
                'total'=>(clone $members)->count(),
                'active'=>(clone $members)->where('status','active')->count(),
                'pending'=>(clone $members)->where('status','pending')->count(),
                'inactive'=>(clone $members)->where('status','inactive')->count(),
                'suspended'=>(clone $members)->where('status','suspended')->count(),
                'rejected'=>(clone $members)->where('status','rejected')->count(),
            ],
            'investments'=>[
                'total'=>(clone $investments)->count(),
                'amount'=>(float)(clone $investments)->sum('amount'),
                'expected_return'=>(float)(clone $investments)->sum('expected_return'),
                'pending'=>(clone $investments)->where('status','pending')->count(),
                'active'=>(clone $investments)->where('status','active')->count(),
                'completed'=>(clone $investments)->where('status','completed')->count(),
                'cancelled'=>(clone $investments)->where('status','cancelled')->count(),
            ],
            'land'=>[
                'total'=>(clone $lands)->count(),
                'planned'=>(clone $lands)->where('status','planned')->count(),
                'negotiating'=>(clone $lands)->where('status','negotiating')->count(),
                'purchased'=>(clone $lands)->where('status','purchased')->count(),
                'sold'=>(clone $lands)->where('status','sold')->count(),
                'cancelled'=>(clone $lands)->where('status','cancelled')->count(),
                'purchase_value'=>(float)(clone $lands)->sum('purchase_price'),
            ],
            'projects'=>[
                'total'=>(clone $projects)->count(),
                'planned'=>(clone $projects)->where('status','planned')->count(),
                'active'=>(clone $projects)->where('status','active')->count(),
                'on_hold'=>(clone $projects)->where('status','on_hold')->count(),
                'completed'=>(clone $projects)->where('status','completed')->count(),
                'cancelled'=>(clone $projects)->where('status','cancelled')->count(),
                'budget'=>(float)(clone $projects)->sum('budget'),
                'cost'=>(float)(clone $projects)->sum('actual_cost'),
            ],
            'polls'=>[
                'total'=>(clone $polls)->count(),
                'active'=>(clone $polls)
                    ->where('is_active',true)
                    ->where('start_at','<=',now())
                    ->where('end_at','>=',now())
                    ->count(),
                'upcoming'=>(clone $polls)
                    ->where('is_active',true)
                    ->where('start_at','>',now())
                    ->count(),
                'ended'=>(clone $polls)
                    ->where('end_at','<',now())
                    ->count(),
                'inactive'=>(clone $polls)
                    ->where('is_active',false)
                    ->count(),
                'votes'=>(clone $polls)
                    ->withCount('votes')
                    ->get()
                    ->sum('votes_count'),
            ],
            'notices'=>[
                'total'=>(clone $notices)->count(),
                'published'=>(clone $notices)->where('is_published',true)->count(),
                'draft'=>(clone $notices)->where('is_published',false)->count(),
                'urgent'=>(clone $notices)->where('priority','urgent')->count(),
                'high'=>(clone $notices)->where('priority','high')->count(),
            ],
            'finance'=>[
                'transactions'=>(clone $transactions)->count(),
                'income'=>$income,
                'expense'=>$expense,
                'net'=>$income-$expense,
            ],
        ];
    }

    public function meta(string $module): array
    {
        return match($module){
            'members'=>[
                'title'=>'Member Report',
                'headings'=>[
                    'Member Code',
                    'Name',
                    'Email',
                    'Phone',
                    'Joining Date',
                    'Status',
                    'Roles',
                ],
            ],
            'finance'=>[
                'title'=>'Finance Report',
                'headings'=>[
                    'Transaction No',
                    'Date',
                    'Type',
                    'Description',
                    'Debit',
                    'Credit',
                    'Status',
                    'Created By',
                ],
            ],
            'investments'=>[
                'title'=>'Investment Report',
                'headings'=>[
                    'Investment No',
                    'Title',
                    'Member',
                    'Amount',
                    'Expected Return',
                    'Paid Return',
                    'Investment Date',
                    'Maturity Date',
                    'Status',
                ],
            ],
            'land'=>[
                'title'=>'Land Report',
                'headings'=>[
                    'Land Code',
                    'Title',
                    'Location',
                    'Area',
                    'Purchase Price',
                    'Current/Sale Value',
                    'Purchase Date',
                    'Sale Date',
                    'Status',
                    'Profit/Loss',
                ],
            ],
            'projects'=>[
                'title'=>'Project Report',
                'headings'=>[
                    'Project Code',
                    'Name',
                    'Location',
                    'Budget',
                    'Actual Cost',
                    'Progress',
                    'Status',
                    'Start Date',
                    'Expected End',
                    'Members',
                ],
            ],
            'polls'=>[
                'title'=>'Poll Report',
                'headings'=>[
                    'Title',
                    'Start',
                    'End',
                    'Active',
                    'Votes',
                ],
            ],
            'notices'=>[
                'title'=>'Notice Report',
                'headings'=>[
                    'Title',
                    'Type',
                    'Priority',
                    'Published',
                    'Publish At',
                    'Expires At',
                    'Created By',
                ],
            ],
            default=>abort(404,'Invalid report module.'),
        };
    }

    public function definition(string $module,$rows): array
    {
        return match($module){
            'members'=>$this->memberDefinition($rows),
            'finance'=>$this->financeDefinition($rows),
            'investments'=>$this->investmentDefinition($rows),
            'land'=>$this->landDefinition($rows),
            'projects'=>$this->projectDefinition($rows),
            'polls'=>$this->pollDefinition($rows),
            'notices'=>$this->noticeDefinition($rows),
            default=>abort(404,'Invalid report module.'),
        };
    }

    private function membersQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Member::query()
            ->with(['user.roles'])
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('member_code','like',"%{$search}%")
                    ->orWhere('phone','like',"%{$search}%")
                    ->orWhere('alternate_phone','like',"%{$search}%")
                    ->orWhereHas('user',function($user)use($search){
                        $user->where(function($u)use($search){
                            $u->where('name','like',"%{$search}%")
                                ->orWhere('email','like',"%{$search}%")
                                ->orWhere('mobile','like',"%{$search}%");
                        });
                    });
            });
        }

        if($status)$query->where('status',$status);

        $this->dateFilter($query,'joining_date',$from,$to);

        return $query;
    }

    private function financeQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Transaction::query()
            ->with(['creator','entries.account'])
            ->latest('transaction_date')
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('transaction_no','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhere('reference_type','like',"%{$search}%")
                    ->orWhereHas('creator',fn($user)=>
                        $user->where('name','like',"%{$search}%")
                    );
            });
        }

        if($status)$query->where('status',$status);

        $this->dateFilter($query,'transaction_date',$from,$to);

        return $query;
    }

    private function investmentsQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Investment::query()
            ->with(['member.user','returns'])
            ->latest('investment_date')
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('investment_no','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhereHas('member.user',fn($user)=>
                        $user->where('name','like',"%{$search}%")
                    );
            });
        }

        if($status)$query->where('status',$status);

        $this->dateFilter($query,'investment_date',$from,$to);

        return $query;
    }

    private function landQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Land::query()
            ->latest('purchase_date')
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('land_code','like',"%{$search}%")
                    ->orWhere('title','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhere('district','like',"%{$search}%")
                    ->orWhere('upazila','like',"%{$search}%")
                    ->orWhere('mouza','like',"%{$search}%")
                    ->orWhere('khatian_no','like',"%{$search}%")
                    ->orWhere('dag_no','like',"%{$search}%")
                    ->orWhere('seller_name','like',"%{$search}%");
            });
        }

        if($status)$query->where('status',$status);

        $this->dateFilter($query,'purchase_date',$from,$to);

        return $query;
    }

    private function projectsQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Project::query()
            ->withCount('members')
            ->latest('start_date')
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('project_code','like',"%{$search}%")
                    ->orWhere('name','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%")
                    ->orWhere('location','like',"%{$search}%");
            });
        }

        if($status)$query->where('status',$status);

        $this->dateFilter($query,'start_date',$from,$to);

        return $query;
    }

    private function pollsQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Poll::query()
            ->withCount('votes')
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhere('description','like',"%{$search}%");
            });
        }

        if($status==='active'){
            $query->where('is_active',true)
                ->where('start_at','<=',now())
                ->where('end_at','>=',now());
        }

        if($status==='upcoming'){
            $query->where('is_active',true)
                ->where('start_at','>',now());
        }

        if($status==='ended'){
            $query->where('end_at','<',now());
        }

        if($status==='inactive'){
            $query->where('is_active',false);
        }

        $this->dateFilter($query,'start_at',$from,$to);

        return $query;
    }

    private function noticesQuery(string $search,?string $status,?string $from,?string $to): Builder
    {
        $query=Notice::query()
            ->with('creator')
            ->latest('id');

        if($search){
            $query->where(function($q)use($search){
                $q->where('title','like',"%{$search}%")
                    ->orWhere('content','like',"%{$search}%");
            });
        }

        if($status==='published'){
            $query->where('is_published',true);
        }

        if($status==='draft'){
            $query->where('is_published',false);
        }

        if($status==='urgent'){
            $query->where('priority','urgent');
        }

        if($status==='high'){
            $query->where('priority','high');
        }

        $this->dateFilter($query,'created_at',$from,$to);

        return $query;
    }

    private function memberDefinition($rows): array
    {
        $meta=$this->meta('members');

        return[
            ...$meta,
            'rows'=>$rows->map(function($member){
                return[
                    $member->member_code??'',
                    $member->user?->name??'',
                    $member->user?->email??'',
                    $member->phone??$member->user?->mobile??'',
                    $this->date($member->joining_date),
                    $this->label($member->status),
                    $member->user?->roles?->map(
                        fn($role)=>$role->display_name??$role->name
                    )->filter()->implode(', ')??'',
                ];
            })->all(),
        ];
    }

    private function financeDefinition($rows): array
    {
        $meta=$this->meta('finance');

        return[
            ...$meta,
            'rows'=>$rows->map(function($transaction){
                return[
                    $transaction->transaction_no??'',
                    $this->date($transaction->transaction_date),
                    $this->label($transaction->type),
                    $transaction->description??'',
                    (float)$transaction->entries->sum('debit'),
                    (float)$transaction->entries->sum('credit'),
                    $this->label($transaction->status),
                    $transaction->creator?->name??'',
                ];
            })->all(),
        ];
    }

    private function investmentDefinition($rows): array
    {
        $meta=$this->meta('investments');

        return[
            ...$meta,
            'rows'=>$rows->map(function($investment){
                $paidReturn=(float)$investment->returns
                    ->where('status','paid')
                    ->sum('amount');

                return[
                    $investment->investment_no??'',
                    $investment->title??'',
                    $investment->member?->user?->name??'',
                    (float)$investment->amount,
                    (float)$investment->expected_return,
                    $paidReturn,
                    $this->date($investment->investment_date),
                    $this->date($investment->maturity_date),
                    $this->label($investment->status),
                ];
            })->all(),
        ];
    }

    private function landDefinition($rows): array
    {
        $meta=$this->meta('land');

        return[
            ...$meta,
            'rows'=>$rows->map(function($land){
                $location=collect([
                    $land->mouza,
                    $land->upazila,
                    $land->district,
                ])->filter()->implode(', ');

                $currentOrSaleValue=$land->status==='sold'
                    ?($land->sale_price??0)
                    :($land->current_value??0);

                return[
                    $land->land_code??'',
                    $land->title??'',
                    $location,
                    trim(($land->land_area??'').' '.($land->area_unit??'')),
                    (float)($land->purchase_price??0),
                    (float)$currentOrSaleValue,
                    $this->date($land->purchase_date),
                    $this->date($land->sale_date??null),
                    $this->label($land->status),
                    (float)($land->profit_loss??0),
                ];
            })->all(),
        ];
    }

    private function projectDefinition($rows): array
    {
        $meta=$this->meta('projects');

        return[
            ...$meta,
            'rows'=>$rows->map(function($project){
                return[
                    $project->project_code??'',
                    $project->name??'',
                    $project->location??'',
                    (float)($project->budget??0),
                    (float)($project->actual_cost??0),
                    ($project->progress??0).'%',
                    $this->label($project->status),
                    $this->date($project->start_date),
                    $this->date($project->expected_end_date),
                    $project->members_count??0,
                ];
            })->all(),
        ];
    }

    private function pollDefinition($rows): array
    {
        $meta=$this->meta('polls');

        return[
            ...$meta,
            'rows'=>$rows->map(function($poll){
                return[
                    $poll->title??'',
                    $this->dateTime($poll->start_at),
                    $this->dateTime($poll->end_at),
                    $poll->is_active?'Yes':'No',
                    $poll->votes_count??0,
                ];
            })->all(),
        ];
    }

    private function noticeDefinition($rows): array
    {
        $meta=$this->meta('notices');

        return[
            ...$meta,
            'rows'=>$rows->map(function($notice){
                return[
                    $notice->title??'',
                    $this->label($notice->type),
                    $this->label($notice->priority),
                    $notice->is_published?'Yes':'No',
                    $this->dateTime($notice->publish_at),
                    $this->dateTime($notice->expires_at),
                    $notice->creator?->name??'',
                ];
            })->all(),
        ];
    }

    private function dates(Request $request): array
    {
        return[
            $request->filled('from')
                ?$request->date('from')->toDateString()
                :null,
            $request->filled('to')
                ?$request->date('to')->toDateString()
                :null,
        ];
    }

    private function dateFilter(Builder $query,string $column,?string $from,?string $to): void
    {
        if($from){
            $query->whereDate($column,'>=',$from);
        }

        if($to){
            $query->whereDate($column,'<=',$to);
        }
    }

    private function date($value): string
    {
        if(!$value)return'';

        if(method_exists($value,'format')){
            return$value->format('Y-m-d');
        }

        return(string)$value;
    }

    private function dateTime($value): string
    {
        if(!$value)return'';

        if(method_exists($value,'format')){
            return$value->format('Y-m-d H:i');
        }

        return(string)$value;
    }

    private function label(?string $value): string
    {
        return ucwords(
            str_replace('_',' ',(string)$value)
        );
    }
}