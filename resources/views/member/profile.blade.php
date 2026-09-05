@extends('layouts.member')

@section('title','My Profile')
@section('page-title','My Profile')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-person-vcard"></i>
            </div>
            <div>
                <h1 class="text-base font-semibold tracking-tight text-slate-800">My Profile</h1>
                <p class="mt-0.5 text-sm text-slate-500">View and update your personal and membership information.</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="loadMemberProfile()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>
            <button type="button" onclick="openEditProfile()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-pencil-square"></i>
                Edit Profile
            </button>
        </div>
    </div>

    <div id="profileLoading" class="rounded-md border border-slate-200 bg-white p-10 text-center">
        <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
        <p class="mt-3 text-sm text-slate-500">Loading profile...</p>
    </div>

    <div id="profileContent" class="hidden space-y-5">
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div id="profilePhotoWrap" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-100 shadow-sm">
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#4680b7] to-[#145da0]  text-2xl font-bold text-white">-</div>
                        </div>
                        <div class="min-w-0">
                            <h2 id="profileName" class="truncate text-lg font-bold text-slate-800">-</h2>
                            <p id="profileEmail" class="mt-0.5 truncate text-sm text-slate-500">-</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span id="profileMemberCode" class="rounded-md bg-indigo-50 px-2 py-1 font-mono text-[10px] font-semibold text-indigo-600">-</span>
                                <span id="profileStatus" class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">Active</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('member.subscriptions') }}" class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-indigo-200 bg-indigo-50 px-4 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                        <i class="bi bi-credit-card"></i>
                        My Subscription
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] text-slate-400">Member Code</p>
                        <p id="memberCode" class="mt-1 font-mono text-base font-bold text-indigo-600">-</p>
                    </div>
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] text-slate-400">Joining Date</p>
                        <p id="joiningDate" class="mt-1 text-base font-bold text-slate-700">-</p>
                    </div>
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] text-emerald-700">Membership</p>
                        <p id="membershipStatus" class="mt-1 text-base font-bold text-emerald-600">-</p>
                    </div>
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                        <i class="bi bi-patch-check"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] text-slate-400">Account Status</p>
                        <p id="accountStatus" class="mt-1 text-base font-bold text-slate-700">-</p>
                    </div>
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <div class="rounded-md border border-slate-200 bg-white xl:col-span-2">
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Personal Information</h2>
                        <p class="text-[11px] text-slate-400">Basic personal and contact details</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-px bg-slate-200 sm:grid-cols-2">
                    <div class="bg-white p-5">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Full Name</p>
                        <p id="personalName" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                    </div>
                    <div class="bg-white p-5">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Email</p>
                        <p id="personalEmail" class="mt-1.5 break-all text-base font-semibold text-slate-700">-</p>
                    </div>
                    <div class="bg-white p-5">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Phone</p>
                        <p id="phone" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                    </div>
                    <div class="bg-white p-5">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Alternate Phone</p>
                        <p id="alternatePhone" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                    </div>
                    <div class="bg-white p-5">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Date of Birth</p>
                        <p id="dateOfBirth" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                    </div>
                    <div class="bg-white p-5">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Gender</p>
                        <p id="gender" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                    </div>
                </div>
            </div>

            <div class="rounded-md border border-slate-200 bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Membership</h2>
                        <p class="text-[11px] text-slate-400">Association membership details</p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <span class="text-xs text-slate-500">Member Code</span>
                        <span id="sideMemberCode" class="font-mono text-sm font-semibold text-indigo-600">-</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <span class="text-xs text-slate-500">Joining Date</span>
                        <span id="sideJoiningDate" class="text-sm font-semibold text-slate-700">-</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <span class="text-xs text-slate-500">Membership Status</span>
                        <span id="sideMembershipStatus" class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">-</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-5 py-4">
                        <span class="text-xs text-slate-500">Language</span>
                        <span id="language" class="text-sm font-semibold text-slate-700">-</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Address Information</h2>
                    <p class="text-[11px] text-slate-400">Residential location details</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-px bg-slate-200 md:grid-cols-3">
                <div class="bg-white p-5 md:col-span-3">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Address</p>
                    <p id="address" class="mt-1.5 text-base font-semibold leading-6 text-slate-700">-</p>
                </div>
                <div class="bg-white p-5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">City</p>
                    <p id="city" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                </div>
                <div class="bg-white p-5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">District</p>
                    <p id="district" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                </div>
                <div class="bg-white p-5">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Mobile</p>
                    <p id="mobile" class="mt-1.5 text-base font-semibold text-slate-700">-</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editProfileModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur-[1px]">
    <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-md border border-slate-200 bg-white shadow-xl">
        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Edit Profile</h3>
                <p class="mt-0.5 text-sm text-slate-400">Update your profile information.</p>
            </div>
            <button type="button" onclick="closeEditProfile()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="editProfileForm" enctype="multipart/form-data" novalidate data-js-validation="1">
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Profile Photo</label>
                    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center">
                        <div id="editPhotoPreview" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-200 text-xl font-bold text-slate-500">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <input id="editProfilePhoto" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="mt-2 text-[11px] text-slate-400">JPG, PNG or WEBP. Maximum 2 MB.</p>
                            <label class="mt-3 flex cursor-pointer items-center gap-2">
                                <input id="removeProfilePhoto" type="checkbox" class="rounded border-slate-300 text-indigo-600">
                                <span class="text-sm text-slate-600">Remove current profile photo</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Phone</label>
                    <input id="editPhone" type="text" maxlength="30" class="h-10 w-full rounded-md border border-slate-300 px-3 text-base text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Alternate Phone</label>
                    <input id="editAlternatePhone" type="text" maxlength="30" class="h-10 w-full rounded-md border border-slate-300 px-3 text-base text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Date of Birth</label>
                    <input id="editDateOfBirth" type="date" class="h-10 w-full rounded-md border border-slate-300 px-3 text-base text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Gender</label>
                    <select id="editGender" class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">City</label>
                    <input id="editCity" type="text" maxlength="100" class="h-10 w-full rounded-md border border-slate-300 px-3 text-base text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">District</label>
                    <input id="editDistrict" type="text" maxlength="100" class="h-10 w-full rounded-md border border-slate-300 px-3 text-base text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Address</label>
                    <textarea id="editAddress" rows="3" maxlength="1000" class="w-full rounded-md border border-slate-300 px-3 py-2 text-base text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"></textarea>
                </div>

                <div id="editProfileError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700 sm:col-span-2"></div>
            </div>

            <div class="sticky bottom-0 flex justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
                <button type="button" onclick="closeEditProfile()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancel</button>
                <button id="saveProfileButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let loadedMemberProfile=null;

async function loadMemberProfile(){
    const loading=document.getElementById('profileLoading');
    const content=document.getElementById('profileContent');

    loading.classList.remove('hidden');
    content.classList.add('hidden');

    loading.innerHTML=`
        <div class="mx-auto h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
        <p class="mt-3 text-sm text-slate-500">Loading profile...</p>
    `;

    try{
        const response=await api('/api/member/profile');
        const data=response.data??{};
        const member=data.member??data.profile??data;
        const user=member.user??data.user??{};

        loadedMemberProfile=member;

        const name=user.name??member.name??'-';
        const email=user.email??member.email??'-';
        const memberCode=member.member_code??'-';
        const status=member.status??'active';
        const joiningDate=formatProfileDate(member.joining_date);
        const dateOfBirth=formatProfileDate(member.date_of_birth);

        document.getElementById('profileName').textContent=name;
        document.getElementById('profileEmail').textContent=email;
        document.getElementById('profileMemberCode').textContent=memberCode;
        renderProfileAvatar(member,name);
        setProfileStatus('profileStatus',status);

        document.getElementById('memberCode').textContent=memberCode;
        document.getElementById('joiningDate').textContent=joiningDate;
        document.getElementById('membershipStatus').textContent=titleCase(status);
        document.getElementById('accountStatus').textContent=user.is_active===false?'Inactive':'Active';

        document.getElementById('personalName').textContent=name;
        document.getElementById('personalEmail').textContent=email;
        document.getElementById('phone').textContent=member.phone??user.mobile??'-';
        document.getElementById('alternatePhone').textContent=member.alternate_phone??'-';
        document.getElementById('dateOfBirth').textContent=dateOfBirth;
        document.getElementById('gender').textContent=member.gender?titleCase(member.gender):'-';

        document.getElementById('sideMemberCode').textContent=memberCode;
        document.getElementById('sideJoiningDate').textContent=joiningDate;
        setProfileStatus('sideMembershipStatus',status);
        document.getElementById('language').textContent=resolveLanguage(user.language);

        document.getElementById('address').textContent=member.address??'-';
        document.getElementById('city').textContent=member.city??'-';
        document.getElementById('district').textContent=member.district??'-';
        document.getElementById('mobile').textContent=user.mobile??member.phone??'-';

        loading.classList.add('hidden');
        content.classList.remove('hidden');
    }catch(error){
        console.error(error);

        loading.innerHTML=`
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
                <i class="bi bi-exclamation-circle text-xl"></i>
            </div>
            <p class="mt-3 text-base font-semibold text-red-600">Failed to load profile.</p>
            <p class="mt-1 text-sm text-slate-400">${escapeProfileHtml(error?.data?.message??error?.message??'Please refresh the page and try again.')}</p>
            <button type="button" onclick="loadMemberProfile()" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                <i class="bi bi-arrow-clockwise"></i>
                Try Again
            </button>
        `;
    }
}

function openEditProfile(){
    if(!loadedMemberProfile){
        return;
    }

    document.getElementById('editPhone').value=loadedMemberProfile.phone??'';
    document.getElementById('editAlternatePhone').value=loadedMemberProfile.alternate_phone??'';
    document.getElementById('editDateOfBirth').value=normalizeDateInput(loadedMemberProfile.date_of_birth);
    document.getElementById('editGender').value=loadedMemberProfile.gender??'';
    document.getElementById('editCity').value=loadedMemberProfile.city??'';
    document.getElementById('editDistrict').value=loadedMemberProfile.district??'';
    document.getElementById('editAddress').value=loadedMemberProfile.address??'';
    document.getElementById('editProfilePhoto').value='';
    document.getElementById('removeProfilePhoto').checked=false;

    renderEditPhotoPreview(loadedMemberProfile);

    document.getElementById('editProfileError').classList.add('hidden');

    const modal=document.getElementById('editProfileModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeEditProfile(){
    const modal=document.getElementById('editProfileModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function renderProfileAvatar(member,name){
    const wrapper=document.getElementById('profilePhotoWrap');
    const photo=member.profile_photo_url??(member.profile_photo?`/storage/${String(member.profile_photo).replace(/^\/+/,'')}`:null);

    if(photo){
        wrapper.innerHTML=`<img src="${escapeProfileHtml(photo)}" alt="${escapeProfileHtml(name)}" class="h-full w-full object-cover">`;
        return;
    }

    const initial=name&&name!=='-'?name.charAt(0).toUpperCase():'-';
    wrapper.innerHTML=`<div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#4680b7] to-[#145da0]  text-2xl font-bold text-white">${escapeProfileHtml(initial)}</div>`;
}

function renderEditPhotoPreview(member){
    const preview=document.getElementById('editPhotoPreview');
    const photo=member.profile_photo_url??(member.profile_photo?`/storage/${String(member.profile_photo).replace(/^\/+/,'')}`:null);

    if(photo){
        preview.innerHTML=`<img src="${escapeProfileHtml(photo)}" class="h-full w-full object-cover" alt="Profile photo">`;
        return;
    }

    const name=member.user?.name??'';
    preview.innerHTML=`<span>${escapeProfileHtml(name?name.charAt(0).toUpperCase():'?')}</span>`;
}

document.getElementById('editProfilePhoto').addEventListener('change',event=>{
    const file=event.target.files?.[0];
    const errorBox=document.getElementById('editProfileError');

    errorBox.classList.add('hidden');

    if(!file){
        renderEditPhotoPreview(loadedMemberProfile);
        return;
    }

    const allowed=['image/jpeg','image/png','image/webp'];

    if(!allowed.includes(file.type)){
        event.target.value='';
        errorBox.textContent='Only JPG, PNG and WEBP images are allowed.';
        errorBox.classList.remove('hidden');
        return;
    }

    if(file.size>2*1024*1024){
        event.target.value='';
        errorBox.textContent='Profile photo cannot exceed 2 MB.';
        errorBox.classList.remove('hidden');
        return;
    }

    document.getElementById('removeProfilePhoto').checked=false;

    const reader=new FileReader();

    reader.onload=e=>{
        document.getElementById('editPhotoPreview').innerHTML=`<img src="${e.target.result}" class="h-full w-full object-cover" alt="Profile preview">`;
    };

    reader.readAsDataURL(file);
});

document.getElementById('removeProfilePhoto').addEventListener('change',event=>{
    if(event.target.checked){
        document.getElementById('editProfilePhoto').value='';
        document.getElementById('editPhotoPreview').innerHTML='<i class="bi bi-person text-2xl"></i>';
        return;
    }

    renderEditPhotoPreview(loadedMemberProfile);
});

document.getElementById('editProfileForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const errorBox=document.getElementById('editProfileError');
    const button=document.getElementById('saveProfileButton');

    errorBox.classList.add('hidden');
    button.disabled=true;
    button.textContent='Saving...';

    try{
        const formData=new FormData();

        appendFormValue(formData,'phone',document.getElementById('editPhone').value);
        appendFormValue(formData,'alternate_phone',document.getElementById('editAlternatePhone').value);
        appendFormValue(formData,'date_of_birth',document.getElementById('editDateOfBirth').value);
        appendFormValue(formData,'gender',document.getElementById('editGender').value);
        appendFormValue(formData,'address',document.getElementById('editAddress').value);
        appendFormValue(formData,'city',document.getElementById('editCity').value);
        appendFormValue(formData,'district',document.getElementById('editDistrict').value);

        const photo=document.getElementById('editProfilePhoto').files?.[0];

        if(photo){
            formData.append('profile_photo',photo);
        }

        if(document.getElementById('removeProfilePhoto').checked){
            formData.append('remove_profile_photo','1');
        }

        formData.append('_method','PUT');

        const response=await api('/api/member/profile',{
            method:'POST',
            body:formData
        });

        if(window.Toast?.success){
            Toast.success(response.message??'Profile updated successfully.');
        }

        closeEditProfile();
        await loadMemberProfile();
    }catch(error){
        let message=error?.data?.message??error?.message??'Failed to update profile.';

        if(error?.data?.errors){
            const errors=Object.values(error.data.errors).flat();

            if(errors.length){
                message=errors[0];
            }
        }

        errorBox.textContent=message;
        errorBox.classList.remove('hidden');
    }finally{
        button.disabled=false;
        button.textContent='Save Changes';
    }
});

function appendFormValue(formData,key,value){
    const cleaned=String(value??'').trim();

    if(cleaned!==''){
        formData.append(key,cleaned);
    }
}

function setProfileStatus(id,status){
    const element=document.getElementById(id);

    if(!element){
        return;
    }

    const styles={
        active:'bg-emerald-50 text-emerald-700',
        pending:'bg-amber-50 text-amber-700',
        suspended:'bg-red-50 text-red-600',
        rejected:'bg-red-50 text-red-600',
        inactive:'bg-slate-100 text-slate-600'
    };

    element.textContent=titleCase(status);
    element.className=`rounded-md px-2 py-1 text-[10px] font-semibold ${styles[status]??styles.inactive}`;
}

function normalizeDateInput(value){
    if(!value){
        return'';
    }

    return String(value).substring(0,10);
}

function formatProfileDate(value){
    if(!value){
        return'-';
    }

    const raw=String(value);
    const date=new Date(raw.length===10?`${raw}T00:00:00`:raw);

    if(Number.isNaN(date.getTime())){
        return raw;
    }

    return date.toLocaleDateString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric'
    });
}

function resolveLanguage(value){
    const languages={
        en:'English',
        bn:'বাংলা'
    };

    return languages[value]??titleCase(value??'');
}

function titleCase(value){
    if(!value){
        return'-';
    }

    return String(value)
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function escapeProfileHtml(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

document.getElementById('editProfileModal').addEventListener('click',event=>{
    if(event.target===event.currentTarget){
        closeEditProfile();
    }
});

document.addEventListener('keydown',event=>{
    if(event.key==='Escape'){
        closeEditProfile();
    }
});

document.addEventListener('DOMContentLoaded',loadMemberProfile);
</script>
@endpush