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
use Illuminate\Support\Facades\DB;

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

        $memberSummary=$members
            ->selectRaw("
                COUNT(*) total,
                SUM(status='active') active,
                SUM(status='pending') pending,
                SUM(status='inactive') inactive,
                SUM(status='suspended') suspended,
                SUM(status='rejected') rejected
            ")
            ->first();

        $investmentSummary=$investments
            ->selectRaw("
                COUNT(*) total,
                COALESCE(SUM(amount),0) amount,
                COALESCE(SUM(expected_return),0) expected_return,
                SUM(status='pending') pending,
                SUM(status='active') active,
                SUM(status='completed') completed,
                SUM(status='cancelled') cancelled
            ")
            ->first();

        $landSummary=$lands
            ->selectRaw("
                COUNT(*) total,
                SUM(status='planned') planned,
                SUM(status='negotiating') negotiating,
                SUM(status='purchased') purchased,
                SUM(status='sold') sold,
                SUM(status='cancelled') cancelled,
                COALESCE(SUM(purchase_price),0) purchase_value
            ")
            ->first();

        $projectSummary=$projects
            ->selectRaw("
                COUNT(*) total,
                SUM(status='planned') planned,
                SUM(status='active') active,
                SUM(status='on_hold') on_hold,
                SUM(status='completed') completed,
                SUM(status='cancelled') cancelled,
                COALESCE(SUM(budget),0) budget,
                COALESCE(SUM(actual_cost),0) cost
            ")
            ->first();

        $now=now();

        $pollSummary=$polls
            ->selectRaw("
                COUNT(*) total,
                SUM(
                    is_active=1
                    AND start_at<=?
                    AND end_at>=?
                ) active,
                SUM(
                    is_active=1
                    AND start_at>?
                ) upcoming,
                SUM(end_at<?) ended,
                SUM(is_active=0) inactive
            ",[
                $now,
                $now,
                $now,
                $now,
            ])
            ->first();

        $noticeSummary=$notices
            ->selectRaw("
                COUNT(*) total,
                SUM(is_published=1) published,
                SUM(is_published=0) draft,
                SUM(priority='urgent') urgent,
                SUM(priority='high') high
            ")
            ->first();

        $voteQuery=DB::table('poll_votes as pv')
            ->join(
                'polls as p',
                'p.id',
                '=',
                'pv.poll_id'
            );

        if($from){
            $voteQuery->where('p.created_at','>=',$from.' 00:00:00');
        }

        if($to){
            $voteQuery->where('p.created_at','<=',$to.' 23:59:59');
        }

        $votes=(int)$voteQuery->count();

        $financeSummary=DB::table('transactions as t')
            ->leftJoin(
                'transaction_entries as te',
                'te.transaction_id',
                '=',
                't.id'
            )
            ->where('t.status','posted')
            ->when(
                $from,
                fn($q)=>$q->where(
                    't.transaction_date',
                    '>=',
                    $from
                )
            )
            ->when(
                $to,
                fn($q)=>$q->where(
                    't.transaction_date',
                    '<=',
                    $to
                )
            )
            ->selectRaw("
                COUNT(DISTINCT t.id) transactions,
                COALESCE(
                    SUM(
                        CASE
                            WHEN t.type='income'
                            THEN te.credit
                            ELSE 0
                        END
                    ),
                    0
                ) income,
                COALESCE(
                    SUM(
                        CASE
                            WHEN t.type='expense'
                            THEN te.debit
                            ELSE 0
                        END
                    ),
                    0
                ) expense
            ")
            ->first();

        $income=(float)($financeSummary->income??0);
        $expense=(float)($financeSummary->expense??0);

        return[
            'members'=>[
                'total'=>(int)($memberSummary->total??0),
                'active'=>(int)($memberSummary->active??0),
                'pending'=>(int)($memberSummary->pending??0),
                'inactive'=>(int)($memberSummary->inactive??0),
                'suspended'=>(int)($memberSummary->suspended??0),
                'rejected'=>(int)($memberSummary->rejected??0),
            ],

            'investments'=>[
                'total'=>(int)($investmentSummary->total??0),
                'amount'=>(float)($investmentSummary->amount??0),
                'expected_return'=>(float)($investmentSummary->expected_return??0),
                'pending'=>(int)($investmentSummary->pending??0),
                'active'=>(int)($investmentSummary->active??0),
                'completed'=>(int)($investmentSummary->completed??0),
                'cancelled'=>(int)($investmentSummary->cancelled??0),
            ],

            'land'=>[
                'total'=>(int)($landSummary->total??0),
                'planned'=>(int)($landSummary->planned??0),
                'negotiating'=>(int)($landSummary->negotiating??0),
                'purchased'=>(int)($landSummary->purchased??0),
                'sold'=>(int)($landSummary->sold??0),
                'cancelled'=>(int)($landSummary->cancelled??0),
                'purchase_value'=>(float)($landSummary->purchase_value??0),
            ],

            'projects'=>[
                'total'=>(int)($projectSummary->total??0),
                'planned'=>(int)($projectSummary->planned??0),
                'active'=>(int)($projectSummary->active??0),
                'on_hold'=>(int)($projectSummary->on_hold??0),
                'completed'=>(int)($projectSummary->completed??0),
                'cancelled'=>(int)($projectSummary->cancelled??0),
                'budget'=>(float)($projectSummary->budget??0),
                'cost'=>(float)($projectSummary->cost??0),
            ],

            'polls'=>[
                'total'=>(int)($pollSummary->total??0),
                'active'=>(int)($pollSummary->active??0),
                'upcoming'=>(int)($pollSummary->upcoming??0),
                'ended'=>(int)($pollSummary->ended??0),
                'inactive'=>(int)($pollSummary->inactive??0),
                'votes'=>$votes,
            ],

            'notices'=>[
                'total'=>(int)($noticeSummary->total??0),
                'published'=>(int)($noticeSummary->published??0),
                'draft'=>(int)($noticeSummary->draft??0),
                'urgent'=>(int)($noticeSummary->urgent??0),
                'high'=>(int)($noticeSummary->high??0),
            ],

            'finance'=>[
                'transactions'=>(int)($financeSummary->transactions??0),
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
            ->with(['member.user'])
            ->withSum(
                [
                    'returns as paid_return_sum'=>fn($query)=>
                        $query->where('status','paid')
                ],
                'amount'
            )
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
                $paidReturn=(float)($investment->paid_return_sum??0);

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

    private function dateFilter(
        Builder $query,
        string $column,
        ?string $from,
        ?string $to
    ): void{
        if($from)$query->where($column,'>=',$from);
        if($to)$query->where($column,'<=',$to);
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