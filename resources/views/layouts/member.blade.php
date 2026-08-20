<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title','Member Portal') - {{ setting('organization_name','Dreamers Association') }}</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 antialiased">

@php
    $authUser=auth()->user();
    $member=$authUser?->member;

    $shareEnabled=filter_var(
        setting('share_enabled',false),
        FILTER_VALIDATE_BOOLEAN
    );

    $organizationName=setting(
        'organization_name',
        'Dreamers Association'
    );

    $userName=$authUser?->name??'Member';

    $memberCode=$member?->member_code??'-';

    $initial=$userName
        ?strtoupper(mb_substr($userName,0,1))
        :'M';

    $profilePhoto=$member?->profile_photo_url;
@endphp

<div class="flex h-screen overflow-hidden">

    {{-- Mobile Overlay --}}
    <div
        id="sidebarOverlay"
        onclick="closeSidebar()"
        class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-[1px] lg:hidden">
    </div>

    {{-- Sidebar --}}
    <aside
        id="memberSidebar"
        class="fixed inset-y-0 left-0 z-50 flex w-[260px] -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0">

        {{-- Brand --}}
        <div class="flex h-[64px] shrink-0 items-center border-b border-slate-200 px-4">
            <a
                href="{{ route('member.dashboard') }}"
                class="flex min-w-0 items-center gap-3">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-[#4680b7] to-[#145da0]  text-white shadow-sm">
                    <i class="bi bi-people-fill text-sm"></i>
                </div>

                <div class="min-w-0">
                    <div class="truncate text-sm font-bold text-slate-800">
                        {{ $organizationName }}
                    </div>

                    <div class="text-[10px] font-medium uppercase tracking-[0.14em] text-slate-400">
                        Member Portal
                    </div>
                </div>
            </a>

            <button
                type="button"
                onclick="closeSidebar()"
                class="ml-auto flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 lg:hidden">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Member Card --}}
        <div class="border-b border-slate-100 px-3 py-4">
            <div class="rounded-md bg-slate-50 p-3">
                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4680b7] to-[#145da0]  text-sm font-bold text-white">
                        @if($profilePhoto)
                            <img
                                src="{{ $profilePhoto }}"
                                alt="{{ $userName }}"
                                class="h-full w-full object-cover"
                            >
                        @else
                            {{ $initial }}
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div class="truncate text-xs font-semibold text-slate-700">
                            {{ $userName }}
                        </div>

                        <div class="mt-0.5 truncate font-mono text-[10px] text-sky-600">
                            {{ $memberCode }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4">

            <div class="mb-2 px-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                Overview
            </div>

            <div class="space-y-1">

                <a
                    href="{{ route('member.dashboard') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold transition
                    {{ request()->routeIs('member.dashboard')
                        ?'bg-sky-50 text-sky-700'
                        :'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">

                    <i class="bi bi-grid-1x2-fill w-4 text-center text-sm"></i>

                    <span>Dashboard</span>
                </a>

                <a
                    href="{{ route('member.profile') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold transition
                    {{ request()->routeIs('member.profile')
                        ?'bg-sky-50 text-sky-700'
                        :'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">

                    <i class="bi bi-person-circle w-4 text-center text-sm"></i>

                    <span>My Profile</span>
                </a>

            </div>

            <div class="mb-2 mt-6 px-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                Finance
            </div>

            <div class="space-y-1">

                <a
                    href="{{ route('member.subscriptions') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold transition
                    {{ request()->routeIs('member.subscriptions')
                        ?'bg-sky-50 text-sky-700'
                        :'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">

                    <i class="bi bi-credit-card-2-front w-4 text-center text-sm"></i>

                    <span>Monthly Subscription</span>
                </a>

                @if($shareEnabled)
                    <a
                        href="{{ route('member.shares') }}"
                        class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold transition
                        {{ request()->routeIs('member.shares')
                            ?'bg-sky-50 text-sky-700'
                            :'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">

                        <i class="bi bi-layers-fill w-4 text-center text-sm"></i>

                        <span>My Shares</span>
                    </a>
                @endif

            </div>

            <div class="mb-2 mt-6 px-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                Community
            </div>

            <div class="space-y-1">

                <a
                    href="{{ route('member.polls') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold transition
                    {{ request()->routeIs('member.polls')
                        ?'bg-sky-50 text-sky-700'
                        :'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">

                    <i class="bi bi-bar-chart-fill w-4 text-center text-sm"></i>

                    <span>Polls</span>
                </a>

                <a
                    href="{{ route('member.notices') }}"
                    class="group flex items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold transition
                    {{ request()->routeIs('member.notices')
                        ?'bg-sky-50 text-sky-700'
                        :'text-slate-600 hover:bg-slate-50 hover:text-slate-800' }}">

                    <i class="bi bi-megaphone-fill w-4 text-center text-sm"></i>

                    <span>Notices</span>
                </a>

            </div>

        </nav>

        {{-- Sidebar Footer --}}
        <div class="border-t border-slate-200 p-3">
            <form
                method="POST"
                action="{{ route('logout') }}">

                @csrf

                <button
                    type="submit"
                    class="flex w-full items-center gap-3 rounded-md px-3 py-2.5 text-xs font-semibold text-red-500 transition hover:bg-red-50">

                    <i class="bi bi-box-arrow-right w-4 text-center text-sm"></i>

                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex min-w-0 flex-1 flex-col">

        {{-- Header --}}
        <header class="z-30 h-[64px] shrink-0 border-b border-slate-200 bg-white shadow-[0_1px_3px_rgba(16,24,40,0.06)]">

            <div class="flex h-full items-center justify-between px-4 sm:px-5 lg:px-6">

                {{-- Left --}}
                <div class="flex min-w-0 items-center gap-2">

                    <button
                        type="button"
                        onclick="openSidebar()"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 lg:hidden"
                        aria-label="Open menu">

                        <i class="bi bi-list text-lg"></i>
                    </button>

                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        class="hidden h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700 sm:inline-flex">

                        <i class="bi bi-globe2"></i>

                        View Website
                    </a>

                    <button
                        type="button"
                        onclick="window.location.reload()"
                        class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">

                        <i class="bi bi-arrow-clockwise"></i>

                        <span class="hidden sm:inline">
                            Refresh
                        </span>
                    </button>

                </div>

                {{-- Right --}}
                <div class="relative flex shrink-0 items-center gap-1 sm:gap-2">

                    {{-- Notifications --}}
                    <button
                        type="button"
                        onclick="toggleNotificationMenu(event)"
                        class="group relative flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700"
                        title="Notifications">

                        <i class="bi bi-bell text-[15px]"></i>

                        <span
                            id="notificationBadge"
                            class="absolute -right-1 -top-1 hidden min-w-[17px] rounded-full bg-red-500 px-1 text-center text-[9px] font-bold leading-[17px] text-white">
                            0
                        </span>
                    </button>

                    {{-- Inbox --}}
                    <button
                        type="button"
                        onclick="toggleInboxMenu(event)"
                        class="group relative flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                        title="Inbox">

                        <i class="bi bi-envelope text-[15px]"></i>

                        <span
                            id="inboxBadge"
                            class="absolute -right-1 -top-1 hidden min-w-[17px] rounded-full bg-indigo-500 px-1 text-center text-[9px] font-bold leading-[17px] text-white">
                            0
                        </span>
                    </button>

                    <div class="mx-1 hidden h-7 w-px bg-slate-200 sm:block"></div>

                    {{-- User Menu --}}
                    <button
                        type="button"
                        onclick="toggleUserMenu(event)"
                        class="group flex items-center gap-2 rounded-md py-1 pl-1 pr-2 transition hover:bg-slate-50">

                        <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4680b7] to-[#145da0]  text-[11px] font-bold text-white">
                            @if($profilePhoto)
                                <img
                                    src="{{ $profilePhoto }}"
                                    alt="{{ $userName }}"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                {{ $initial }}
                            @endif
                        </div>

                        <div class="hidden max-w-[140px] text-left sm:block">
                            <div class="truncate text-xs font-semibold text-slate-700">
                                {{ $userName }}
                            </div>

                            <div class="truncate text-[10px] text-slate-400">
                                {{ $memberCode }}
                            </div>
                        </div>

                        <i class="bi bi-chevron-down hidden text-[10px] text-slate-400 sm:block"></i>
                    </button>

                    {{-- User Dropdown --}}
                    <div
                        id="userDropdown"
                        class="absolute right-0 top-[48px] z-50 hidden w-60 overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">

                        <div class="border-b border-slate-100 px-4 py-3">
                            <div class="text-[10px] uppercase tracking-wide text-slate-400">
                                Signed in as
                            </div>

                            <div class="mt-1 truncate text-sm font-semibold text-slate-700">
                                {{ $userName }}
                            </div>

                            <div class="mt-0.5 truncate text-[11px] text-slate-400">
                                {{ $authUser?->email }}
                            </div>

                            <div class="mt-1 font-mono text-[10px] font-semibold text-sky-600">
                                {{ $memberCode }}
                            </div>
                        </div>

                        <div class="p-1.5">
                            <a
                                href="{{ route('member.profile') }}"
                                class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">

                                <i class="bi bi-person"></i>

                                My Profile
                            </a>

                            <a
                                href="{{ route('member.subscriptions') }}"
                                class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-xs font-medium text-slate-600 transition hover:bg-slate-50">

                                <i class="bi bi-credit-card"></i>

                                My Subscription
                            </a>

                            <div class="my-1 border-t border-slate-100"></div>

                            <form
                                method="POST"
                                action="{{ route('logout') }}">

                                @csrf

                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-xs font-medium text-red-500 transition hover:bg-red-50">

                                    <i class="bi bi-box-arrow-right"></i>

                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Notification Dropdown --}}
                    <div
                        id="notificationDropdown"
                        class="absolute right-12 top-[48px] z-50 hidden w-[330px] overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">

                        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                            <div>
                                <h3 class="text-xs font-semibold text-slate-700">
                                    Notifications
                                </h3>

                                <p class="text-[10px] text-slate-400">
                                    Recent updates
                                </p>
                            </div>

                            <button
                                type="button"
                                onclick="markAllNotificationsRead()"
                                class="text-[10px] font-semibold text-sky-600 hover:text-sky-700">

                                Mark all read
                            </button>
                        </div>

                        <div
                            id="notificationList"
                            class="max-h-[320px] overflow-y-auto">

                            <div class="p-6 text-center text-xs text-slate-400">
                                Loading...
                            </div>
                        </div>
                    </div>

                    {{-- Inbox Dropdown --}}
                    <div
                        id="inboxDropdown"
                        class="absolute right-2 top-[48px] z-50 hidden w-[330px] overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl shadow-slate-900/10">

                        <div class="border-b border-slate-100 px-4 py-3">
                            <h3 class="text-xs font-semibold text-slate-700">
                                Inbox
                            </h3>

                            <p class="text-[10px] text-slate-400">
                                Messages and communication
                            </p>
                        </div>

                        <div
                            id="inboxList"
                            class="max-h-[320px] overflow-y-auto">

                            <div class="p-6 text-center">
                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                                    <i class="bi bi-envelope"></i>
                                </div>

                                <p class="mt-3 text-xs font-semibold text-slate-600">
                                    No messages
                                </p>

                                <p class="mt-1 text-[10px] text-slate-400">
                                    Your inbox is currently empty.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- Main Content --}}
        <main class="min-h-0 flex-1 overflow-y-auto">
            <div class="mx-auto w-full max-w-[1600px] p-4 sm:p-5 lg:p-6">
                @yield('content')
            </div>
        </main>

    </div>
</div>

<script>
function openSidebar(){
    document
        .getElementById('memberSidebar')
        .classList.remove('-translate-x-full');

    document
        .getElementById('sidebarOverlay')
        .classList.remove('hidden');
}

function closeSidebar(){
    document
        .getElementById('memberSidebar')
        .classList.add('-translate-x-full');

    document
        .getElementById('sidebarOverlay')
        .classList.add('hidden');
}

function closeHeaderDropdowns(except=null){
    const ids=[
        'userDropdown',
        'notificationDropdown',
        'inboxDropdown'
    ];

    ids.forEach(id=>{
        if(id!==except){
            document
                .getElementById(id)
                ?.classList
                .add('hidden');
        }
    });
}

function toggleUserMenu(event){
    event.stopPropagation();

    closeHeaderDropdowns(
        'userDropdown'
    );

    document
        .getElementById('userDropdown')
        .classList
        .toggle('hidden');
}

function toggleNotificationMenu(event){
    event.stopPropagation();

    closeHeaderDropdowns(
        'notificationDropdown'
    );

    const menu=
        document.getElementById(
            'notificationDropdown'
        );

    menu.classList.toggle('hidden');

    if(!menu.classList.contains('hidden')){
        loadNotifications();
    }
}

function toggleInboxMenu(event){
    event.stopPropagation();

    closeHeaderDropdowns(
        'inboxDropdown'
    );

    document
        .getElementById('inboxDropdown')
        .classList
        .toggle('hidden');
}

async function loadNotifications(){
    const list=
        document.getElementById(
            'notificationList'
        );

    try{
        const response=await api(
            '/api/notifications?per_page=8'
        );

        const payload=
            response.data?.data??
            response.data??
            [];

        const notifications=
            Array.isArray(payload)
                ?payload
                :[];

        if(!notifications.length){
            list.innerHTML=`
                <div class="p-6 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                        <i class="bi bi-bell"></i>
                    </div>

                    <p class="mt-3 text-xs font-semibold text-slate-600">
                        No notifications
                    </p>

                    <p class="mt-1 text-[10px] text-slate-400">
                        You're all caught up.
                    </p>
                </div>
            `;

            return;
        }

        list.innerHTML=
            notifications
                .map(notification=>`
                    <button
                        type="button"
                        onclick="openNotification(${Number(notification.id)})"
                        class="flex w-full gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50">

                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-md ${
                            notification.read_at
                                ?'bg-slate-100 text-slate-500'
                                :'bg-sky-50 text-sky-600'
                        }">

                            <i class="bi bi-bell"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="truncate text-xs font-semibold text-slate-700">
                                ${escapeLayoutHtml(
                                    notification.title??
                                    'Notification'
                                )}
                            </div>

                            <div class="mt-1 line-clamp-2 text-[10px] leading-4 text-slate-400">
                                ${escapeLayoutHtml(
                                    notification.message??
                                    ''
                                )}
                            </div>

                            <div class="mt-1.5 text-[9px] text-slate-400">
                                ${formatLayoutDate(
                                    notification.created_at
                                )}
                            </div>
                        </div>

                        ${
                            !notification.read_at
                                ?`
                                    <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>
                                `
                                :''
                        }
                    </button>
                `)
                .join('');

    }catch(error){
        list.innerHTML=`
            <div class="p-6 text-center text-xs text-red-500">
                Failed to load notifications.
            </div>
        `;
    }
}

async function loadNotificationCount(){
    try{
        const response=await api(
            '/api/notifications/unread-count'
        );

        const count=Number(
            response.data?.count??
            response.count??
            0
        );

        const badge=
            document.getElementById(
                'notificationBadge'
            );

        if(count>0){
            badge.textContent=
                count>99
                    ?'99+'
                    :count;

            badge.classList.remove(
                'hidden'
            );
        }else{
            badge.classList.add(
                'hidden'
            );
        }
    }catch(error){
        console.error(error);
    }
}

async function openNotification(id){
    try{
        await api(
            `/api/notifications/${id}/read`,
            {
                method:'PATCH'
            }
        );

        await loadNotificationCount();
        await loadNotifications();

    }catch(error){
        console.error(error);
    }
}

async function markAllNotificationsRead(){
    try{
        await api(
            '/api/notifications/read-all',
            {
                method:'PATCH'
            }
        );

        await loadNotificationCount();
        await loadNotifications();

    }catch(error){
        console.error(error);
    }
}

function formatLayoutDate(value){
    if(!value){
        return'';
    }

    const date=new Date(value);

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
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
    const div=
        document.createElement(
            'div'
        );

    div.textContent=
        String(value??'');

    return div.innerHTML;
}

document.addEventListener(
    'click',
    ()=>{
        closeHeaderDropdowns();
    }
);

document.addEventListener(
    'DOMContentLoaded',
    ()=>{
        loadNotificationCount();
    }
);

document.addEventListener(
    'keydown',
    event=>{
        if(event.key==='Escape'){
            closeSidebar();
            closeHeaderDropdowns();
        }
    }
);
</script>

@stack('scripts')
</body>
</html>