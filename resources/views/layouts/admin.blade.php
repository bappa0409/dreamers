<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Dashboard') | {{ setting('organization_name','Dreamers Association') }}
    </title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    @if(setting('site_favicon'))
        <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        html,
        body {
            height: 100%;
        }

        body {
            overflow: hidden;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none;
        }

        /*
        |--------------------------------------------------------------------------
        | Main Scrollbar
        |--------------------------------------------------------------------------
        */

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, .35);
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, .55);
        }


        /*
        |--------------------------------------------------------------------------
        | Sidebar Scrollbar
        |--------------------------------------------------------------------------
        */

        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 999px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .22);
        }


        /*
        |--------------------------------------------------------------------------
        | Select
        |--------------------------------------------------------------------------
        */

        .company-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Active Menu
        |--------------------------------------------------------------------------
        */

        .menu-link {
            position: relative;
        }

        .menu-link.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 7px;
            bottom: 7px;
            width: 3px;
            border-radius: 0 999px 999px 0;
            background: #38bdf8;
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">

    @php

    $authUser = auth()->user();

    /*
    |--------------------------------------------------------------------------
    | Permission Helper
    |--------------------------------------------------------------------------
    |
    | System Analyst is automatically allowed because
    | User::hasPermission() already contains the bypass.
    |
    */

    $can = static function (string $permission) use ($authUser): bool {

    return $authUser
    ? $authUser->hasPermission($permission)
    : false;
    };


    /*
    |--------------------------------------------------------------------------
    | Navigation Section Visibility
    |--------------------------------------------------------------------------
    */

    $hasMainManagementMenu =
    $can('Member.view') ||
    $can('Finance.view') ||
    $can('Investment.view') ||
    $can('Land.view') ||
    $can('Project.view') ||
    $can('Poll.view') ||
    $can('Notice.view') ||
    $can('Report.view');


    $hasSystemMenu =
    $can('Document.view') ||
    $can('Approval.view') ||
    $can('Mailing.view') ||
    $can('User.view') ||
    $can('Role.view') ||
    $can('Setting.view') ||
    $can('Backup.view') ||
    $can('Audit.view');

    @endphp


    <div id="app" class="flex h-screen overflow-hidden">


        {{-- =====================================================
        MOBILE OVERLAY
        ====================================================== --}}

        <div id="sidebarOverlay" class="fixed inset-0 z-40 hidden bg-slate-950/60 backdrop-blur-sm lg:hidden"
            onclick="closeSidebar()"></div>



        {{-- =====================================================
        SIDEBAR
        ====================================================== --}}

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 flex w-[250px] -translate-x-full flex-col
               bg-gradient-to-b from-slate-950 via-[#0d3b66] to-[#0a2f52]
               shadow-2xl transition-transform duration-300 ease-in-out
               lg:static lg:translate-x-0">


            {{-- =================================================
            BRAND
            ================================================== --}}

            @php
            $organizationName=setting('organization_name','Dreamers Association');
            $siteLogo=setting('site_logo');

            $nameParts=preg_split('/\s+/',trim($organizationName),2);
            $primaryName=$nameParts[0]??$organizationName;
            $secondaryName=$nameParts[1]??'';
            @endphp

            <div class="flex h-[76px] shrink-0 items-center border-b border-white/[0.08] px-4">
                <a href="{{ route('dashboard') }}" class="flex w-full items-center gap-3 focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-md">
                        @if($siteLogo)
                        <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $organizationName }}"
                            class="h-full w-full object-contain">
                        @else
                        <div
                            class="flex h-full w-full items-center justify-center bg-white text-lg font-black uppercase text-slate-950">
                            {{ mb_substr($organizationName,0,1) }}
                        </div>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div
                            class="truncate text-[17px] font-bold leading-tight uppercase tracking-[0.06em] text-white">
                            {{ $primaryName }}
                        </div>

                        @if($secondaryName)
                        <div
                            class="mt-0.5 truncate text-[11px] font-medium uppercase tracking-[0.12em] text-sky-200/80">
                            {{ $secondaryName }}
                        </div>
                        @endif
                    </div>
                </a>
            </div>

            {{-- =================================================
            NAVIGATION
            ================================================== --}}

            <div class="sidebar-scroll flex-1 overflow-y-auto px-3 py-4">


                {{-- MAIN --}}

                <div class="mb-2 px-3 pt-1 text-[10px] font-semibold
                       uppercase tracking-[0.14em] text-sky-200/45">
                    Main
                </div>


                <nav class="space-y-0.5">


                    {{-- =================================================
                    DASHBOARD
                    ================================================== --}}

                    <a href="{{ route('dashboard') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                           text-[13px] font-medium transition-all
                    {{ request()->routeIs('dashboard')
                        ? 'active bg-sky-400/15 text-white'
                        : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                    }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md
                        {{ request()->routeIs('dashboard')
                            ? 'bg-sky-400/10 text-sky-300'
                            : 'bg-white/[0.05] text-sky-100/65 group-hover:bg-white/[0.08] group-hover:text-white'
                        }}">
                            <i class="bi bi-speedometer2 text-[14px]"></i>
                        </span>

                        <span>Dashboard</span>

                    </a>



                    {{-- =================================================
                    MEMBERS
                    ================================================== --}}

                    @if($can('Member.view'))

                    <a href="{{ route('admin.members') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.members*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65
                                   group-hover:bg-white/[0.08] group-hover:text-white">
                            <i class="bi bi-people text-[14px]"></i>
                        </span>

                        <span>Members</span>

                    </a>

                    @endif



                    {{-- =================================================
                    FINANCE
                    ================================================== --}}

                    @if($can('Finance.view'))

                    <a href="/admin/finance" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium text-sky-100/75 transition-all
                               hover:bg-white/[0.07] hover:text-white
                               {{ request()->is('admin/finance*')
                                    ? 'active bg-sky-400/15 text-white'
                                    : ''
                               }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-cash-coin text-[14px]"></i>
                        </span>

                        <span>Finance</span>

                    </a>

                    @endif



                    {{-- =================================================
                    INVESTMENTS
                    ================================================== --}}

                    @if($can('Investment.view'))

                    <a href="{{ route('admin.investments') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.investments*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-graph-up-arrow text-[14px]"></i>
                        </span>

                        <span>Investments</span>

                    </a>

                    @endif



                    {{-- =================================================
                    LAND
                    ================================================== --}}

                    @if($can('Land.view'))

                    <a href="{{ route('admin.land') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.land*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-buildings text-[14px]"></i>
                        </span>

                        <span>Land</span>

                    </a>

                    @endif



                    {{-- =================================================
                    PROJECTS
                    ================================================== --}}

                    @if($can('Project.view'))

                    <a href="{{ route('admin.projects') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.projects*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-kanban text-[14px]"></i>
                        </span>

                        <span>Projects</span>

                    </a>

                    @endif



                    {{-- =================================================
                    POLLS
                    ================================================== --}}

                    @if($can('Poll.view'))

                    <a href="{{ route('admin.polls') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.polls*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-ui-checks-grid text-[14px]"></i>
                        </span>

                        <span>Polls</span>

                    </a>

                    @endif



                    {{-- =================================================
                    NOTICES
                    ================================================== --}}

                    @if($can('Notice.view'))

                    <a href="{{ route('admin.notices') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.notices*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-megaphone text-[14px]"></i>
                        </span>

                        <span>Notices</span>

                    </a>

                    @endif



                    {{-- =================================================
                    REPORTS
                    ================================================== --}}

                    @if($can('Report.view'))

                    <a href="{{ route('admin.reports') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                               text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.reports*')
                            ? 'active bg-sky-400/15 text-white'
                            : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                   rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-bar-chart-line text-[14px]"></i>
                        </span>

                        <span>Reports</span>

                    </a>

                    @endif

                </nav>



                {{-- =================================================
                SYSTEM SECTION
                ================================================== --}}

                @if($hasSystemMenu)

                <div class="my-4 border-t border-white/[0.07]"></div>


                <div class="mb-2 px-3 pt-1 text-[10px] font-semibold
                           uppercase tracking-[0.14em] text-sky-200/45">
                    System
                </div>


                <nav class="space-y-0.5">


                    {{-- DOCUMENTS --}}

                    @if($can('Document.view'))

                    <a href="{{ route('admin.documents') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.documents*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-file-earmark-text text-[14px]"></i>
                        </span>

                        <span>Documents</span>

                    </a>

                    @endif



                    {{-- APPROVALS --}}

                    @if($can('Approval.view'))

                    <a href="{{ route('admin.approvals') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.approvals*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-check2-square text-[14px]"></i>
                        </span>

                        <span>Approvals</span>

                    </a>

                    @endif



                    {{-- MAILING --}}

                    @if($can('Mailing.view'))

                    <a href="{{ route('admin.mailing') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.mailing*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-envelope text-[14px]"></i>
                        </span>

                        <span>Mailing</span>

                    </a>

                    @endif



                    {{-- USERS --}}

                    @if($can('User.view'))

                    <a href="{{ route('admin.users') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.users*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-people text-[14px]"></i>
                        </span>

                        <span>Users</span>

                    </a>

                    @endif



                    {{-- ROLES & PERMISSIONS --}}

                    @if($can('Role.view'))

                    <a href="{{ route('admin.roles') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.roles*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-shield-lock text-[14px]"></i>
                        </span>

                        <span>Roles & Permissions</span>

                    </a>

                    @endif

                    {{--User Role Assignment--}}
                    @if($can('Role.view'))
                    <a href="{{ route('admin.user-roles') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1 text-[13px] font-medium transition-all
                        {{ request()->routeIs('admin.user-roles')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                        }}">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-person-gear text-[14px]"></i>
                        </span>
                        <span>User Role Assignment</span>
                    </a>
                    @endif



                    {{-- WEBSITE PAGE --}}

                    @if($can('Setting.view'))

                    <a href="{{ route('admin.landing-page') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.landing-page*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-window-stack text-[14px]"></i>
                        </span>

                        <span>Website Page</span>

                    </a>

                    @endif



                    {{-- SETTINGS --}}

                    @if($can('Setting.view'))

                    <a href="{{ route('admin.settings') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.settings*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-gear text-[14px]"></i>
                        </span>

                        <span>Settings</span>

                    </a>

                    @endif



                    {{-- AUDIT LOGS --}}
                    @if($can('Audit.view'))

                    <a href="{{ route('admin.activity-logs') }}" class="menu-link group flex items-center gap-3 rounded-md px-3 py-1
                                   text-[13px] font-medium transition-all
                            {{ request()->routeIs('admin.activity-logs*')
                                ? 'active bg-sky-400/15 text-white'
                                : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'
                            }}">

                        <span class="flex h-7 w-7 shrink-0 items-center justify-center
                                       rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-clock-history text-[14px]"></i>
                        </span>

                        <span>Audit Logs</span>

                    </a>

                    @endif

                    {{-- BACKUPS --}}
                    @if($can('Backup.view'))
                    <a href="{{ route('admin.backups') }}"
                        class="menu-link group flex items-center gap-3 rounded-md px-3 py-1 text-[13px] font-medium transition-all {{ request()->routeIs('admin.backups*') ? 'active bg-sky-400/15 text-white' : 'text-sky-100/75 hover:bg-white/[0.07] hover:text-white'}}">
                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white/[0.05] text-sky-100/65">
                            <i class="bi bi-hdd-network text-[14px]"></i>
                        </span>
                        <span>Database Backup</span>
                    </a>
                    @endif
                </nav>

                @endif

            </div>



            {{-- =================================================
            SIDEBAR USER
            ================================================== --}}

            <div class="shrink-0 border-t border-white/[0.08] p-3">

                <div class="flex items-center gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center
                           rounded-full border border-white/10 bg-white/10
                           text-xs font-bold text-white">
                        {{
                        strtoupper(
                        substr(
                        $authUser?->name ?? 'U',
                        0,
                        1
                        )
                        )
                        }}
                    </div>


                    <div class="min-w-0 flex-1">

                        <div class="truncate text-[12px] font-semibold text-white">
                            {{ $authUser?->name ?? 'User' }}
                        </div>


                        <div class="mt-0.5 truncate text-[10px] text-sky-200/60">

                            @if($authUser?->isSystemAnalyst())

                            System Analyst

                            @else

                            {{
                            collect(
                            $authUser?->cachedRoleNames() ?? []
                            )
                            ->map(
                            fn ($role) =>
                            str($role)
                            ->replace('_', ' ')
                            ->title()
                            )
                            ->join(', ')
                            }}

                            @endif

                        </div>

                    </div>



                    {{-- Logout --}}

                    <form method="POST" action="{{ route('logout') }}">

                        @csrf

                        <button type="submit" title="Logout" class="flex h-8 w-8 items-center justify-center
                               rounded-full text-sky-100/60 transition
                               hover:bg-white/10 hover:text-white">
                            <i class="bi bi-box-arrow-right text-[15px]"></i>
                        </button>

                    </form>

                </div>

            </div>

        </aside>



        {{-- =====================================================
        MAIN COLUMN
        ====================================================== --}}

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">


            {{-- =================================================
            TOPBAR
            ================================================== --}}

            <header class="z-30 h-[64px] shrink-0 border-b border-slate-200
                   bg-white shadow-[0_1px_3px_rgba(16,24,40,0.06)]">

                <div class="flex h-full items-center justify-between px-4 sm:px-5 lg:px-6">


                    {{-- LEFT --}}

                    <div class="flex min-w-0 items-center gap-3">


                        {{-- Mobile Menu --}}

                        <button type="button" onclick="openSidebar()" class="flex h-9 w-9 shrink-0 items-center justify-center
                               rounded-md border border-slate-200 bg-white
                               text-slate-600 transition hover:bg-slate-50
                               lg:hidden" aria-label="Open menu">
                            <i class="bi bi-list text-lg"></i>
                        </button>



                        {{-- Page Title --}}

                        <div class="flex min-w-0 items-center gap-2">

                            <span class="h-2 w-2 shrink-0 rounded-full
                                   bg-gradient-to-br from-[#145da0] to-sky-400"></span>

                            <h1 class="truncate text-[15px] font-semibold
                                   tracking-wide text-slate-800 sm:text-[16px]">
                                @yield('page_title', 'Dashboard')
                            </h1>

                        </div>

                    </div>



                    {{-- RIGHT --}}
                    <div class="relative flex shrink-0 items-center gap-2 sm:gap-3">

                        @php
                            $unreadNotificationCount=auth()->check()
                                ?app(\App\Services\NotificationService::class)->unreadCount(auth()->user())
                                :0;
                        @endphp

                        {{-- Notification --}}
                        @if($can('Notification.view'))
                            <a href="{{ route('admin.notifications') }}"
                            class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                            title="Notifications">

                                <i class="bi bi-bell text-[16px]"></i>

                                @if($unreadNotificationCount>0)
                                    <span id="notificationBadge"
                                        class="absolute right-0.5 top-0.5 min-w-[16px] rounded-full bg-red-500 px-1 text-center text-[9px] font-bold leading-4 text-white">
                                        {{ $unreadNotificationCount>99?'99+':$unreadNotificationCount }}
                                    </span>
                                @endif
                            </a>
                        @endif



                        {{-- Divider --}}

                        <div class="hidden h-7 w-px bg-slate-200 sm:block"></div>



                        {{-- User --}}

                        <button type="button" onclick="toggleUserMenu(event)" class="group flex items-center gap-2 rounded-full
                               py-1 pl-1 pr-1.5 transition hover:bg-slate-50">

                            <div class="flex h-8 w-8 items-center justify-center
                                   rounded-full bg-gradient-to-br
                                   from-[#0d3b66] to-[#145da0]
                                   text-[11px] font-bold text-white">
                                {{
                                strtoupper(
                                substr(
                                $authUser?->name ?? 'U',
                                0,
                                1
                                )
                                )
                                }}
                            </div>


                            <div class="hidden text-left sm:block">

                                <div class="max-w-[130px] truncate
                                       text-xs font-semibold text-slate-700">
                                    {{ $authUser?->name ?? 'User' }}
                                </div>

                                <div class="max-w-[130px] truncate
                                       text-[10px] text-slate-400">
                                    {{ $authUser?->email ?? '' }}
                                </div>

                            </div>


                            <i class="bi bi-chevron-down hidden text-[10px]
                                   text-slate-400 transition
                                   group-hover:text-slate-600 sm:block"></i>

                        </button>



                        {{-- =================================================
                        USER DROPDOWN
                        ================================================== --}}

                        <div id="userDropdown" class="absolute right-0 top-[48px] z-50 hidden w-60
                               overflow-hidden rounded-md border border-slate-200
                               bg-white shadow-xl shadow-slate-900/10">

                            <div class="border-b border-slate-100 px-4 py-3">

                                <div class="text-[11px] text-slate-400">
                                    Signed in as
                                </div>

                                <div class="mt-1 truncate text-sm font-semibold text-slate-700">
                                    {{ $authUser?->name ?? 'User' }}
                                </div>

                                <div class="mt-0.5 truncate text-[11px] text-slate-400">
                                    {{ $authUser?->email ?? '' }}
                                </div>


                                <div class="mt-1 truncate text-[10px] font-medium text-sky-600">

                                    @if($authUser?->isSystemAnalyst())

                                    System Analyst

                                    @else

                                    {{
                                    collect(
                                    $authUser?->cachedRoleNames()
                                    ?? []
                                    )
                                    ->map(
                                    fn ($role) =>
                                    str($role)
                                    ->replace('_', ' ')
                                    ->title()
                                    )
                                    ->join(', ')
                                    }}

                                    @endif

                                </div>

                            </div>


                            <div class="p-1.5">

                                @if(Route::has('member.profile'))

                                <a href="{{ route('member.profile') }}" class="flex w-full items-center gap-2
                                           rounded-md px-3 py-1.5 text-left
                                           text-xs font-medium text-slate-600
                                           transition hover:bg-slate-50">
                                    <i class="bi bi-person text-sm"></i>

                                    My Profile
                                </a>

                                @endif


                                <form method="POST" action="{{ route('logout') }}">

                                    @csrf

                                    <button type="submit" class="flex w-full items-center gap-2
                                           rounded-md px-3 py-1.5 text-left
                                           text-xs font-medium text-red-500
                                           transition hover:bg-red-50">
                                        <i class="bi bi-box-arrow-right text-sm"></i>

                                        Sign Out
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </header>



            {{-- =================================================
            CONTENT
            ================================================== --}}

            <main class="custom-scrollbar flex-1 overflow-y-auto bg-[#f4f6f9]">

                <div class="min-h-full p-4 sm:p-5 lg:p-6">

                    {{-- Success Message --}}

                    @if(session('success'))

                    <div class="mb-4 rounded-md border border-emerald-200
                               bg-emerald-50 px-4 py-3
                               text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>

                    @endif


                    {{-- Error Message --}}

                    @if(session('error'))

                    <div class="mb-4 rounded-md border border-red-200
                               bg-red-50 px-4 py-3
                               text-sm text-red-700">
                        {{ session('error') }}
                    </div>

                    @endif


                    {{-- Validation Errors --}}

                    @if($errors->any())

                    <div class="mb-4 rounded-md border border-red-200
                               bg-red-50 px-4 py-3 text-sm text-red-700">

                        <div class="font-semibold">
                            Please check the following:
                        </div>

                        <ul class="mt-2 list-disc space-y-1 pl-5">

                            @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                            @endforeach

                        </ul>

                    </div>

                    @endif


                    @yield('content')

                </div>

            </main>

        </div>

    </div>



    {{-- =====================================================
    JAVASCRIPT
    ====================================================== --}}

    <script>
        /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    function openSidebar()
    {
        const sidebar =
            document.getElementById('sidebar');

        const overlay =
            document.getElementById('sidebarOverlay');

        if (!sidebar || !overlay) {
            return;
        }

        sidebar.classList.remove(
            '-translate-x-full'
        );

        overlay.classList.remove(
            'hidden'
        );

        document.body.classList.add(
            'overflow-hidden'
        );
    }


    function closeSidebar()
    {
        const sidebar =
            document.getElementById('sidebar');

        const overlay =
            document.getElementById('sidebarOverlay');

        if (!sidebar || !overlay) {
            return;
        }

        /*
         * On desktop Tailwind's lg:translate-x-0
         * keeps the sidebar visible.
         */

        sidebar.classList.add(
            '-translate-x-full'
        );

        overlay.classList.add(
            'hidden'
        );

        document.body.classList.remove(
            'overflow-hidden'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | User Dropdown
    |--------------------------------------------------------------------------
    */

    function toggleUserMenu(event)
    {
        if (event) {
            event.stopPropagation();
        }

        const dropdown =
            document.getElementById(
                'userDropdown'
            );

        if (!dropdown) {
            return;
        }

        dropdown.classList.toggle(
            'hidden'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Close Dropdown When Clicking Outside
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const dropdown =
                document.getElementById(
                    'userDropdown'
                );

            if (!dropdown) {
                return;
            }

            const button =
                event.target.closest(
                    'button[onclick^="toggleUserMenu"]'
                );


            if (
                !button &&
                !dropdown.contains(event.target)
            ) {
                dropdown.classList.add(
                    'hidden'
                );
            }
        }
    );



    /*
    |--------------------------------------------------------------------------
    | Escape
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key !== 'Escape') {
                return;
            }

            closeSidebar();

            const dropdown =
                document.getElementById(
                    'userDropdown'
                );

            dropdown?.classList.add(
                'hidden'
            );
        }
    );



    /*
    |--------------------------------------------------------------------------
    | Resize
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'resize',
        function () {

            if (window.innerWidth < 1024) {
                return;
            }

            const overlay =
                document.getElementById(
                    'sidebarOverlay'
                );

            overlay?.classList.add(
                'hidden'
            );

            document.body.classList.remove(
                'overflow-hidden'
            );
        }
    );



    /*
    |--------------------------------------------------------------------------
    | Close Sidebar After Mobile Menu Navigation
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('#sidebar a')
        .forEach(function (link) {

            link.addEventListener(
                'click',
                function () {

                    if (
                        window.innerWidth < 1024
                    ) {
                        closeSidebar();
                    }
                }
            );
        });



    /*
    |--------------------------------------------------------------------------
    | Notification Count
    |--------------------------------------------------------------------------
    */

    @if($can('Notification.view'))

        async function loadNotificationCount()
        {
            const badge =
                document.getElementById(
                    'notificationBadge'
                );

            if (!badge) {
                return;
            }


            /*
             * api() comes from the project's
             * existing JavaScript API helper.
             */

            if (
                typeof window.api !== 'function' &&
                typeof api !== 'function'
            ) {
                return;
            }


            try {

                const response =
                    await api(
                        '/api/notifications/unread-count'
                    );


                const count =
                    response?.data?.count ??
                    response?.count ??
                    0;


                if (count > 0) {

                    badge.innerText =
                        count > 99
                            ? '99+'
                            : count;

                    badge.classList.remove(
                        'hidden'
                    );

                } else {

                    badge.classList.add(
                        'hidden'
                    );
                }

            } catch (error) {

                /*
                 * Notification errors must never
                 * break the whole admin layout.
                 */

                console.error(
                    'Notification count error:',
                    error
                );
            }
        }

    @endif



    /*
    |--------------------------------------------------------------------------
    | Start
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            @if($can('Notification.view'))

                loadNotificationCount();

            @endif
        }
    );

    </script>


    @stack('scripts')

</body>

</html>