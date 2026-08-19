@extends('layouts.member')

@section('title', 'My Profile')

@section('page-title', 'My Profile')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">
        My Profile
    </h1>

    <p class="text-sm text-slate-500">
        View your account and membership information.
    </p>
</div>


<!-- Loading -->
<div
    id="profileLoading"
    class="rounded-md border border-slate-200
           bg-white p-10 text-center ">

    <div
        class="mx-auto h-8 w-8 animate-spin rounded-full
               border-4 border-slate-200 border-t-blue-600">
    </div>

    <p class="mt-3 text-sm text-slate-500">
        Loading profile...
    </p>
</div>


<!-- Profile Content -->
<div
    id="profileContent"
    class="hidden space-y-6">


    <!-- Header Card -->
    <div
        class="rounded-md border border-slate-200
               bg-white p-6 ">

        <div
            class="flex flex-col gap-5 sm:flex-row
                   sm:items-center sm:justify-between">

            <div class="flex items-center gap-4">

                <div
                    id="profileInitial"
                    class="flex h-16 w-16 shrink-0
                           items-center justify-center
                           rounded-full bg-blue-600
                           text-2xl font-bold text-white">

                    -
                </div>

                <div>

                    <h2
                        id="profileName"
                        class="text-xl font-bold text-slate-900">
                        -
                    </h2>

                    <p
                        id="profileEmail"
                        class="text-sm text-slate-500">
                        -
                    </p>

                </div>

            </div>


            <div>

                <span
                    id="profileStatus"
                    class="inline-flex rounded-full
                           bg-emerald-100 px-3 py-1.5
                           text-xs font-semibold
                           text-emerald-700">

                    Active

                </span>

            </div>

        </div>

    </div>


    <!-- Membership Information -->
    <div
        class="rounded-md border border-slate-200
               bg-white ">

        <div
            class="border-b border-slate-200 px-6 py-4">

            <h2 class="font-semibold text-slate-900">
                Membership Information
            </h2>

        </div>


        <div
            class="grid gap-6 p-6 sm:grid-cols-2
                   lg:grid-cols-3">


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Member Code
                </p>

                <p
                    id="memberCode"
                    class="mt-2 font-semibold text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Joining Date
                </p>

                <p
                    id="joiningDate"
                    class="mt-2 font-semibold text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Membership Status
                </p>

                <p
                    id="membershipStatus"
                    class="mt-2 font-semibold text-slate-800">
                    -
                </p>
            </div>

        </div>

    </div>


    <!-- Personal Information -->
    <div
        class="rounded-md border border-slate-200
               bg-white ">

        <div
            class="border-b border-slate-200 px-6 py-4">

            <h2 class="font-semibold text-slate-900">
                Personal Information
            </h2>

        </div>


        <div
            class="grid gap-6 p-6 sm:grid-cols-2
                   lg:grid-cols-3">


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Full Name
                </p>

                <p
                    id="personalName"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Email
                </p>

                <p
                    id="personalEmail"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Phone
                </p>

                <p
                    id="phone"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Alternate Phone
                </p>

                <p
                    id="alternatePhone"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Date of Birth
                </p>

                <p
                    id="dateOfBirth"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>
            </div>


            <div>
                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Gender
                </p>

                <p
                    id="gender"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>
            </div>

        </div>

    </div>


    <!-- Address -->
    <div
        class="rounded-md border border-slate-200
               bg-white ">

        <div
            class="border-b border-slate-200 px-6 py-4">

            <h2 class="font-semibold text-slate-900">
                Address
            </h2>

        </div>


        <div
            class="grid gap-6 p-6 sm:grid-cols-2
                   lg:grid-cols-3">


            <div class="sm:col-span-2 lg:col-span-3">

                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    Address
                </p>

                <p
                    id="address"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>

            </div>


            <div>

                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    City
                </p>

                <p
                    id="city"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>

            </div>


            <div>

                <p class="text-xs font-medium uppercase
                          tracking-wide text-slate-400">
                    District
                </p>

                <p
                    id="district"
                    class="mt-2 font-medium text-slate-800">
                    -
                </p>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

async function loadMemberProfile() {

    const loading =
        document.getElementById('profileLoading');

    const content =
        document.getElementById('profileContent');

    try {

        const response =
            await api('/api/member/profile');

        const data =
            response.data || {};

        /*
        |--------------------------------------------------------------------------
        | Resolve Member / User
        |--------------------------------------------------------------------------
        */

        const member =
            data.member ||
            data.profile ||
            data;

        const user =
            member.user ||
            data.user ||
            {};


        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        const name =
            user.name ||
            member.name ||
            '-';

        const email =
            user.email ||
            member.email ||
            '-';

        document.getElementById('profileName')
            .textContent = name;

        document.getElementById('profileEmail')
            .textContent = email;

        document.getElementById('profileInitial')
            .textContent =
            name !== '-'
                ? name.charAt(0).toUpperCase()
                : '-';


        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        const status =
            member.status ||
            'active';

        document.getElementById('profileStatus')
            .textContent = status;

        document.getElementById('membershipStatus')
            .textContent = status;


        /*
        |--------------------------------------------------------------------------
        | Membership
        |--------------------------------------------------------------------------
        */

        document.getElementById('memberCode')
            .textContent =
            member.member_code || '-';

        document.getElementById('joiningDate')
            .textContent =
            member.joining_date || '-';


        /*
        |--------------------------------------------------------------------------
        | Personal Information
        |--------------------------------------------------------------------------
        */

        document.getElementById('personalName')
            .textContent = name;

        document.getElementById('personalEmail')
            .textContent = email;

        document.getElementById('phone')
            .textContent =
            member.phone || '-';

        document.getElementById('alternatePhone')
            .textContent =
            member.alternate_phone || '-';

        document.getElementById('dateOfBirth')
            .textContent =
            member.date_of_birth || '-';

        document.getElementById('gender')
            .textContent =
            member.gender || '-';


        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        document.getElementById('address')
            .textContent =
            member.address || '-';

        document.getElementById('city')
            .textContent =
            member.city || '-';

        document.getElementById('district')
            .textContent =
            member.district || '-';


        loading.classList.add('hidden');

        content.classList.remove('hidden');

    } catch (error) {

        console.error(error);

        loading.innerHTML = `
            <div class="text-red-600">

                <i class="bi bi-exclamation-circle text-2xl"></i>

                <p class="mt-2 font-medium">
                    Failed to load profile.
                </p>

                <p class="text-sm text-slate-500">
                    Please refresh the page and try again.
                </p>

            </div>
        `;
    }
}


document.addEventListener(
    'DOMContentLoaded',
    loadMemberProfile
);

</script>

@endpush
