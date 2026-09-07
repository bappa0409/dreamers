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
    $can=static fn(string $permission):bool=>$authUser?$authUser->hasAnyPermission(array_filter(explode(',', $permission))):false;

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
    ['Meeting.view','admin.meetings','admin.meetings*','bi-calendar2-event','Meetings'],
    ['Poll.view','admin.polls','admin.polls*','bi-ui-checks-grid','Polls'],
    ];

    $communication=[
    ['Notice.view','admin.notices','admin.notices*','bi-megaphone','Notices'],
    ['Mail.view,Mailing.view','admin.mailing','admin.mailing*','bi-envelope','Mailing'],
    ['FeedbackSupport.view','admin.feedback-support','admin.feedback-support*','bi-headset','Feedback & Support'],
    ['ContactMessage.view','admin.contact-messages','admin.contact-messages*','bi-chat-square-text','Contact Messages'],
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
    ['admin.finance.subscription-plans','admin.finance.subscription-plans*','bi-card-checklist','Subscription Plans'],
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
    ['admin.finance.manual-guide','admin.finance.manual-guide*','bi-book','Accounts Guide'],
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

    $unreadNotificationCount=$authUser
    ?app(\App\Services\NotificationService::class)->unreadCount($authUser)
    :0;

    $canViewContactMessages=$can('ContactMessage.view');

    $unreadContactMessagesCount=$canViewContactMessages
    ?\App\Models\ContactMessage::unread()->count()
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
                            class="ml-[24px] border-l border-white/10 pl-2 pt-2 {{ $financeOpen?'':'hidden' }}">
                            @foreach($financeItems as [$route,$match,$icon,$label])
                            @php($active=request()->routeIs($match))
                            <a href="{{ route($route) }}"
                                class="flex items-center gap-2 rounded-md px-2 py-1 text-[11.5px] transition {{ $active?'bg-white/[0.08] font-semibold text-sky-300':'text-sky-100/60 hover:bg-white/[0.06] hover:text-white' }}">
                                <i class="bi {{ $icon }} w-4 text-center text-[11px]"></i>
                                <span>{{ $label }}</span>
                            </a>
                            @endforeach

                            {{-- <a href="{{ route($route) }}"
                            class="flex items-center gap-2 rounded-md px-2 py-1 text-[11.5px] transition text-sky-100/60 hover:bg-white/[0.06] hover:text-white">
                                <i class="bi bi-book w-4 text-center text-[11px]"></i>
                                <span>Accounts Guide</span>
                            </a> --}}
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
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/10 text-sm font-bold text-white">
                        {{ strtoupper(substr($authUser?->name??'U',0,1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[12px] font-semibold text-white">{{ $authUser?->name??'User' }}</div>
                        <div class="mt-0.5 truncate text-[10px] text-sky-200/60">{{ $roleNames }}</div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" novalidate data-js-validation="1">
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

                    <div class="relative flex shrink-0 items-center gap-3 sm:gap-4">

                        <div class="flex items-center gap-0.5">
                            <button type="button" onclick="toggleNotificationMenu(event)"
                                class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                title="Notifications">
                                <i class="bi bi-bell text-[16px]"></i>
                                <span id="notificationBadge"
                                    class="{{ $unreadNotificationCount>0?'':'hidden' }} absolute right-0.5 top-0.5 min-w-[16px] rounded-full bg-red-500 px-1 text-center text-[9px] font-bold leading-4 text-white">
                                    {{ $unreadNotificationCount>99?'99+':$unreadNotificationCount }}
                                </span>
                            </button>

                            @if($canViewContactMessages&&Route::has('admin.contact-messages'))
                            <a href="{{ route('admin.contact-messages') }}"
                                class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                title="Contact Messages">
                                <i class="bi bi-chat-square-text text-[16px]"></i>
                                <span id="contactMessageBadge"
                                    class="{{ $unreadContactMessagesCount>0?'':'hidden' }} absolute right-0.5 top-0.5 min-w-[16px] rounded-full bg-red-500 px-1 text-center text-[9px] font-bold leading-4 text-white">
                                    {{ $unreadContactMessagesCount>99?'99+':$unreadContactMessagesCount }}
                                </span>
                            </a>
                            @endif
                        </div>

                        <div id="notificationDropdown"
                            class="absolute right-12 top-[48px] z-50 hidden w-[340px] max-w-[85vw] overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">

                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-700">Notifications</h3>
                                    <p id="notificationUnreadText" class="mt-0.5 text-[10px] text-slate-400">
                                        {{ $unreadNotificationCount>0?$unreadNotificationCount.' unread notification'.($unreadNotificationCount===1?'':'s'):'No unread notifications' }}
                                    </p>
                                </div>

                                <button id="notificationMarkAllButton" type="button"
                                    onclick="event.stopPropagation();markAllNotificationsRead()"
                                    {{ $unreadNotificationCount<=0?'disabled':'' }}
                                    class="text-[10px] font-semibold text-sky-600 transition hover:text-sky-700 disabled:cursor-not-allowed disabled:opacity-40">
                                    Mark all read
                                </button>
                            </div>

                            <div id="notificationList" class="max-h-[360px] overflow-y-auto">
                                <div class="p-8 text-center">
                                    <div class="mx-auto h-6 w-6 animate-spin rounded-full border-4 border-slate-200 border-t-sky-600"></div>
                                    <p class="mt-2 text-[10px] text-slate-400">Loading notifications...</p>
                                </div>
                            </div>

                            @if(Route::has('admin.notifications')&&$can('Notification.view'))
                            <div class="border-t border-slate-100 bg-slate-50/60 p-2">
                                <a href="{{ route('admin.notifications') }}"
                                    class="flex h-9 w-full items-center justify-center gap-2 rounded-md text-[11px] font-semibold text-sky-600 transition hover:bg-sky-50 hover:text-sky-700">
                                    View All Notifications
                                    <i class="bi bi-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                            @endif

                        </div>

                        <div class="hidden h-7 w-px bg-slate-200 sm:block"></div>

                        <button type="button" onclick="toggleUserMenu(event)"
                            class="group flex items-center gap-2 rounded-full py-1 pl-1 pr-1.5 transition hover:bg-slate-50">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-[#4680b7] to-[#145da0] text-[11px] font-bold text-white">
                                {{ strtoupper(substr($authUser?->name??'U',0,1)) }}
                            </div>

                            <div class="hidden text-left sm:block">
                                <div class="max-w-[130px] truncate text-sm font-semibold text-slate-700">{{
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
                                <div class="mt-1 truncate text-base font-semibold text-slate-700">{{
                                    $authUser?->name??'User' }}</div>
                                <div class="mt-0.5 truncate text-[11px] text-slate-400">{{ $authUser?->email??'' }}
                                </div>
                                <div class="mt-1 truncate text-[10px] font-medium text-sky-600">{{ $roleNames }}</div>
                            </div>

                            <div class="p-1.5">
                                @if(Route::has('member.profile'))
                                <a href="{{ route('member.profile') }}"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-left text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                                    <i class="bi bi-person text-base"></i>
                                    <span>My Profile</span>
                                </a>
                                @endif

                                <form method="POST" action="{{ route('logout') }}" novalidate data-js-validation="1">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-left text-sm font-medium text-red-500 transition hover:bg-red-50">
                                        <i class="bi bi-box-arrow-right text-base"></i>
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
                        class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-700">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-base text-red-700">
                        {{ session('error') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-base text-red-700">
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

            <footer class="relative z-10 shrink-0 border-t border-slate-200 bg-white p-4 sm:px-5 lg:px-6">
                <div class="flex flex-col items-center justify-between text-[11px] text-slate-400 sm:flex-row">
                    <p>&copy; {{ date('Y') }} {{ $organizationName }}. All rights reserved.</p>
                    <p>
                        Developed by
                        <a href="https://www.facebook.com/bappa040976"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold text-slate-500 transition-colors hover:text-blue-600">
                            Bappa Sutradhar
                        </a>
                    </p>
                </div>
            </footer>
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
        const response=await api(@json(route('admin.system.refresh-cache')),
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
    closeHeaderDropdowns('userDropdown');
    document.getElementById('userDropdown')?.classList.toggle('hidden');
}

function closeHeaderDropdowns(except=null){
    ['userDropdown','notificationDropdown'].forEach(id=>{
        if(id!==except)document.getElementById(id)?.classList.add('hidden');
    });
}

document.addEventListener('click',event=>{
    const userDropdown=document.getElementById('userDropdown');
    const notifDropdown=document.getElementById('notificationDropdown');

    if(userDropdown&&!userDropdown.contains(event.target)){
        const userButton=event.target.closest('button[onclick^="toggleUserMenu"]');
        if(!userButton)userDropdown.classList.add('hidden');
    }

    if(notifDropdown&&!notifDropdown.contains(event.target)){
        const notifButton=event.target.closest('button[onclick^="toggleNotificationMenu"]');
        if(!notifButton)notifDropdown.classList.add('hidden');
    }
});

document.addEventListener('keydown',event=>{
    if(event.key!=='Escape')return;
    closeSidebar();
    document.getElementById('userDropdown')?.classList.add('hidden');
    document.getElementById('notificationDropdown')?.classList.add('hidden');
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

const notificationState={
    unread:@json($unreadNotificationCount),
    loaded:false,
    loading:null
};

function toggleNotificationMenu(event){
    event.stopPropagation();
    closeHeaderDropdowns('notificationDropdown');

    const menu=document.getElementById('notificationDropdown');
    if(!menu)return;

    menu.classList.toggle('hidden');

    if(!menu.classList.contains('hidden')){
        loadNotifications();
    }
}

function updateNotificationBadges(count){
    count=Math.max(0,Number(count??0));
    notificationState.unread=count;

    const display=count>99?'99+':String(count);
    const badge=document.getElementById('notificationBadge');
    const unreadText=document.getElementById('notificationUnreadText');
    const markAllButton=document.getElementById('notificationMarkAllButton');

    if(badge){
        badge.textContent=display;
        badge.classList.toggle('hidden',count===0);
    }

    if(unreadText){
        unreadText.textContent=count
            ?`${count} unread notification${count===1?'':'s'}`
            :'No unread notifications';
    }

    if(markAllButton){
        markAllButton.disabled=count===0;
    }
}

function getNotificationUrl(notification){
    const raw=
        notification?.url??
        notification?.action_url??
        notification?.data?.url??
        notification?.data?.action_url??
        null;

    if(!raw)return null;

    const value=String(raw).trim();

    if(value.startsWith('/')&&!value.startsWith('//')){
        return value;
    }

    try{
        const url=new URL(value,window.location.origin);

        return url.origin===window.location.origin
            ?url.pathname+url.search+url.hash
            :null;
    }catch{
        return null;
    }
}

function renderNotificationItems(notifications){
    const list=document.getElementById('notificationList');
    if(!list)return;

    if(!notifications.length){
        list.innerHTML=`
            <div class="p-8 text-center">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                    <i class="bi bi-bell"></i>
                </div>
                <p class="mt-3 text-sm font-semibold text-slate-600">No notifications</p>
                <p class="mt-1 text-[10px] text-slate-400">You're all caught up.</p>
            </div>`;
        return;
    }

    list.innerHTML=notifications.map(notification=>{
        const unread=!notification.read_at;
        const title=
            notification.title??
            notification.data?.title??
            'Notification';
        const message=
            notification.message??
            notification.data?.message??
            '';

        return `
        <button type="button"
            onclick="openNotification('${notification.id}',${unread?'true':'false'})"
            class="relative flex w-full gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 ${unread?'bg-sky-50/40 hover:bg-sky-50/70':'hover:bg-slate-50'}">

            ${unread?`<span class="absolute inset-y-0 left-0 w-[2px] bg-sky-500"></span>`:''}

            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-bell"></i>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <div class="truncate text-sm font-semibold ${unread?'text-slate-800':'text-slate-700'}">
                        ${AdminUI.escapeHtml(title)}
                    </div>
                    ${unread?`<span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>`:''}
                </div>

                <div class="mt-1 line-clamp-2 text-[10px] leading-4 text-slate-400">
                    ${AdminUI.escapeHtml(message)}
                </div>

                <div class="mt-1.5 text-[9px] text-slate-400">
                    <i class="bi bi-clock me-1"></i>
                    ${AdminUI.formatDate(notification.created_at,true)}
                </div>
            </div>
        </button>`;
    }).join('');
}

async function loadNotifications(force=false){
    const list=document.getElementById('notificationList');
    if(!list)return;

    if(notificationState.loaded&&!force)return;

    if(notificationState.loading){
        return notificationState.loading;
    }

    list.innerHTML=`
        <div class="p-8 text-center">
            <div class="mx-auto h-6 w-6 animate-spin rounded-full border-4 border-slate-200 border-t-sky-600"></div>
            <p class="mt-2 text-[10px] text-slate-400">Loading notifications...</p>
        </div>`;

    notificationState.loading=(async()=>{
        try{
            const response=await api('/api/notifications?per_page=8');

            const payload=
                response.data?.data??
                response.data??
                [];

            renderNotificationItems(Array.isArray(payload)?payload:[]);

            notificationState.loaded=true;

        }catch(error){
            console.error('Notification list error:',error);

            list.innerHTML=`
                <div class="p-7 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-500">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <p class="mt-2 text-[11px] font-semibold text-red-600">Failed to load notifications</p>
                    <button type="button" onclick="loadNotifications(true)"
                        class="mt-3 text-[10px] font-semibold text-sky-600">Try Again</button>
                </div>`;
        }finally{
            notificationState.loading=null;
        }
    })();

    return notificationState.loading;
}

async function openNotification(id,wasUnread){
    try{
        const response=await api(
            `/api/notifications/${id}/read`,
            {method:'PATCH'}
        );

        if(wasUnread){
            updateNotificationBadges(notificationState.unread-1);
        }

        notificationState.loaded=false;

        const url=getNotificationUrl(response.data??null);

        if(url){
            window.location.href=url;
        }else{
            await loadNotifications(true);
        }

    }catch(error){
        console.error('Open notification error:',error);

        window.Toast?.error(
            AdminUI.extractError(error)
        );
    }
}

async function markAllNotificationsRead(){
    const button=document.getElementById('notificationMarkAllButton');
    if(!button||button.disabled)return;

    const original=button.innerHTML;
    button.disabled=true;
    button.innerHTML='<i class="bi bi-arrow-repeat animate-spin me-1"></i>Updating...';

    try{
        await api('/api/notifications/read-all',{method:'PATCH'});

        updateNotificationBadges(0);
        notificationState.loaded=false;

        await loadNotifications(true);

        window.Toast?.success('All notifications marked as read.');

    }catch(error){
        console.error('Mark all read error:',error);

        window.Toast?.error(
            AdminUI.extractError(error)
        );

    }finally{
        button.disabled=notificationState.unread===0;
        button.innerHTML=original;
    }
}

async function loadNotificationCount(){
    try{
        const response=await api('/api/notifications/unread-count');
        const count=response?.data?.count??response?.count??0;

        updateNotificationBadges(count);
    }catch(error){
        console.error('Notification count error:',error);
    }
}

window.updateContactMessageBadge=function(count){
    const badge=document.getElementById('contactMessageBadge');
    if(!badge)return;

    count=Number(count??0);

    badge.textContent=count>99?'99+':count;
    badge.classList.toggle('hidden',count<=0);
};

async function loadContactMessageCount(){
    if(!document.getElementById('contactMessageBadge'))return;

    try{
        const response=await api('/api/contact-messages?per_page=5');

        updateContactMessageBadge(response?.unread_count??0);
    }catch(error){
        console.error('Contact message count error:',error);
    }
}

document.addEventListener('DOMContentLoaded',()=>{
    loadNotificationCount();
    loadContactMessageCount();
});
    </script>

    @stack('scripts')
</body>

</html>