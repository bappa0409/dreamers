@extends('layouts.admin')

@section('title','Dashboard')
@section('page_title','Dashboard')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5  md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-speedometer2 text-base"></i>
            </div>

            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">
                    Welcome, {{ $user->name }}
                </h1>

                <p class="text-xs text-slate-500">
                    Dreamers Association overview and recent activities.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($member)
                <span class="rounded-md bg-slate-100 px-2.5 py-1.5 text-sm font-semibold text-slate-600">
                    {{ $member->member_code }}
                </span>
            @endif

            <span class="rounded-md bg-indigo-50 px-2.5 py-1.5 text-sm font-semibold text-indigo-700">
                @if($user->isSystemAnalyst())
                    System Analyst
                @else
                    {{ collect($user->cachedRoleNames())->map(fn($role)=>str($role)->replace('_',' ')->title())->join(', ') }}
                @endif
            </span>
        </div>
    </div>

    {{-- No Approval Workflow Configured --}}
    @if($dashboard['no_approval_workflow'])
        <div class="flex flex-col gap-3 rounded-md border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-amber-800">No approval workflow is set up</p>
                    <p class="mt-0.5 text-xs text-amber-700">
                        Without a workflow, everything (member registration, share purchases, loans, welfare requests, etc.) gets approved automatically — nothing waits for review.
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.approval-workflows') }}" class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-md bg-amber-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-amber-700">
                <i class="bi bi-diagram-3"></i>
                Set Up Workflow
            </a>
        </div>
    @endif

    {{-- Member Stats --}}
    @if($dashboard['members'])
        <section>
            <div class="mb-3 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Members</h2>
                    <p class="mt-0.5 text-sm text-slate-400">Membership overview</p>
                </div>

                <a href="{{ route('admin.members') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                    View Members
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                <div class="rounded-md border border-slate-200 bg-white p-4 ">
                    <p class="text-xs text-slate-500">Total Members</p>
                    <p class="mt-2 text-xl font-bold text-slate-800">
                        {{ $dashboard['members']['total'] }}
                    </p>
                </div>

                <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
                    <p class="text-xs text-emerald-700">Active</p>
                    <p class="mt-2 text-xl font-bold text-emerald-600">
                        {{ $dashboard['members']['active'] }}
                    </p>
                </div>

                <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
                    <p class="text-xs text-amber-700">Pending</p>
                    <p class="mt-2 text-xl font-bold text-amber-600">
                        {{ $dashboard['members']['pending'] }}
                    </p>
                </div>

                <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
                    <p class="text-xs text-red-700">Suspended</p>
                    <p class="mt-2 text-xl font-bold text-red-600">
                        {{ $dashboard['members']['suspended'] }}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs text-slate-500">Inactive</p>
                    <p class="mt-2 text-xl font-bold text-slate-600">
                        {{ $dashboard['members']['inactive'] }}
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- Main Summary --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        @if($dashboard['investments'])
            <div class="rounded-md border border-slate-200 bg-white p-5 ">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Investments</h3>
                        <p class="mt-0.5 text-sm text-slate-400">Investment summary</p>
                    </div>

                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-md bg-slate-50 p-3">
                        <p class="text-[11px] text-slate-400">Total</p>
                        <p class="mt-1 text-xl font-bold text-slate-800">
                            {{ $dashboard['investments']['total'] }}
                        </p>
                    </div>

                    <div class="rounded-md bg-emerald-50 p-3">
                        <p class="text-[11px] text-emerald-600">Active</p>
                        <p class="mt-1 text-xl font-bold text-emerald-600">
                            {{ $dashboard['investments']['active'] }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 rounded-md border border-indigo-100 bg-indigo-50/50 p-3">
                    <p class="text-[11px] text-indigo-500">Total Amount</p>

                    <p class="mt-1 text-lg font-bold text-indigo-700">
                        ৳{{ money($dashboard['investments']['total_amount']) }}
                    </p>
                </div>
            </div>
        @endif

        @if($dashboard['projects'])
            <div class="rounded-md border border-slate-200 bg-white p-5 ">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Projects</h3>
                        <p class="mt-0.5 text-sm text-slate-400">Project overview</p>
                    </div>

                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                        <i class="bi bi-kanban"></i>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="rounded-md bg-slate-50 p-3 text-center">
                        <p class="text-[10px] text-slate-400">Total</p>
                        <p class="mt-1 text-xl font-bold text-slate-800">
                            {{ $dashboard['projects']['total'] }}
                        </p>
                    </div>

                    <div class="rounded-md bg-indigo-50 p-3 text-center">
                        <p class="text-[10px] text-indigo-500">Active</p>
                        <p class="mt-1 text-xl font-bold text-indigo-600">
                            {{ $dashboard['projects']['active'] }}
                        </p>
                    </div>

                    <div class="rounded-md bg-emerald-50 p-3 text-center">
                        <p class="text-[10px] text-emerald-500">Completed</p>
                        <p class="mt-1 text-xl font-bold text-emerald-600">
                            {{ $dashboard['projects']['completed'] }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Approval --}}
    @if($dashboard['approvals'])
        <section class="rounded-md border border-slate-200 bg-white p-5 ">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Approval Workflow</h3>
                    <p class="mt-0.5 text-sm text-slate-400">Approval request status</p>
                </div>

                <a href="{{ route('admin.approvals') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                    View Approvals
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-md bg-amber-50 p-3">
                    <p class="text-[11px] text-amber-600">Pending</p>
                    <p class="mt-1 text-xl font-bold text-amber-600">
                        {{ $dashboard['approvals']['pending'] }}
                    </p>
                </div>

                <div class="rounded-md bg-emerald-50 p-3">
                    <p class="text-[11px] text-emerald-600">Approved</p>
                    <p class="mt-1 text-xl font-bold text-emerald-600">
                        {{ $dashboard['approvals']['approved'] }}
                    </p>
                </div>

                <div class="rounded-md bg-red-50 p-3">
                    <p class="text-[11px] text-red-600">Rejected</p>
                    <p class="mt-1 text-xl font-bold text-red-600">
                        {{ $dashboard['approvals']['rejected'] }}
                    </p>
                </div>

                <div class="rounded-md bg-slate-100 p-3">
                    <p class="text-[11px] text-slate-500">Cancelled</p>
                    <p class="mt-1 text-xl font-bold text-slate-600">
                        {{ $dashboard['approvals']['cancelled'] }}
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- Recent --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        @if(count($dashboard['recent_members']))
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-base font-bold text-slate-800">Recent Members</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($dashboard['recent_members'] as $recentMember)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-slate-700">
                                    {{ data_get($recentMember,'user.name','Member') }}
                                </p>

                                <p class="mt-0.5 text-sm text-slate-400">
                                    {{ $recentMember['member_code'] }}
                                </p>
                            </div>

                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold
                            {{ $recentMember['status']==='active'
                                ?'bg-emerald-50 text-emerald-700'
                                :($recentMember['status']==='pending'
                                    ?'bg-amber-50 text-amber-700'
                                    :'bg-slate-100 text-slate-600') }}">
                                {{ ucfirst($recentMember['status']) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(count($dashboard['recent_approvals']))
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-base font-bold text-slate-800">Recent Approvals</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($dashboard['recent_approvals'] as $approval)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-slate-700">
                                    {{ $approval['module'] }} — {{ $approval['action'] }}
                                </p>

                                <p class="mt-0.5 text-sm text-slate-400">
                                    {{ data_get($approval,'requester.name','System') }}
                                </p>
                            </div>

                            <span class="rounded-full px-2 py-1 text-[10px] font-semibold
                            {{ $approval['status']==='approved'
                                ?'bg-emerald-50 text-emerald-700'
                                :($approval['status']==='pending'
                                    ?'bg-amber-50 text-amber-700'
                                    :($approval['status']==='rejected'
                                        ?'bg-red-50 text-red-700'
                                        :'bg-slate-100 text-slate-600')) }}">
                                {{ ucfirst($approval['status']) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- General Member --}}
    @if(
        !$dashboard['members'] &&
        !$dashboard['approvals'] &&
        !$dashboard['investments'] &&
        !$dashboard['projects']
    )
        <div class="rounded-md border border-slate-200 bg-white p-6 ">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person-circle"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Member Dashboard
                    </h2>

                    <p class="text-xs text-slate-500">
                        Welcome to Dreamers Association. Use the available menu items to access your personal information and association services.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection