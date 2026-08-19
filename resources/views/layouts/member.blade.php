<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Member Portal') |
        {{ setting('organization_name','Dreamers Association') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @if(setting('site_favicon'))
        <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    @stack('styles')
</head>

<body class="bg-slate-100 text-slate-800">

    <div class="min-h-screen">

        <!-- Mobile Overlay -->
        <div
            id="memberSidebarOverlay"
            class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden">
        </div>


        <!-- Sidebar -->
        <aside
            id="memberSidebar"
            class="fixed inset-y-0 left-0 z-50 w-64
                   -translate-x-full bg-slate-900 text-white
                   transition-transform duration-300
                   lg:translate-x-0">

            <div class="flex h-full flex-col">

                <!-- Logo -->
                <div class="flex h-16 items-center
                            border-b border-white/10 px-5">

                    <div
                        class="flex h-10 w-10 items-center
                               justify-center rounded-md
                               bg-blue-600">

                        <i class="bi bi-stars text-xl"></i>

                    </div>

                    <div class="ml-3">
                        <h1 class="font-bold">
                            Dreamers
                        </h1>

                        <p class="text-xs text-slate-400">
                            Member Portal
                        </p>
                    </div>
                </div>


                <!-- Navigation -->
                <nav class="flex-1 space-y-1 overflow-y-auto p-4">

                    <a
                        href="{{ route('member.dashboard') }}"
                        class="flex items-center gap-3 rounded-md
                               px-4 py-3 text-sm font-medium
                               transition
                               {{ request()->routeIs('member.dashboard')
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">

                        <i class="bi bi-grid-1x2"></i>

                        Dashboard
                    </a>


                    <a
                        href="{{ route('member.profile') }}"
                        class="flex items-center gap-3 rounded-md
                               px-4 py-3 text-sm font-medium
                               transition
                               {{ request()->routeIs('member.profile')
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">

                        <i class="bi bi-person"></i>

                        My Profile
                    </a>


                    <a
                        href="{{ route('member.investments') }}"
                        class="flex items-center gap-3 rounded-md
                               px-4 py-3 text-sm font-medium
                               transition
                               {{ request()->routeIs('member.investments')
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">

                        <i class="bi bi-graph-up-arrow"></i>

                        My Investments
                    </a>


                    <a
                        href="{{ route('member.projects') }}"
                        class="flex items-center gap-3 rounded-md
                               px-4 py-3 text-sm font-medium
                               transition
                               {{ request()->routeIs('member.projects')
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">

                        <i class="bi bi-kanban"></i>

                        My Projects
                    </a>


                    <a
                        href="{{ route('member.land-investments') }}"
                        class="flex items-center gap-3 rounded-md
                               px-4 py-3 text-sm font-medium
                               transition
                               {{ request()->routeIs('member.land-investments')
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">

                        <i class="bi bi-geo-alt"></i>

                        Land Investments
                    </a>


                    <a
                        href="{{ route('member.polls') }}"
                        class="flex items-center gap-3 rounded-md
                               px-4 py-3 text-sm font-medium
                               transition
                               {{ request()->routeIs('member.polls')
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">

                        <i class="bi bi-bar-chart"></i>

                        Polls
                    </a>

                </nav>


                <!-- User -->
                <div class="border-t border-white/10 p-4">

                    <div class="mb-3 flex items-center gap-3">

                        <div
                            class="flex h-10 w-10 items-center
                                   justify-center rounded-full
                                   bg-blue-600 font-semibold">

                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}

                        </div>

                        <div class="min-w-0">

                            <p class="truncate text-sm font-semibold">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="truncate text-xs text-slate-400">
                                Member
                            </p>

                        </div>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('member.logout') }}">

                        @csrf

                        <button
                            type="submit"
                            class="flex w-full items-center gap-3
                                   rounded-md px-4 py-3 text-sm
                                   text-slate-300 transition
                                   hover:bg-red-500/10
                                   hover:text-red-400">

                            <i class="bi bi-box-arrow-right"></i>

                            Logout
                        </button>

                    </form>

                </div>

            </div>

        </aside>


        <!-- Main -->
        <div class="min-h-screen lg:pl-64">

            <!-- Topbar -->
            <header
                class="sticky top-0 z-30 flex h-16
                       items-center justify-between
                       border-b border-slate-200
                       bg-white/95 px-4 backdrop-blur
                       sm:px-6">

                <button
                    id="memberSidebarToggle"
                    type="button"
                    class="rounded-md p-2 text-slate-600
                           hover:bg-slate-100 lg:hidden">

                    <i class="bi bi-list text-2xl"></i>

                </button>


                <div class="hidden sm:block">

                    <h2 class="text-sm font-semibold text-slate-800">
                        @yield('page-title', 'Member Portal')
                    </h2>

                </div>


                <div class="ml-auto flex items-center gap-3">

                    <span
                        class="hidden text-sm text-slate-500 sm:block">

                        Welcome,
                        <span class="font-semibold text-slate-700">
                            {{ auth()->user()->name }}
                        </span>

                    </span>

                    <div
                        class="flex h-9 w-9 items-center
                               justify-center rounded-full
                               bg-blue-100 text-sm font-bold
                               text-blue-700">

                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}

                    </div>

                </div>

            </header>


            <!-- Content -->
            <main class="p-4 sm:p-6">

                @yield('content')

            </main>

        </div>

    </div>


    <script>
        const memberSidebar =
            document.getElementById('memberSidebar');

        const memberSidebarToggle =
            document.getElementById('memberSidebarToggle');

        const memberSidebarOverlay =
            document.getElementById('memberSidebarOverlay');


        function openMemberSidebar() {

            memberSidebar.classList.remove(
                '-translate-x-full'
            );

            memberSidebarOverlay.classList.remove(
                'hidden'
            );
        }


        function closeMemberSidebar() {

            memberSidebar.classList.add(
                '-translate-x-full'
            );

            memberSidebarOverlay.classList.add(
                'hidden'
            );
        }


        memberSidebarToggle?.addEventListener(
            'click',
            openMemberSidebar
        );


        memberSidebarOverlay?.addEventListener(
            'click',
            closeMemberSidebar
        );
    </script>


    @stack('scripts')

</body>

</html>