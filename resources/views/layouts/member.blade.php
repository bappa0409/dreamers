<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.app-config')
    <title>@yield('title','Member Portal') - {{ setting('organization_name','Dreamers Association') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @if(setting('site_favicon'))
    <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif

    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        #memberSidebar nav::-webkit-scrollbar {
            width: 2px
        }

        #memberSidebar nav::-webkit-scrollbar-track {
            background: transparent
        }

        #memberSidebar nav::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .35);
            border-radius: 999px
        }

        #memberSidebar nav::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, .55)
        }

        #memberSidebar nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, .35) transparent
        }
    </style>
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 antialiased">
    @php
    $authUser=auth()->user();
    $member=$authUser?->member;
    $shareEnabled=filter_var(setting('share_enabled',false),FILTER_VALIDATE_BOOLEAN);
    $organizationName=setting('organization_name','Dreamers Association');
    $userName=$authUser?->name??'Member';
    $memberCode=$member?->member_code??'-';
    $initial=$userName?strtoupper(mb_substr($userName,0,1)):'M';
    $profilePhoto=$member?->profile_photo_url;
    $notificationUnreadCount=$authUser?->unreadNotifications()->count()??0;

    $linkClass='group flex items-center gap-2.5 rounded-md px-2.5 py-1 text-sm font-semibold transition';
    $linkInactive='text-slate-600 hover:bg-slate-50 hover:text-slate-800';
    $linkActive='bg-sky-50 text-sky-700';
    $iconClass='w-4 text-center text-base';
    $sectionClass='mb-1 mt-3 px-2.5 text-[9px] font-semibold uppercase tracking-[0.14em] text-slate-500';

    $overview=[
    ['member.dashboard','member.dashboard','bi-grid-1x2-fill','Dashboard'],
    ['member.profile','member.profile','bi-person-circle','Profile'],
    ['member.nominees','member.nominees','bi-person-heart','Nominees'],
    ];

    $association=[
    ['member.tours','member.tours*','bi-airplane-fill','Tours'],
    ['member.meetings','member.meetings*','bi-people-fill','Meetings'],
    ['member.polls','member.polls*','bi-bar-chart-fill','Polls'],
    ];

    $support=[
    ['member.welfare','member.welfare*','bi-heart-pulse','Welfare Support'],
    ['member.feedback-support','member.feedback-support*','bi-headset','Feedback & Support'],
    ];

    $updates=[
    ['member.notices','member.notices*','bi-megaphone-fill','Notices'],
    ];

    $subscriptionOpen=request()->routeIs('member.subscriptions*')||request()->routeIs('member.subscription-payments');
    $investmentOpen=request()->routeIs('member.investments*');
    @endphp

    <div class="flex h-screen overflow-hidden">

        <div id="sidebarOverlay" onclick="closeSidebar()"
            class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-[1px] lg:hidden"></div>

        <aside id="memberSidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-[250px] -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0">

            <div class="flex h-[64px] shrink-0 items-center border-b border-slate-200 px-4">
                <a href="{{ route('member.dashboard') }}" class="flex min-w-0 items-center gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-[#4680b7] to-[#145da0] text-white shadow-sm">
                        <i class="bi bi-people-fill text-base"></i>
                    </div>

                    <div class="min-w-0">
                        <div class="truncate text-base font-bold text-slate-800">{{ $organizationName }}</div>
                        <div class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-500">Member Portal
                        </div>
                    </div>
                </a>

                <button type="button" onclick="closeSidebar()"
                    class="ml-auto flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-600 lg:hidden">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-3">

                {{-- OVERVIEW --}}
                <div class="{{ $sectionClass }} !mt-0">Overview</div>

                <div class="space-y-0.5">
                    @foreach($overview as [$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi {{ $icon }} {{ $iconClass }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </div>

                {{-- FINANCE --}}
                <div class="{{ $sectionClass }}">Finance</div>

                <div class="space-y-0.5">

                    <div>
                        <button type="button" onclick="toggleMemberMenu('subscriptionMenu','subscriptionChevron')"
                            class="{{ $linkClass }} w-full {{ $subscriptionOpen?$linkActive:$linkInactive }}">
                            <i class="bi bi-credit-card-2-front {{ $iconClass }}"></i>
                            <span class="flex-1 text-left">Subscriptions</span>
                            <i id="subscriptionChevron"
                                class="bi bi-chevron-down text-[9px] transition-transform duration-200 {{ $subscriptionOpen?'rotate-180':'' }}"></i>
                        </button>

                        <div id="subscriptionMenu"
                            class="{{ $subscriptionOpen?'':'hidden' }} ml-[22px] border-l border-slate-200 pl-3">
                            <a href="{{ route('member.subscriptions') }}"
                                class="flex items-center gap-2 rounded-md px-2 py-1 text-[11px] font-medium transition {{ request()->routeIs('member.subscriptions')?'bg-sky-50 text-sky-700':'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                Monthly Dues
                            </a>

                            <a href="{{ route('member.subscription-payments') }}"
                                class="flex items-center gap-2 rounded-md px-2 py-1 text-[11px] font-medium transition {{ request()->routeIs('member.subscription-payments')?'bg-sky-50 text-sky-700':'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                Payment History
                            </a>
                        </div>
                    </div>

                    @if($shareEnabled)
                    @php($active=request()->routeIs('member.shares*'))
                    <a href="{{ route('member.shares') }}"
                        class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi bi-layers-fill {{ $iconClass }}"></i>
                        <span>Shares</span>
                    </a>
                    @endif

                    @php($active=request()->routeIs('member.loans*'))
                    <a href="{{ route('member.loans') }}"
                        class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi bi-bank {{ $iconClass }}"></i>
                        <span>Loans</span>
                    </a>

                    <div>
                        <button type="button" onclick="toggleMemberMenu('investmentMenu','investmentChevron')"
                            class="{{ $linkClass }} w-full {{ $investmentOpen?$linkActive:$linkInactive }}">
                            <i class="bi bi-graph-up-arrow {{ $iconClass }}"></i>
                            <span class="flex-1 text-left">Investments</span>
                            <i id="investmentChevron"
                                class="bi bi-chevron-down text-[9px] transition-transform duration-200 {{ $investmentOpen?'rotate-180':'' }}"></i>
                        </button>

                        <div id="investmentMenu"
                            class="{{ $investmentOpen?'':'hidden' }} ml-[22px] border-l border-slate-200 pl-3">
                            <a href="{{ route('member.investments.active') }}"
                                class="flex items-center gap-2 rounded-md px-2 py-1 text-[11px] font-medium transition {{ request()->routeIs('member.investments.active')?'bg-sky-50 text-sky-700':'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                Active Investments
                            </a>

                            <a href="{{ route('member.investments.completed') }}"
                                class="flex items-center gap-2 rounded-md px-2 py-1 text-[11px] font-medium transition {{ request()->routeIs('member.investments.completed')?'bg-sky-50 text-sky-700':'text-slate-500 hover:bg-slate-50 hover:text-slate-700' }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current opacity-60"></span>
                                Completed Investments
                            </a>
                        </div>
                    </div>

                </div>

                {{-- ASSOCIATION --}}
                <div class="{{ $sectionClass }}">Association</div>

                <div class="space-y-0.5">
                    @foreach($association as [$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi {{ $icon }} {{ $iconClass }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </div>

                {{-- ASSISTANCE & SUPPORT --}}
                <div class="{{ $sectionClass }}">Assistance & Support</div>

                <div class="space-y-0.5">
                    @foreach($support as [$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi {{ $icon }} {{ $iconClass }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach
                </div>

                {{-- UPDATES --}}
                <div class="{{ $sectionClass }}">Communication</div>

                <div class="space-y-0.5">
                    @foreach($updates as [$route,$match,$icon,$label])
                    @php($active=request()->routeIs($match))
                    <a href="{{ route($route) }}" class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi {{ $icon }} {{ $iconClass }}"></i>
                        <span>{{ $label }}</span>
                    </a>
                    @endforeach

                    @php($active=request()->routeIs('member.notifications*'))
                    <a href="{{ route('member.notifications') }}"
                        class="{{ $linkClass }} justify-between {{ $active?$linkActive:$linkInactive }}">
                        <span class="flex items-center gap-2.5">
                            <i class="bi bi-bell-fill {{ $iconClass }}"></i>
                            <span>Notifications</span>
                        </span>

                        <span id="sidebarNotificationBadge"
                            class="{{ $notificationUnreadCount>0?'':'hidden' }} min-w-[18px] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[9px] font-bold leading-none text-white">
                            {{ $notificationUnreadCount>99?'99+':$notificationUnreadCount }}
                        </span>
                    </a>
                </div>

                {{-- ACCOUNT --}}
                <div class="{{ $sectionClass }}">Account</div>

                <div class="space-y-0.5">
                    @php($active=request()->routeIs('member.exit*'))
                    <a href="{{ route('member.exit') }}"
                        class="{{ $linkClass }} {{ $active?$linkActive:$linkInactive }}">
                        <i class="bi bi-box-arrow-right {{ $iconClass }}"></i>
                        <span>Membership Cancel</span>
                    </a>
                </div>

            </nav>

            <div class="shrink-0 border-t border-slate-200 p-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4680b7] to-[#145da0] text-[11px] font-bold text-white">
                        @if($profilePhoto)
                        <img src="{{ $profilePhoto }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                        @else
                        {{ $initial }}
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="truncate text-[12px] font-semibold text-slate-700">{{ $userName }}</div>
                        <div class="truncate text-[10px] text-slate-500">{{ $memberCode }}</div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" novalidate data-js-validation="1">
                        @csrf
                        <button type="submit" title="Sign Out"
                            class="flex h-8 w-8 items-center justify-center rounded-full text-slate-500 transition hover:bg-red-50 hover:text-red-500">
                            <i class="bi bi-box-arrow-right text-[15px]"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <div class="flex min-w-0 flex-1 flex-col">

            <header
                class="z-30 h-[64px] shrink-0 border-b border-slate-200 bg-white shadow-[0_1px_3px_rgba(16,24,40,0.06)]">

                <div class="flex h-full items-center justify-between px-4 sm:px-5 lg:px-6">

                    <div class="flex min-w-0 items-center gap-2">

                        <button type="button" onclick="openSidebar()"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 lg:hidden"
                            aria-label="Open menu">
                            <i class="bi bi-list text-lg"></i>
                        </button>

                        <a href="{{ route('home') }}" target="_blank"
                            class="hidden h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700 sm:inline-flex">
                            <i class="bi bi-globe2"></i>
                            View Website
                        </a>

                        <button type="button" onclick="window.location.reload()"
                            class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">
                            <i class="bi bi-arrow-clockwise"></i>
                            <span class="hidden sm:inline">Refresh</span>
                        </button>

                    </div>

                    <div class="relative flex shrink-0 items-center gap-1 sm:gap-2">

                        <button type="button" onclick="toggleNotificationMenu(event)"
                            class="group relative flex h-9 w-9 items-center justify-center bg-white"
                            title="Notifications">
                            <i class="bi bi-bell text-[15px]"></i>


                            <span id="notificationBadge" class="{{ $notificationUnreadCount > 0 ? '' : 'hidden' }}
    absolute -right-0 -top-0
    min-w-[17px] rounded-full bg-red-500 px-1 text-center
    text-[9px] font-bold leading-[17px] text-white">
                                {{ $notificationUnreadCount > 99 ? '99+' : $notificationUnreadCount }}
                            </span>
                        </button>


                        <div class="mx-1 hidden h-7 w-px bg-slate-200 sm:block"></div>

                        <button type="button" onclick="toggleUserMenu(event)"
                            class="group flex items-center gap-2 rounded-md py-1 pl-1 pr-2 transition hover:bg-slate-50">

                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4680b7] to-[#145da0] text-[11px] font-bold text-white">
                                @if($profilePhoto)
                                <img src="{{ $profilePhoto }}" alt="{{ $userName }}" class="h-full w-full object-cover">
                                @else
                                {{ $initial }}
                                @endif
                            </div>

                            <div class="hidden max-w-[140px] text-left sm:block">
                                <div class="truncate text-xs 2xl:text-sm font-semibold text-slate-700">{{ $userName }}
                                </div>
                                <div class="truncate text-[10px] text-slate-500">{{ $memberCode }}</div>
                            </div>

                            <i class="bi bi-chevron-down hidden text-[10px] text-slate-500 sm:block"></i>
                        </button>

                        <div id="userDropdown"
                            class="absolute right-0 top-[48px] z-50 hidden w-60 overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <div class="text-[10px] uppercase tracking-wide text-slate-500">Signed in as</div>
                                <div class="mt-1 truncate text-base font-semibold text-slate-700">{{ $userName }}</div>
                                <div class="mt-0.5 truncate text-[11px] text-slate-500">{{ $authUser?->email }}</div>
                                <div class="mt-1 font-mono text-[10px] font-semibold text-sky-600">{{ $memberCode }}
                                </div>
                            </div>

                            <div class="p-1.5">
                                <a href="{{ route('member.profile') }}"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                                    <i class="bi bi-person"></i>
                                    My Profile
                                </a>

                                <a href="{{ route('member.subscriptions') }}"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                                    <i class="bi bi-credit-card"></i>
                                    My Subscription
                                </a>

                                <div class="my-1 border-t border-slate-100"></div>

                                <form method="POST" action="{{ route('logout') }}" novalidate data-js-validation="1">
                                    @csrf
                                    <button type="submit"
                                        class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-medium text-red-500 transition hover:bg-red-50">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div id="notificationDropdown"
                            class="absolute right-12 top-[48px] z-50 hidden w-[350px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xl shadow-slate-900/10">

                            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                <div>
                                    <h3 class="text-sm font-semibold text-slate-700">Notifications</h3>

                                    <p id="notificationUnreadText" class="mt-0.5 text-[10px] text-slate-500">
                                        {{ $notificationUnreadCount>0?$notificationUnreadCount.' unread
                                        notification'.($notificationUnreadCount===1?'':'s'):'No unread notifications' }}
                                    </p>
                                </div>

                                <button id="notificationMarkAllButton" type="button"
                                    onclick="event.stopPropagation();markAllNotificationsRead()" {{
                                    $notificationUnreadCount<=0?'disabled':'' }}
                                    class="text-[10px] font-semibold text-sky-600 transition hover:text-sky-700 disabled:cursor-not-allowed disabled:opacity-40">
                                    Mark all read
                                </button>
                            </div>

                            <div id="notificationList" class="max-h-[360px] overflow-y-auto">
                                <div class="p-8 text-center">
                                    <div
                                        class="mx-auto h-6 w-6 animate-spin rounded-full border-4 border-slate-200 border-t-sky-600">
                                    </div>
                                    <p class="mt-2 text-[10px] text-slate-500">Loading notifications...</p>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 bg-slate-50/60 p-2">
                                <a href="{{ route('member.notifications') }}"
                                    class="flex h-9 w-full items-center justify-center gap-2 rounded-md text-[11px] font-semibold text-sky-600 transition hover:bg-sky-50 hover:text-sky-700">
                                    View All Notifications
                                    <i class="bi bi-arrow-right text-[10px]"></i>
                                </a>
                            </div>

                        </div>

                        <div id="inboxDropdown"
                            class="absolute right-2 top-[48px] z-50 hidden w-[330px] overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">
                            <div class="border-b border-slate-100 px-4 py-3">
                                <h3 class="text-sm font-semibold text-slate-700">Inbox</h3>
                                <p class="text-[10px] text-slate-500">Messages and communication</p>
                            </div>

                            <div id="inboxList" class="max-h-[320px] overflow-y-auto">
                                <div class="p-6 text-center">
                                    <div
                                        class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                                        <i class="bi bi-envelope"></i>
                                    </div>

                                    <p class="mt-3 text-sm font-semibold text-slate-600">No messages</p>
                                    <p class="mt-1 text-[10px] text-slate-500">Your inbox is currently empty.</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </header>

            <main class="min-h-0 flex-1 overflow-y-auto">
                <div class="mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    <script>
        function toggleMemberMenu(menuId,chevronId){
    const menu=document.getElementById(menuId);
    const chevron=document.getElementById(chevronId);
    if(!menu)return;
    menu.classList.toggle('hidden');
    chevron?.classList.toggle('rotate-180',!menu.classList.contains('hidden'));
}

function openSidebar(){
    document.getElementById('memberSidebar')?.classList.remove('-translate-x-full');
    document.getElementById('sidebarOverlay')?.classList.remove('hidden');
}

function closeSidebar(){
    document.getElementById('memberSidebar')?.classList.add('-translate-x-full');
    document.getElementById('sidebarOverlay')?.classList.add('hidden');
}

function closeHeaderDropdowns(except=null){
    ['userDropdown','notificationDropdown','inboxDropdown'].forEach(id=>{
        if(id!==except)document.getElementById(id)?.classList.add('hidden');
    });
}

function toggleUserMenu(event){
    event.stopPropagation();
    closeHeaderDropdowns('userDropdown');
    document.getElementById('userDropdown')?.classList.toggle('hidden');
}

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

function toggleInboxMenu(event){
    event.stopPropagation();
    closeHeaderDropdowns('inboxDropdown');
    document.getElementById('inboxDropdown')?.classList.toggle('hidden');
}

const notificationState={
    unread:@json($notificationUnreadCount),
    loaded:false,
    loading:null
};

function notificationMeta(notification){
    const value=String(
        notification.data?.type??
        notification.type??
        notification.title??
        ''
    ).toLowerCase();

    if(value.includes('subscription')||value.includes('payment')){
        return{
            label:'Subscription',
            icon:'bi-credit-card',
            className:'bg-emerald-50 text-emerald-600'
        };
    }

    if(value.includes('share')){
        return{
            label:'Share',
            icon:'bi-layers-fill',
            className:'bg-violet-50 text-violet-600'
        };
    }

    if(value.includes('loan')){
        return{
            label:'Loan',
            icon:'bi-bank',
            className:'bg-cyan-50 text-cyan-600'
        };
    }

    if(value.includes('investment')){
        return{
            label:'Investment',
            icon:'bi-graph-up-arrow',
            className:'bg-indigo-50 text-indigo-600'
        };
    }

    if(value.includes('welfare')){
        return{
            label:'Welfare',
            icon:'bi-heart-pulse',
            className:'bg-rose-50 text-rose-600'
        };
    }

    if(value.includes('support')||value.includes('feedback')){
        return{
            label:'Support',
            icon:'bi-headset',
            className:'bg-purple-50 text-purple-600'
        };
    }

    if(value.includes('notice')){
        return{
            label:'Notice',
            icon:'bi-megaphone-fill',
            className:'bg-sky-50 text-sky-600'
        };
    }

    if(value.includes('poll')){
        return{
            label:'Poll',
            icon:'bi-bar-chart-fill',
            className:'bg-amber-50 text-amber-600'
        };
    }

    if(value.includes('security')||value.includes('login')||value.includes('password')){
        return{
            label:'Security',
            icon:'bi-shield-check',
            className:'bg-red-50 text-red-600'
        };
    }

    if(value.includes('member')){
        return{
            label:'Membership',
            icon:'bi-person-check',
            className:'bg-cyan-50 text-cyan-600'
        };
    }

    return{
        label:'General',
        icon:'bi-bell',
        className:'bg-slate-100 text-slate-500'
    };
}

function escapeLayoutJs(value){
    return String(value??'')
        .replaceAll('\\','\\\\')
        .replaceAll("'","\\'");
}

function updateNotificationBadges(count){
    count=Math.max(
        0,
        Number(count??0)
    );

    notificationState.unread=count;

    const display=count>99
        ?'99+'
        :String(count);

    const topBadge=
        document.getElementById('notificationBadge');

    const sidebarBadge=
        document.getElementById('sidebarNotificationBadge');

    const unreadText=
        document.getElementById('notificationUnreadText');

    const markAllButton=
        document.getElementById('notificationMarkAllButton');

    [topBadge,sidebarBadge]
        .filter(Boolean)
        .forEach(badge=>{
            badge.textContent=display;
            badge.classList.toggle(
                'hidden',
                count===0
            );
        });

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

    if(
        value.startsWith('/')&&
        !value.startsWith('//')
    ){
        return value;
    }

    try{
        const url=new URL(
            value,
            window.location.origin
        );

        return url.origin===window.location.origin
            ?url.pathname+url.search+url.hash
            :null;

    }catch{
        return null;
    }
}

function renderNotificationItems(notifications){
    const list=
        document.getElementById('notificationList');

    if(!list)return;

    if(!notifications.length){
        list.innerHTML=`
            <div class="p-8 text-center">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-50 text-slate-500">
                    <i class="bi bi-bell"></i>
                </div>

                <p class="mt-3 text-sm font-semibold text-slate-600">
                    No notifications
                </p>

                <p class="mt-1 text-[10px] text-slate-500">
                    You're all caught up.
                </p>
            </div>`;

        return;
    }

    list.innerHTML=notifications.map(notification=>{
        const meta=notificationMeta(notification);
        const unread=!notification.read_at;

        return`
        <button type="button"
            onclick="openNotification('${escapeLayoutJs(notification.id)}',${unread?'true':'false'})"
            class="relative flex w-full gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 ${unread?'bg-sky-50/30 hover:bg-sky-50/60':'hover:bg-slate-50'}">

            ${unread?`
                <span class="absolute inset-y-0 left-0 w-[2px] bg-sky-500"></span>
            `:''}

            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-md ${meta.className}">
                <i class="bi ${meta.icon}"></i>
            </div>

            <div class="min-w-0 flex-1">

                <div class="flex items-start justify-between gap-2">
                    <div class="truncate text-sm font-semibold ${unread?'text-slate-800':'text-slate-700'}">
                        ${escapeLayoutHtml(
                            notification.title??
                            notification.data?.title??
                            'Notification'
                        )}
                    </div>

                    ${unread?`
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>
                    `:''}
                </div>

                <div class="mt-1 line-clamp-2 text-[10px] leading-4 text-slate-500">
                    ${escapeLayoutHtml(
                        notification.message??
                        notification.data?.message??
                        ''
                    )}
                </div>

                <div class="mt-1.5 flex items-center gap-2 text-[9px] text-slate-500">
                    <span>
                        <i class="bi bi-clock me-1"></i>
                        ${formatLayoutDate(notification.created_at)}
                    </span>

                    <span>
                        ${escapeLayoutHtml(meta.label)}
                    </span>
                </div>

            </div>
        </button>`;
    }).join('');
}

async function loadNotifications(force=false){
    const list=
        document.getElementById('notificationList');

    if(!list)return;

    if(
        notificationState.loaded&&
        !force
    ){
        return;
    }

    if(notificationState.loading){
        return notificationState.loading;
    }

    list.innerHTML=`
        <div class="p-8 text-center">
            <div class="mx-auto h-6 w-6 animate-spin rounded-full border-4 border-slate-200 border-t-sky-600"></div>
            <p class="mt-2 text-[10px] text-slate-500">
                Loading notifications...
            </p>
        </div>`;

    notificationState.loading=(async()=>{
        try{
            const response=await api(
                '/api/notifications?per_page=8'
            );

            const payload=
                response.data?.data??
                response.data??
                [];

            renderNotificationItems(
                Array.isArray(payload)
                    ?payload
                    :[]
            );

            notificationState.loaded=true;

        }catch(error){
            console.error(error);

            list.innerHTML=`
                <div class="p-7 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-red-50 text-red-500">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>

                    <p class="mt-2 text-[11px] font-semibold text-red-600">
                        Failed to load notifications
                    </p>

                    <button type="button"
                        onclick="loadNotifications(true)"
                        class="mt-3 text-[10px] font-semibold text-sky-600">
                        Try Again
                    </button>
                </div>`;

        }finally{
            notificationState.loading=null;
        }
    })();

    return notificationState.loading;
}

async function openNotification(id,wasUnread=false){
    try{
        const response=await api(
            `/api/notifications/${id}/read`,
            {
                method:'PATCH'
            }
        );

        if(wasUnread){
            updateNotificationBadges(
                notificationState.unread-1
            );
        }

        notificationState.loaded=false;

        const url=getNotificationUrl(
            response.data??null
        );

        window.location.href=
            url??@json(route('member.notifications'));

    }catch(error){
        console.error(error);

        window.Toast?.error(
            error?.data?.message??
            error?.message??
            'Unable to open notification.'
        );
    }
}

async function markAllNotificationsRead(){
    const button=
        document.getElementById(
            'notificationMarkAllButton'
        );

    if(
        !button||
        button.disabled
    ){
        return;
    }

    const original=button.innerHTML;

    button.disabled=true;
    button.innerHTML=
        '<i class="bi bi-arrow-repeat animate-spin me-1"></i>Updating...';

    try{
        await api(
            '/api/notifications/read-all',
            {
                method:'PATCH'
            }
        );

        updateNotificationBadges(0);

        notificationState.loaded=false;

        await loadNotifications(true);

        window.Toast?.success(
            'All notifications marked as read.'
        );

    }catch(error){
        console.error(error);

        button.disabled=false;

        window.Toast?.error(
            error?.data?.message??
            error?.message??
            'Unable to update notifications.'
        );

    }finally{
        button.innerHTML=original;
        button.disabled=
            notificationState.unread===0;
    }
}

function formatLayoutDate(value){
    if(!value)return'';

    const date=new Date(value);

    if(Number.isNaN(date.getTime())){
        return'';
    }

    return date.toLocaleString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            hour:'2-digit',
            minute:'2-digit'
        }
    );
}

function escapeLayoutHtml(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

document.addEventListener('click',()=>{
    closeHeaderDropdowns();
});

document.addEventListener('keydown',event=>{
    if(event.key==='Escape'){
        closeSidebar();
        closeHeaderDropdowns();
    }
});

document.querySelectorAll('#memberSidebar a').forEach(link=>{
    link.addEventListener('click',()=>{
        if(window.innerWidth<1024){
            closeSidebar();
        }
    });
});
    </script>

    @stack('scripts')
</body>

</html>