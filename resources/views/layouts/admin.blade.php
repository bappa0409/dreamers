<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>@yield('title','Dashboard') | {{ setting('organization_name','Dreamers Association') }}</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @if(setting('site_favicon'))
    <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        html,
        body {
            height: 100%
        }

        body {
            overflow: hidden
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, .35);
            border-radius: 999px
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, .55)
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 5px
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 999px
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .22)
        }

        .company-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none
        }

        .menu-link {
            position: relative
        }

        .menu-link.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 7px;
            bottom: 7px;
            width: 3px;
            border-radius: 0 999px 999px 0;
            background: #38bdf8
        }
    </style>
    @stack('styles')
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    @php
    $authUser=auth()->user();
    $can=static fn(string $permission):bool=>$authUser?$authUser->hasPermission($permission):false;

    $organizationName=setting('organization_name','Dreamers Association');
    $siteLogo=setting('site_logo');
    $nameParts=preg_split('/\s+/',trim($organizationName),2);
    $primaryName=$nameParts[0]??$organizationName;
    $secondaryName=$nameParts[1]??'';

    $linkClass='menu-link group flex items-center gap-2.5 rounded-md px-2.5 py-1 text-[12.5px] font-medium
    transition-all';
    $inactiveClass='text-sky-100/75 hover:bg-white/[0.07] hover:text-white';
    $activeClass='active bg-sky-400/15 text-white';
    $iconClass='flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white/[0.05] text-sky-100/65';
    $sectionClass='mb-1 mt-3 px-2.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-sky-200/40';

    $membership=[
    ['Member.view','admin.members','admin.members*','bi-people','Members'],
    ['Nominee.view','admin.nominees','admin.nominees*','bi-person-heart','Nominees'],
    ['MemberExit.view','admin.member-exits','admin.member-exits*','bi-box-arrow-right','Member Exit'],
    ];

    $financeAssets=[
    ['Loan.view','admin.loans','admin.loans*','bi-bank','Loans'],
    ['Welfare.view','admin.welfare','admin.welfare*','bi-heart-pulse','Welfare Fund'],
    ['Investment.view','admin.investments','admin.investments*','bi-graph-up-arrow','Investments'],
    ['Land.view','admin.land','admin.land*','bi-buildings','Land Management'],
    ];

    $operations=[
    ['Project.view','admin.projects','admin.projects*','bi-kanban','Projects'],
    ['Tour.view','admin.tours','admin.tours*','bi-bus-front','Tours'],
    ];

    $governance=[
    ['Committee.view','admin.committees','admin.committees*','bi-person-badge','Committee & Election'],
    ['Meeting.view','admin.meetings','admin.meetings*','bi-calendar2-event','Meetings'],
    ['Poll.view','admin.polls','admin.polls*','bi-ui-checks-grid','Polls'],
    ];

    $communication=[
    ['Notice.view','admin.notices','admin.notices*','bi-megaphone','Notices'],
    ['Mailing.view','admin.mailing','admin.mailing*','bi-envelope','Mailing'],
    ['FeedbackSupport.view','admin.feedback-support','admin.feedback-support*','bi-headset','Feedback & Support'],
    ];

    $system=[
    ['Document.view','admin.documents','admin.documents*','bi-file-earmark-text','Documents'],
    ['Approval.view','admin.approvals','admin.approvals*','bi-check2-square','Approvals'],
    ['User.view','admin.users','admin.users*','bi-people','Users'],
    ['Role.view','admin.roles','admin.roles*','bi-shield-lock','Roles & Permissions'],
    ['Role.view','admin.user-roles','admin.user-roles*','bi-person-gear','User Role Assignment'],
    ['Setting.view','admin.landing-page','admin.landing-page*','bi-window-stack','Website Page'],
    ['Setting.view','admin.settings','admin.settings*','bi-gear','Settings'],
    ['Audit.view','admin.activity-logs','admin.activity-logs*','bi-clock-history','Audit Logs'],
    ['Backup.view','admin.backups','admin.backups*','bi-hdd-network','Database Backup'],
    ];

    $financeItems=[
    ['admin.finance','admin.finance','bi-speedometer2','Finance Dashboard'],
    ['admin.incomes','admin.incomes*','bi-arrow-down-circle','Incomes'],
    ['admin.expenses','admin.expenses*','bi-arrow-up-circle','Expenses'],
    ['admin.finance.subscription-payments','admin.finance.subscription-payments*','bi-cash-stack','Subscription
    Payments'],
    ['admin.finance.share-purchases','admin.finance.share-purchases*','bi-layers','Share Purchases'],
    ['admin.charges','admin.charges*','bi-receipt','Charges'],
    ['admin.assets','admin.assets*','bi-box-seam','Assets'],
    ['admin.finance.accounts','admin.finance.accounts*','bi-diagram-3','Chart of Accounts'],
    ['admin.finance.journals','admin.finance.journals*','bi-journal-text','Journal Entries'],
    ['admin.finance.ledger','admin.finance.ledger*','bi-journal-bookmark','General Ledger'],
    ['admin.finance.trial-balance','admin.finance.trial-balance*','bi-bar-chart-steps','Trial Balance'],
    ['admin.finance.balance-sheet','admin.finance.balance-sheet*','bi-table','Balance Sheet'],
    ['admin.finance.profit-loss','admin.finance.profit-loss*','bi-graph-up','Profit & Loss'],
    ];

    $financeOpen=request()->routeIs(
    'admin.finance*',
    'admin.incomes*',
    'admin.expenses*',
    'admin.charges*',
    'admin.assets*'
    );

    $renderable=fn(array $items)=>collect($items)->filter(fn($item)=>$can($item[0]));

    $membershipItems=$renderable($membership);
    $financeAssetItems=$renderable($financeAssets);
    $operationItems=$renderable($operations);
    $governanceItems=$renderable($governance);
    $communicationItems=$renderable($communication);
    $systemItems=$renderable($system);

    $roleNames=$authUser?->isSystemAnalyst()
    ?'System Analyst'
    :collect($authUser?->cachedRoleNames()??[])
    ->map(fn($role)=>str($role)->replace('_',' ')->title())
    ->join(', ');

    $unreadNotificationCount=$can('Notification.view')
    ?app(\App\Services\NotificationService::class)->unreadCount($authUser)
    :0;
    @endphp

    <div id="app" class="flex h-screen overflow-hidden">

        <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"
            onclick="closeSidebar()"></div>

        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-[250px] -translate-x-full flex-col bg-gradient-to-b from-slate-950 via-[#0d3b66] to-[#0a2f52] shadow-2xl transition-transform duration-300 ease-in-out lg:static lg:translate-x-0">

            <div class="flex h-[76px] shrink-0 items-center border-b border-white/[0.08] px-4">
                <a href="{{ route('dashboard') }}" class="flex w-full items-center gap-3 focus:outline-none">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-md">
                        @if($siteLogo)
                        <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $organizationName }}"
                            class="h-full w-full object-contain">
                        @else
                        <div
                            class="flex h-full w-full items-center justify-center bg-white text-lg font-black uppercase text-slate-950">
                            {{ mb_substr($organizationName,0,1) }}</div>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div
                            class="truncate text-[17px] font-bold uppercase leading-tight tracking-[0.06em] text-white">
                            {{ $primaryName }}</div>
                        @if($secondaryName)
                        <div
                            class="mt-0.5 truncate text-[11px] font-medium uppercase tracking-[0.12em] text-sky-200/80">
                            {{ $secondaryName }}</div>
                        @endif
                    </div>
                </a>
            </div>

            <div class="sidebar-scroll flex-1 overflow-y-auto px-3 py-3">

                <nav class="space-y-0.5">
                    @php($active=request()->routeIs('dashboard'))
                    <a href="{{ route('dashboard') }}"
                        class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi bi-speedometer2 text-[14px]"></i></span>
                        <span>Dashboard</span>
                    </a>
                </nav>

                @if($membershipItems->isNotEmpty())
                <div class="{{ $sectionClass }}">Membership</div>
                <nav class="space-y-0.5">
                    @foreach($membershipItems as [$permission,$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi {{ $icon }} text-[14px]"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </nav>
                @endif

                @if($can('Finance.view')||$financeAssetItems->isNotEmpty())
                <div class="{{ $sectionClass }}">Finance & Assets</div>
                <nav class="space-y-0.5">

                    @if($can('Finance.view'))
                    <div>
                        <button type="button" onclick="toggleFinanceMenu()"
                            class="{{ $linkClass }} w-full {{ $financeOpen?$activeClass:$inactiveClass }}">
                            <span class="{{ $iconClass }}"><i class="bi bi-cash-coin text-[14px]"></i></span>
                            <span class="flex-1 text-left">Finance</span>
                            <i id="financeMenuArrow"
                                class="bi bi-chevron-down text-[10px] transition-transform duration-200 {{ $financeOpen?'rotate-180':'' }}"></i>
                        </button>

                        <div id="financeSubmenu"
                            class="ml-[24px] border-l border-white/10 pl-3 {{ $financeOpen?'':'hidden' }}">
                            @foreach($financeItems as [$route,$match,$icon,$label])
                            @php($active=request()->routeIs($match))
                            <a href="{{ route($route) }}"
                                class="flex items-center gap-2 rounded-md px-2 py-1 text-[11.5px] transition {{ $active?'bg-white/[0.08] font-semibold text-sky-300':'text-sky-100/60 hover:bg-white/[0.06] hover:text-white' }}">
                                <i class="bi {{ $icon }} w-4 text-center text-[11px]"></i>
                                <span>{{ $label }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @foreach($financeAssetItems as [$permission,$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi {{ $icon }} text-[14px]"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach

                </nav>
                @endif

                @if($operationItems->isNotEmpty())
                <div class="{{ $sectionClass }}">Operations</div>
                <nav class="space-y-0.5">
                    @foreach($operationItems as [$permission,$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi {{ $icon }} text-[14px]"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </nav>
                @endif

                @if($governanceItems->isNotEmpty())
                <div class="{{ $sectionClass }}">Governance</div>
                <nav class="space-y-0.5">
                    @foreach($governanceItems as [$permission,$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi {{ $icon }} text-[14px]"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </nav>
                @endif

                @if($communicationItems->isNotEmpty())
                <div class="{{ $sectionClass }}">Communication & Support</div>
                <nav class="space-y-0.5">
                    @foreach($communicationItems as [$permission,$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi {{ $icon }} text-[14px]"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </nav>
                @endif

                @if($can('Report.view'))
                <div class="{{ $sectionClass }}">Reports</div>
                <nav class="space-y-0.5">
                    @php($active=request()->routeIs('admin.reports*'))
                    <a href="{{ route('admin.reports') }}"
                        class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi bi-bar-chart-line text-[14px]"></i></span>
                        <span>Reports</span>
                    </a>
                </nav>
                @endif

                @if($systemItems->isNotEmpty())
                <div class="my-3 border-t border-white/[0.07]"></div>
                <div class="mb-1 px-2.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-sky-200/40">System
                </div>
                <nav class="space-y-0.5">
                    @foreach($systemItems as [$permission,$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$activeClass:$inactiveClass }}">
                        <span class="{{ $iconClass }}"><i class="bi {{ $icon }} text-[14px]"></i></span>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </nav>
                @endif

            </div>

            <div class="shrink-0 border-t border-white/[0.08] p-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/10 text-xs font-bold text-white">
                        {{ strtoupper(substr($authUser?->name??'U',0,1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[12px] font-semibold text-white">{{ $authUser?->name??'User' }}</div>
                        <div class="mt-0.5 truncate text-[10px] text-sky-200/60">{{ $roleNames }}</div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Logout"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-sky-100/60 transition hover:bg-white/10 hover:text-white">
                            <i class="bi bi-box-arrow-right text-[15px]"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">

            <header
                class="z-30 h-[64px] shrink-0 border-b border-slate-200 bg-white shadow-[0_1px_3px_rgba(16,24,40,0.06)]">
                <div class="flex h-full items-center justify-between px-4 sm:px-5 lg:px-6">

                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" onclick="openSidebar()"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 lg:hidden"
                            aria-label="Open menu">
                            <i class="bi bi-list text-lg"></i>
                        </button>

                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-gradient-to-br from-[#145da0] to-sky-400"></span>
                                <h1 class="truncate text-[15px] font-semibold tracking-wide text-slate-800 sm:text-[16px]">
                                    @yield('page_title','Dashboard')
                                </h1>
                            </div>

                            <a href="{{ route('member.dashboard') }}"
                                class="hidden shrink-0 items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-[11px] font-semibold text-sky-700 transition hover:border-sky-300 hover:bg-sky-100 sm:inline-flex">
                                <i class="bi bi-person-circle"></i>
                                <span>Member Dashboard</span>
                            </a>
                            @if($authUser?->isSystemAnalyst())
                            <button id="refreshCacheBtn"
                                type="button"
                                onclick="refreshSystemCache()"
                                class="hidden shrink-0 items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1.5 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-60 sm:inline-flex">
                                <i id="refreshCacheIcon" class="bi bi-arrow-repeat"></i>
                                <span id="refreshCacheText">Refresh Cache</span>
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="relative flex shrink-0 items-center gap-2 sm:gap-3">

                        @if($can('Notification.view'))
                        <a href="{{ route('admin.notifications') }}"
                            class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            title="Notifications">
                            <i class="bi bi-bell text-[16px]"></i>
                            <span id="notificationBadge"
                                class="{{ $unreadNotificationCount>0?'':'hidden' }} absolute right-0.5 top-0.5 min-w-[16px] rounded-full bg-red-500 px-1 text-center text-[9px] font-bold leading-4 text-white">
                                {{ $unreadNotificationCount>99?'99+':$unreadNotificationCount }}
                            </span>
                        </a>
                        @endif

                        <div class="hidden h-7 w-px bg-slate-200 sm:block"></div>

                        <button type="button" onclick="toggleUserMenu(event)"
                            class="group flex items-center gap-2 rounded-full py-1 pl-1 pr-1.5 transition hover:bg-slate-50">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#4680b7] to-[#145da0] text-[11px] font-bold text-white">
                                {{ strtoupper(substr($authUser?->name??'U',0,1)) }}
                            </div>

                            <div class="hidden text-left sm:block">
                                <div class="max-w-[130px] truncate text-xs font-semibold text-slate-700">{{
                                    $authUser?->name??'User' }}</div>
                                <div class="max-w-[130px] truncate text-[10px] text-slate-400">{{ $authUser?->email??''
                                    }}</div>
                            </div>

                            <i
                                class="bi bi-chevron-down hidden text-[10px] text-slate-400 transition group-hover:text-slate-600 sm:block"></i>
                        </button>

                        <div id="userDropdown"
                            class="absolute right-0 top-[48px] z-50 hidden w-60 overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <div class="text-[11px] text-slate-400">Signed in as</div>
                                <div class="mt-1 truncate text-sm font-semibold text-slate-700">{{
                                    $authUser?->name??'User' }}</div>
                                <div class="mt-0.5 truncate text-[11px] text-slate-400">{{ $authUser?->email??'' }}
                                </div>
                                <div class="mt-1 truncate text-[10px] font-medium text-sky-600">{{ $roleNames }}</div>
                            </div>

                            <div class="p-1.5">
                                @if(Route::has('member.profile'))
                                <a href="{{ route('member.profile') }}"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-left text-xs font-medium text-slate-600 transition hover:bg-slate-50">
                                    <i class="bi bi-person text-sm"></i>
                                    <span>My Profile</span>
                                </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-left text-xs font-medium text-red-500 transition hover:bg-red-50">
                                        <i class="bi bi-box-arrow-right text-sm"></i>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </header>

            <main class="custom-scrollbar flex-1 overflow-y-auto bg-[#f4f6f9]">
                <div class="min-h-full p-4 sm:p-5 lg:p-6">

                    @if(session('success'))
                    <div
                        class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <div class="font-semibold">Please check the following:</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    <script>
       async function refreshSystemCache(){
    const btn=document.getElementById('refreshCacheBtn');
    const icon=document.getElementById('refreshCacheIcon');
    const text=document.getElementById('refreshCacheText');

    if(!btn||btn.disabled)return;

    btn.disabled=true;
    icon.className='bi bi-arrow-repeat animate-spin';
    text.textContent='Refreshing...';

    try{
        const response=await api(
            @json(route('admin.system.refresh-cache')),
            {
                method:'POST',
                body:JSON.stringify({})
            }
        );

        Toast.success(
            response?.message??'System cache refreshed successfully.'
        );
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }finally{
        btn.disabled=false;
        icon.className='bi bi-arrow-repeat';
        text.textContent='Refresh Cache';
    }
}

        function openSidebar(){
    const sidebar=document.getElementById('sidebar');
    const overlay=document.getElementById('sidebarOverlay');
    if(!sidebar||!overlay)return;
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeSidebar(){
    const sidebar=document.getElementById('sidebar');
    const overlay=document.getElementById('sidebarOverlay');
    if(!sidebar||!overlay)return;
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function toggleFinanceMenu(){
    const menu=document.getElementById('financeSubmenu');
    const arrow=document.getElementById('financeMenuArrow');
    if(!menu)return;
    const opening=menu.classList.contains('hidden');
    menu.classList.toggle('hidden');
    arrow?.classList.toggle('rotate-180',opening);
}

function toggleUserMenu(event){
    event?.stopPropagation();
    document.getElementById('userDropdown')?.classList.toggle('hidden');
}

document.addEventListener('click',event=>{
    const dropdown=document.getElementById('userDropdown');
    if(!dropdown)return;
    const button=event.target.closest('button[onclick^="toggleUserMenu"]');
    if(!button&&!dropdown.contains(event.target))dropdown.classList.add('hidden');
});

document.addEventListener('keydown',event=>{
    if(event.key!=='Escape')return;
    closeSidebar();
    document.getElementById('userDropdown')?.classList.add('hidden');
});

window.addEventListener('resize',()=>{
    if(window.innerWidth<1024)return;
    document.getElementById('sidebarOverlay')?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
});

document.querySelectorAll('#sidebar a').forEach(link=>{
    link.addEventListener('click',()=>{
        if(window.innerWidth<1024)closeSidebar();
    });
});

@if($can('Notification.view'))
async function loadNotificationCount(){
    const badge=document.getElementById('notificationBadge');
    if(!badge)return;

    const apiFn=window.api??(typeof api==='function'?api:null);
    if(typeof apiFn!=='function')return;

    try{
        const response=await apiFn('/api/notifications/unread-count');
        const count=response?.data?.count??response?.count??0;

        if(count>0){
            badge.textContent=count>99?'99+':count;
            badge.classList.remove('hidden');
        }else{
            badge.classList.add('hidden');
        }
    }catch(error){
        console.error('Notification count error:',error);
    }
}
@endif

document.addEventListener('DOMContentLoaded',()=>{
    @if($can('Notification.view'))
    loadNotificationCount();
    @endif
});
    </script>

    @stack('scripts')
</body>

</html>