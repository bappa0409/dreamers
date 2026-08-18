@extends('layouts.member')

@section('title', 'Dashboard')

@section('page-title', 'Member Dashboard')

@section('content')

<div class="mb-6">

    <h1 class="text-2xl font-bold text-slate-900">
        Member Dashboard
    </h1>

    <p class="mt-1 text-sm text-slate-500">
        Welcome back. Here is an overview of your membership.
    </p>

</div>


<!-- Loading -->
<div
    id="dashboardLoading"
    class="rounded-md border border-slate-200
           bg-white p-8 text-center ">

    <div
        class="mx-auto h-8 w-8 animate-spin rounded-full
               border-4 border-slate-200 border-t-blue-600">
    </div>

    <p class="mt-3 text-sm text-slate-500">
        Loading dashboard...
    </p>

</div>


<!-- Dashboard -->
<div
    id="dashboardContent"
    class="hidden">


    <!-- Stats -->
    <div
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-4">


        <!-- Investments -->
        <div
            class="rounded-md border border-slate-200
                   bg-white p-5 ">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm text-slate-500">
                        Investments
                    </p>

                    <h3
                        id="investmentCount"
                        class="mt-2 text-2xl font-bold
                               text-slate-900">
                        0
                    </h3>

                </div>

                <div
                    class="flex h-11 w-11 items-center
                           justify-center rounded-md
                           bg-blue-100 text-blue-600">

                    <i class="bi bi-graph-up-arrow text-xl"></i>

                </div>

            </div>

        </div>


        <!-- Projects -->
        <div
            class="rounded-md border border-slate-200
                   bg-white p-5 ">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm text-slate-500">
                        Projects
                    </p>

                    <h3
                        id="projectCount"
                        class="mt-2 text-2xl font-bold
                               text-slate-900">
                        0
                    </h3>

                </div>

                <div
                    class="flex h-11 w-11 items-center
                           justify-center rounded-md
                           bg-emerald-100 text-emerald-600">

                    <i class="bi bi-kanban text-xl"></i>

                </div>

            </div>

        </div>


        <!-- Land -->
        <div
            class="rounded-md border border-slate-200
                   bg-white p-5 ">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm text-slate-500">
                        Land Investments
                    </p>

                    <h3
                        id="landCount"
                        class="mt-2 text-2xl font-bold
                               text-slate-900">
                        0
                    </h3>

                </div>

                <div
                    class="flex h-11 w-11 items-center
                           justify-center rounded-md
                           bg-amber-100 text-amber-600">

                    <i class="bi bi-geo-alt text-xl"></i>

                </div>

            </div>

        </div>


        <!-- Polls -->
        <div
            class="rounded-md border border-slate-200
                   bg-white p-5 ">

            <div class="flex items-center justify-between">

                <div>

                    <p class="text-sm text-slate-500">
                        Available Polls
                    </p>

                    <h3
                        id="pollCount"
                        class="mt-2 text-2xl font-bold
                               text-slate-900">
                        0
                    </h3>

                </div>

                <div
                    class="flex h-11 w-11 items-center
                           justify-center rounded-md
                           bg-purple-100 text-purple-600">

                    <i class="bi bi-bar-chart text-xl"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- Profile + Quick Links -->
    <div
        class="mt-6 grid gap-6 lg:grid-cols-3">


        <!-- Profile -->
        <div
            class="rounded-md border border-slate-200
                   bg-white p-6  lg:col-span-2">

            <div
                class="flex items-center justify-between">

                <div>

                    <h2 class="font-semibold text-slate-900">
                        My Membership
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Your current membership information.
                    </p>

                </div>

                <span
                    id="memberStatus"
                    class="rounded-full bg-emerald-100
                           px-3 py-1 text-xs font-semibold
                           text-emerald-700">

                    Active

                </span>

            </div>


            <div
                class="mt-6 grid gap-5 sm:grid-cols-2">


                <div>

                    <p class="text-xs text-slate-400">
                        Name
                    </p>

                    <p
                        id="memberName"
                        class="mt-1 font-medium text-slate-800">
                        -
                    </p>

                </div>


                <div>

                    <p class="text-xs text-slate-400">
                        Member Code
                    </p>

                    <p
                        id="memberCode"
                        class="mt-1 font-medium text-slate-800">
                        -
                    </p>

                </div>


                <div>

                    <p class="text-xs text-slate-400">
                        Email
                    </p>

                    <p
                        id="memberEmail"
                        class="mt-1 font-medium text-slate-800">
                        -
                    </p>

                </div>


                <div>

                    <p class="text-xs text-slate-400">
                        Joining Date
                    </p>

                    <p
                        id="joiningDate"
                        class="mt-1 font-medium text-slate-800">
                        -
                    </p>

                </div>

            </div>

        </div>


        <!-- Quick Links -->
        <div
            class="rounded-md border border-slate-200
                   bg-white p-6 ">

            <h2 class="font-semibold text-slate-900">
                Quick Access
            </h2>

            <div class="mt-4 space-y-2">

                <a
                    href="{{ route('member.profile') }}"
                    class="flex items-center gap-3 rounded-md
                           border border-slate-200 p-3
                           text-sm font-medium
                           hover:bg-slate-50">

                    <i class="bi bi-person text-blue-600"></i>

                    My Profile

                </a>


                <a
                    href="{{ route('member.investments') }}"
                    class="flex items-center gap-3 rounded-md
                           border border-slate-200 p-3
                           text-sm font-medium
                           hover:bg-slate-50">

                    <i class="bi bi-graph-up-arrow text-emerald-600"></i>

                    My Investments

                </a>


                <a
                    href="{{ route('member.projects') }}"
                    class="flex items-center gap-3 rounded-md
                           border border-slate-200 p-3
                           text-sm font-medium
                           hover:bg-slate-50">

                    <i class="bi bi-kanban text-purple-600"></i>

                    My Projects

                </a>


                <a
                    href="{{ route('member.polls') }}"
                    class="flex items-center gap-3 rounded-md
                           border border-slate-200 p-3
                           text-sm font-medium
                           hover:bg-slate-50">

                    <i class="bi bi-bar-chart text-amber-600"></i>

                    Polls

                </a>

            </div>

        </div>

    </div>

</div>


@endsection


@push('scripts')

<script>

async function loadMemberDashboard() {

    const loading =
        document.getElementById('dashboardLoading');

    const content =
        document.getElementById('dashboardContent');

    try {

        const response =
            await api('/api/member/dashboard');


        const data =
            response.data || {};


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        const member =
            data.member ||
            data.profile ||
            {};


        const user =
            member.user ||
            data.user ||
            {};


        document.getElementById('memberName')
            .textContent =
            user.name ||
            member.name ||
            '-';


        document.getElementById('memberEmail')
            .textContent =
            user.email ||
            member.email ||
            '-';


        document.getElementById('memberCode')
            .textContent =
            member.member_code ||
            '-';


        document.getElementById('joiningDate')
            .textContent =
            member.joining_date ||
            '-';


        document.getElementById('memberStatus')
            .textContent =
            member.status ||
            'Active';


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        document.getElementById('investmentCount')
            .textContent =
            data.investments_count ??
            data.investment_count ??
            0;


        document.getElementById('projectCount')
            .textContent =
            data.projects_count ??
            data.project_count ??
            0;


        document.getElementById('landCount')
            .textContent =
            data.land_investments_count ??
            data.land_count ??
            0;


        document.getElementById('pollCount')
            .textContent =
            data.polls_count ??
            data.poll_count ??
            0;


        loading.classList.add('hidden');

        content.classList.remove('hidden');

    } catch (error) {

        console.error(error);

        loading.innerHTML = `
            <div class="text-red-600">
                <i class="bi bi-exclamation-circle text-2xl"></i>

                <p class="mt-2 font-medium">
                    Failed to load dashboard.
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Please refresh the page and try again.
                </p>
            </div>
        `;
    }
}


document.addEventListener(
    'DOMContentLoaded',
    loadMemberDashboard
);

</script>

@endpush