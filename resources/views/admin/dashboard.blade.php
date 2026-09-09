@extends('layouts.admin')

@section('title','Dashboard')
@section('page_title','Dashboard')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-speedometer2 text-lg"></i>
            </div>

            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">
                    Welcome, {{ $user->name }}
                </h1>

                <p class="text-xs 2xl:text-sm text-slate-500">
                    {{ setting('organization_name', 'Dreamers Association') }} overview and recent activities.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if($member)
                <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-slate-600">
                    <i class="bi bi-person-badge text-slate-400"></i>
                    {{ $member->member_code }}
                </span>
            @endif

            <span class="inline-flex items-center gap-1.5 rounded-md bg-indigo-50 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-indigo-700">
                <i class="bi bi-shield-check"></i>
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
                    <p class="mt-0.5 text-xs 2xl:text-sm leading-5 text-amber-700">
                        Without a workflow, all requests (member registration, loans, welfare requests, member exits, share purchases, tours, accounts, income, expenses, subscription payments, charges, assets and journal entries) are approved automatically without any review. You can set up a workflow now, or skip it and continue without one.
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.approval-workflows') }}" class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-md bg-amber-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-amber-700">
                <i class="bi bi-diagram-3"></i>
                Set Up Workflow
            </a>
        </div>
    @endif

    {{-- Member Stats --}}
    {{-- @if($dashboard['members'])
        <section>
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Members</h2>
                        <p class="text-xs 2xl:text-sm text-slate-400">Membership overview</p>
                    </div>
                </div>

                <a href="{{ route('admin.members') }}" class="inline-flex w-fit shrink-0 cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    View Members
                    <i class="bi bi-arrow-right text-[11px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                <div class="rounded-md border border-slate-200 bg-white p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs 2xl:text-sm text-slate-500">Total</p>
                        <i class="bi bi-people text-slate-300"></i>
                    </div>
                    <p class="mt-2 text-xl font-bold text-slate-800">
                        {{ $dashboard['members']['total'] }}
                    </p>
                </div>

                <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs 2xl:text-sm text-emerald-700">Active</p>
                        <i class="bi bi-check-circle text-emerald-400"></i>
                    </div>
                    <p class="mt-2 text-xl font-bold text-emerald-600">
                        {{ $dashboard['members']['active'] }}
                    </p>
                </div>

                <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs 2xl:text-sm text-amber-700">Pending</p>
                        <i class="bi bi-hourglass-split text-amber-400"></i>
                    </div>
                    <p class="mt-2 text-xl font-bold text-amber-600">
                        {{ $dashboard['members']['pending'] }}
                    </p>
                </div>

                <div class="rounded-md border border-red-200 bg-red-50/50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs 2xl:text-sm text-red-700">Suspended</p>
                        <i class="bi bi-slash-circle text-red-400"></i>
                    </div>
                    <p class="mt-2 text-xl font-bold text-red-600">
                        {{ $dashboard['members']['suspended'] }}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 bg-slate-50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs 2xl:text-sm text-slate-500">Inactive</p>
                        <i class="bi bi-pause-circle text-slate-400"></i>
                    </div>
                    <p class="mt-2 text-xl font-bold text-slate-600">
                        {{ $dashboard['members']['inactive'] }}
                    </p>
                </div>
            </div>
        </section>
    @endif --}}
    @if($dashboard['members']) 
        <section class="rounded-lg border border-slate-200 bg-white p-5 transition hover:shadow-sm"> 
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"> <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600"> <i class="bi bi-people text-base"></i> </div>

                <div>
                    <h2 class="text-base font-bold tracking-tight text-slate-800">
                        Members
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-400 2xl:text-sm">
                        Membership overview and current status
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.members') }}"
            class="inline-flex w-full shrink-0 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto 2xl:text-sm">
                View Members
                <i class="bi bi-arrow-right text-[11px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
            {{-- Total --}}
            <div class="rounded-md border border-slate-200 bg-slate-50/70 p-4 transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500 2xl:text-sm">
                        Total
                    </p>
                    <i class="bi bi-people text-slate-300"></i>
                </div>

                <p class="mt-2 text-xl font-bold text-slate-800">
                    {{ $dashboard['members']['total'] }}
                </p>
            </div>

            {{-- Active --}}
            <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4 transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-emerald-700 2xl:text-sm">
                        Active
                    </p>
                    <i class="bi bi-check-circle text-emerald-400"></i>
                </div>

                <p class="mt-2 text-xl font-bold text-emerald-600">
                    {{ $dashboard['members']['active'] }}
                </p>
            </div>

            {{-- Pending --}}
            <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4 transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-amber-700 2xl:text-sm">
                        Pending
                    </p>
                    <i class="bi bi-hourglass-split text-amber-400"></i>
                </div>

                <p class="mt-2 text-xl font-bold text-amber-600">
                    {{ $dashboard['members']['pending'] }}
                </p>
            </div>

            {{-- Suspended --}}
            <div class="rounded-md border border-red-200 bg-red-50/50 p-4 transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-red-700 2xl:text-sm">
                        Suspended
                    </p>
                    <i class="bi bi-slash-circle text-red-400"></i>
                </div>

                <p class="mt-2 text-xl font-bold text-red-600">
                    {{ $dashboard['members']['suspended'] }}
                </p>
            </div>

            {{-- Inactive --}}
            <div class="rounded-md border border-slate-200 bg-slate-50 p-4 transition hover:shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-slate-500 2xl:text-sm">
                        Inactive
                    </p>
                    <i class="bi bi-pause-circle text-slate-400"></i>
                </div>

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
            <section class="rounded-lg border border-slate-200 bg-white p-5 transition hover:shadow-sm">

                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="bi bi-graph-up-arrow text-base"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-bold tracking-tight text-slate-800">
                            Investments
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-400 2xl:text-sm">
                            Investment summary
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">

                    {{-- Total --}}
                    <div class="rounded-md border border-slate-200 bg-slate-50/70 p-3 transition hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-500 2xl:text-sm">
                                Total
                            </p>
                            <i class="bi bi-graph-up text-slate-300"></i>
                        </div>

                        <p class="mt-2 text-xl font-bold text-slate-800">
                            {{ $dashboard['investments']['total'] }}
                        </p>
                    </div>

                    {{-- Active --}}
                    <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-3 transition hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-emerald-700 2xl:text-sm">
                                Active
                            </p>
                            <i class="bi bi-check-circle text-emerald-400"></i>
                        </div>

                        <p class="mt-2 text-xl font-bold text-emerald-600">
                            {{ $dashboard['investments']['active'] }}
                        </p>
                    </div>

                    {{-- Amount --}}
                    <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-3 transition hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-indigo-600 2xl:text-sm">
                                Amount
                            </p>
                            <i class="bi bi-cash-stack text-indigo-400"></i>
                        </div>

                        <p class="mt-2 text-lg font-bold text-indigo-700">
                            {{ money($dashboard['investments']['total_amount']) }}
                        </p>
                    </div>

                </div>
            </section>
        @endif

        @if($dashboard['projects'])
            <section class="rounded-lg border border-slate-200 bg-white p-5 transition hover:shadow-sm">

                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                        <i class="bi bi-kanban text-base"></i>
                    </div>

                    <div>
                        <h2 class="text-base font-bold tracking-tight text-slate-800">
                            Projects
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-400 2xl:text-sm">
                            Project overview
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">

                    {{-- Total --}}
                    <div class="rounded-md border border-slate-200 bg-slate-50/70 p-3 transition hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-500 2xl:text-sm">
                                Total
                            </p>
                            <i class="bi bi-kanban text-slate-300"></i>
                        </div>

                        <p class="mt-2 text-xl font-bold text-slate-800">
                            {{ $dashboard['projects']['total'] }}
                        </p>
                    </div>

                    {{-- Active --}}
                    <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-3 transition hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-indigo-600 2xl:text-sm">
                                Active
                            </p>
                            <i class="bi bi-play-circle text-indigo-400"></i>
                        </div>

                        <p class="mt-2 text-xl font-bold text-indigo-600">
                            {{ $dashboard['projects']['active'] }}
                        </p>
                    </div>

                    {{-- Completed --}}
                    <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-3 transition hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-emerald-700 2xl:text-sm">
                                Completed
                            </p>
                            <i class="bi bi-check-circle text-emerald-400"></i>
                        </div>

                        <p class="mt-2 text-xl font-bold text-emerald-600">
                            {{ $dashboard['projects']['completed'] }}
                        </p>
                    </div>

                </div>
            </section>
        @endif
    </div>

    {{-- Approval --}}
    @if($dashboard['approvals'])
        <section class="rounded-lg border border-slate-200 bg-white p-5 transition hover:shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                        <i class="bi bi-diagram-3 text-base"></i>
                    </div>

                    <div>
                        <h3 class="text-base font-bold tracking-tight text-slate-800">
                            Approval Workflow
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-400 2xl:text-sm">
                            Approval request status
                        </p>
                    </div>
                </div>

                <a href="{{ route('admin.approvals') }}"
                class="inline-flex w-fit shrink-0 cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 2xl:text-sm">
                    View Approvals
                    <i class="bi bi-arrow-right text-[11px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                {{-- Pending --}}
                <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-amber-700 2xl:text-sm">
                            Pending
                        </p>
                        <i class="bi bi-clock-history text-amber-400"></i>
                    </div>

                    <p class="mt-2 text-xl font-bold text-amber-600">
                        {{ $dashboard['approvals']['pending'] }}
                    </p>
                </div>

                {{-- Approved --}}
                <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-emerald-700 2xl:text-sm">
                            Approved
                        </p>
                        <i class="bi bi-check-circle text-emerald-400"></i>
                    </div>

                    <p class="mt-2 text-xl font-bold text-emerald-600">
                        {{ $dashboard['approvals']['approved'] }}
                    </p>
                </div>

                {{-- Rejected --}}
                <div class="rounded-md border border-red-200 bg-red-50/50 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-red-700 2xl:text-sm">
                            Rejected
                        </p>
                        <i class="bi bi-x-circle text-red-400"></i>
                    </div>

                    <p class="mt-2 text-xl font-bold text-red-600">
                        {{ $dashboard['approvals']['rejected'] }}
                    </p>
                </div>

                {{-- Cancelled --}}
                <div class="rounded-md border border-slate-200 bg-slate-50/70 p-4 transition hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-slate-500 2xl:text-sm">
                            Cancelled
                        </p>
                        <i class="bi bi-slash-circle text-slate-400"></i>
                    </div>

                    <p class="mt-2 text-xl font-bold text-slate-600">
                        {{ $dashboard['approvals']['cancelled'] }}
                    </p>
                </div>
            </div>
        </section>
    @endif

    {{-- Recent --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        @if(count($dashboard['recent_members']))
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white transition hover:shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Recent Members</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($dashboard['recent_members'] as $recentMember)
                        <div class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                    <i class="bi bi-person"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-700">
                                        {{ data_get($recentMember,'user.name','Member') }}
                                    </p>

                                    <p class="mt-0.5 text-xs 2xl:text-sm text-slate-400">
                                        {{ $recentMember['member_code'] }}
                                    </p>
                                </div>
                            </div>

                            <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-semibold
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
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white transition hover:shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Recent Approvals</h3>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach($dashboard['recent_approvals'] as $approval)
                        <div class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-50 text-sky-600">
                                    <i class="bi bi-diagram-3"></i>
                                </div>

                                <div class="min-w-0">
                                    <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-700">
                                        {{ $approval['module'] }} — {{ $approval['action'] }}
                                    </p>

                                    <p class="mt-0.5 text-xs 2xl:text-sm text-slate-400">
                                        {{ data_get($approval,'requester.name','System') }}
                                    </p>
                                </div>
                            </div>

                            <span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-semibold
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
        <div class="rounded-md border border-slate-200 bg-white p-6">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person-circle"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Member Dashboard
                    </h2>

                    <p class="text-xs 2xl:text-sm text-slate-500">
                        Welcome to {{ setting('organization_name', 'Dreamers Association') }}. Use the available menu items to access your personal information and association services.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>

@endsection