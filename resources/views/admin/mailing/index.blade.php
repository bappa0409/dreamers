@extends('layouts.admin')

@section('title','Mailing')
@section('page_title','Mailing')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">

        <div class="flex items-start gap-3">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-envelope-paper"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">
                    Mailing
                </h1>

                <p class="text-sm text-slate-500">
                    Create and send email campaigns to association members.
                </p>
            </div>

        </div>


        @if(auth()->user()->hasPermission('Mail.create'))

            <button
                type="button"
                onclick="openCampaignModal()"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-plus-lg"></i>
                New Campaign
            </button>

        @endif

    </div>


    {{-- =========================================================
    SEARCH
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Campaigns
                    </p>

                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by subject or email content
                    </p>
                </div>

            </div>


            <div class="flex w-full items-center lg:w-auto">

                <div class="relative w-full lg:w-80">

                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search campaigns..."
                        class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    >

                </div>


                <select
                    id="statusFilter"
                    class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400"
                >
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="sending">Sending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="cancelled">Cancelled</option>
                </select>


                <button
                    type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>

            </div>

        </div>

    </div>


    {{-- =========================================================
    TABLE
    ========================================================== --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        <div class="w-full overflow-hidden">

            <table class="w-full table-fixed text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr>

                        <th class="w-[34%] px-3 py-3 text-left text-xs font-semibold text-slate-600">
                            Campaign
                        </th>

                        <th class="w-[11%] px-3 py-3 text-left text-xs font-semibold text-slate-600">
                            Recipients
                        </th>

                        <th class="w-[10%] px-3 py-3 text-left text-xs font-semibold text-slate-600">
                            Sent
                        </th>

                        <th class="w-[10%] px-3 py-3 text-left text-xs font-semibold text-slate-600">
                            Failed
                        </th>

                        <th class="w-[15%] px-3 py-3 text-left text-xs font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="w-[20%] px-3 py-3 text-right text-xs font-semibold text-slate-600">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody id="campaignTable">

                    <tr>
                        <td
                            colspan="6"
                            class="px-5 py-10 text-center text-slate-400"
                        >
                            Loading campaigns...
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>


        <div
            id="paginationContainer"
            class="border-t border-slate-200 px-4 py-3"
        ></div>

    </div>

</div>


{{-- =============================================================
CAMPAIGN MODAL
============================================================= --}}
<div
    id="campaignModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-3">

            <div class="flex min-w-0 items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-envelope-plus"></i>
                </div>

                <div>
                    <h2
                        id="campaignModalTitle"
                        class="text-lg font-bold text-slate-800"
                    >
                        New Campaign
                    </h2>

                    <p class="text-xs text-slate-500">
                        Write the subject and email content.
                    </p>
                </div>

            </div>


            <button
                type="button"
                onclick="closeCampaignModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <form
            id="campaignForm"
            class="flex min-h-0 flex-1 flex-col"
         novalidate data-js-validation="1">

            <div class="space-y-4 overflow-y-auto p-5">

                <div>

                    <label class="form-label">
                        Subject *
                    </label>

                    <input
                        id="subject"
                        maxlength="255"
                        class="app-input"
                        placeholder="Campaign subject"
                    >

                </div>


                <div>

                    <label class="form-label">
                        Email Content *
                    </label>

                    <textarea
                        id="body"
                        rows="10"
                        maxlength="20000"
                        class="app-input resize-none"
                        placeholder="Write email content..."
                    ></textarea>

                </div>


                <div
                    id="campaignError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"
                ></div>

            </div>


            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">

                <button
                    type="button"
                    onclick="closeCampaignModal()"
                    class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="saveCampaignButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                >
                    Save Campaign
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
RECIPIENT MODAL
============================================================= --}}
<div
    id="recipientModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-3">

            <div class="flex min-w-0 items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-people"></i>
                </div>

                <div class="min-w-0">

                    <h2 class="text-lg font-bold text-slate-800">
                        Add Recipients
                    </h2>

                    <p
                        id="recipientCampaignInfo"
                        class="mt-1 truncate text-xs text-slate-500"
                    ></p>

                </div>

            </div>


            <button
                type="button"
                onclick="closeRecipientModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div class="space-y-4 overflow-y-auto p-5">

            <input
                id="recipientCampaignId"
                type="hidden"
            >


            <div>

                <label class="form-label">
                    Audience
                </label>

                <select
                    id="audienceType"
                    class="app-input"
                >
                    <option value="all_active_members">
                        All Active Members
                    </option>

                    <option value="selected_members">
                        Selected Members
                    </option>

                    <option value="manual">
                        Manual Email
                    </option>
                </select>

            </div>


            {{-- Selected Members --}}
            <div
                id="selectedMembersSection"
                class="hidden"
            >

                <div class="relative">

                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="memberSearch"
                        type="text"
                        placeholder="Search members..."
                        class="app-input !pl-9"
                    >

                </div>


                <div
                    id="memberResults"
                    class="mt-2 max-h-64 overflow-y-auto rounded-md border border-slate-200 p-2"
                >
                    <p class="p-5 text-center text-xs text-slate-400">
                        Search active members.
                    </p>
                </div>

            </div>


            {{-- Manual --}}
            <div
                id="manualSection"
                class="hidden space-y-3"
            >

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <div>
                        <label class="form-label">
                            Name
                        </label>

                        <input
                            id="manualName"
                            type="text"
                            placeholder="Recipient name"
                            class="app-input"
                        >
                    </div>


                    <div>
                        <label class="form-label">
                            Email *
                        </label>

                        <input
                            id="manualEmail"
                            type="email"
                            placeholder="name@example.com"
                            class="app-input"
                        >
                    </div>

                </div>

            </div>


            <div
                id="recipientError"
                class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"
            ></div>

        </div>


        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">

            <button
                type="button"
                onclick="closeRecipientModal()"
                class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                id="saveRecipientsButton"
                type="button"
                onclick="saveRecipients()"
                class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
            >
                Add Recipients
            </button>

        </div>

    </div>

</div>


{{-- =============================================================
DETAILS MODAL
============================================================= --}}
<div
    id="detailsModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">

            <div class="flex min-w-0 items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                    <i class="bi bi-envelope-open"></i>
                </div>

                <div class="min-w-0">

                    <h2
                        id="detailsTitle"
                        class="truncate text-lg font-bold text-slate-800"
                    >
                        Campaign
                    </h2>

                    <p
                        id="detailsSubtitle"
                        class="mt-1 truncate text-xs text-slate-500"
                    ></p>

                </div>

            </div>


            <button
                type="button"
                onclick="AdminUI.closeModal('detailsModal')"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div
            id="detailsContent"
            class="min-h-0 flex-1 overflow-y-auto p-5"
        ></div>

    </div>

</div>


<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>

@endsection


@push('scripts')

<script>
let campaigns=[];
let editingCampaign=null;
let currentPage=1;
let lastPage=1;
let total=0;
let selectedUsers=new Set();

const canUpdate=@json(
    auth()->user()->hasPermission('Mail.update')
);

const canSend=@json(
    auth()->user()->hasPermission('Mail.send')
);

const canDelete=@json(
    auth()->user()->hasPermission('Mail.delete')
);

const el={
    table:document.getElementById('campaignTable'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),

    form:document.getElementById('campaignForm'),
    subject:document.getElementById('subject'),
    body:document.getElementById('body'),
    saveButton:document.getElementById('saveCampaignButton'),

    audienceType:document.getElementById('audienceType'),
    memberSearch:document.getElementById('memberSearch'),
    memberResults:document.getElementById('memberResults'),

    manualName:document.getElementById('manualName'),
    manualEmail:document.getElementById('manualEmail')
};


/*
|--------------------------------------------------------------------------
| Load Campaigns
|--------------------------------------------------------------------------
*/

async function loadCampaigns(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading campaigns...',
            6
        );

    const query=
        AdminUI.query({
            search:
                el.search.value.trim(),

            status:
                el.statusFilter.value,

            page
        });

    try{
        const response=
            await api(
                `/api/mail-campaigns?${query}`
            );

        const paginator=
            response.data??{};

        campaigns=
            Array.isArray(
                paginator.data
            )
                ?paginator.data
                :[];

        currentPage=
            Number(
                paginator.current_page??1
            );

        lastPage=
            Number(
                paginator.last_page??1
            );

        total=
            Number(
                paginator.total??0
            );


        renderCampaigns();


        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadCampaigns
        });

    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                6
            );


        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadCampaigns
        });
    }
}


/*
|--------------------------------------------------------------------------
| Render Campaigns
|--------------------------------------------------------------------------
*/

function renderCampaigns(){
    if(!campaigns.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No mail campaigns found.',
                6
            );

        return;
    }


    el.table.innerHTML=
        campaigns.map(item=>`

            <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50">

                {{-- Campaign --}}
                <td class="min-w-0 overflow-hidden px-3 py-4">

                    <p
                        class="truncate text-xs font-semibold text-slate-800"
                        title="${AdminUI.escapeHtml(item.subject??'')}"
                    >
                        ${AdminUI.escapeHtml(
                            item.subject??
                            'Untitled Campaign'
                        )}
                    </p>


                    <p
                        class="mt-1 truncate text-[10px] text-slate-400"
                        title="${AdminUI.escapeHtml(AdminUI.formatDate(item.created_at,true))}"
                    >
                        ${AdminUI.formatDate(
                            item.created_at,
                            true
                        )}
                    </p>

                </td>


                {{-- Recipients --}}
                <td class="overflow-hidden px-3 py-4">

                    <p class="truncate text-xs font-semibold text-slate-700">
                        ${item.recipients_count??0}
                    </p>

                </td>


                {{-- Sent --}}
                <td class="overflow-hidden px-3 py-4">

                    <p class="truncate text-xs font-semibold text-emerald-700">
                        ${item.sent_count??0}
                    </p>

                </td>


                {{-- Failed --}}
                <td class="overflow-hidden px-3 py-4">

                    <p class="truncate text-xs font-semibold ${
                        Number(item.failed_count??0)>0
                            ?'text-red-600'
                            :'text-slate-400'
                    }">
                        ${item.failed_count??0}
                    </p>

                </td>


                {{-- Status --}}
                <td class="overflow-hidden px-3 py-4">

                    ${AdminUI.statusBadge(
                        item.status
                    )}

                </td>


                {{-- Actions --}}
                <td class="px-3 py-4">

                    <div class="flex items-center justify-end gap-1">

                        <button
                            type="button"
                            onclick="viewCampaign(${item.id})"
                            title="View Campaign"
                            class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-sky-50 text-sky-700 transition hover:bg-sky-100"
                        >
                            <i class="bi bi-eye text-xs"></i>
                        </button>


                        ${
                            canUpdate&&
                            item.status==='draft'
                                ?`
                                    <button
                                        type="button"
                                        onclick="openRecipientModal(${item.id})"
                                        title="Manage Recipients"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                    >
                                        <i class="bi bi-people text-xs"></i>
                                    </button>


                                    <button
                                        type="button"
                                        onclick="editCampaign(${item.id})"
                                        title="Edit Campaign"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                    >
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>
                                `
                                :''
                        }


                        ${
                            canSend&&
                            item.status==='draft'
                                ?`
                                    <button
                                        type="button"
                                        onclick="sendCampaign(${item.id})"
                                        title="Send Campaign"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                                    >
                                        <i class="bi bi-send text-xs"></i>
                                    </button>
                                `
                                :''
                        }


                        ${
                            canDelete&&
                            item.status==='draft'
                                ?`
                                    <button
                                        type="button"
                                        onclick="deleteCampaign(${item.id})"
                                        title="Delete Campaign"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                    >
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                `
                                :''
                        }

                    </div>

                </td>

            </tr>

        `).join('');
}


/*
|--------------------------------------------------------------------------
| Campaign Modal
|--------------------------------------------------------------------------
*/

window.openCampaignModal=
function(item=null){
    editingCampaign=item;

    AdminUI.resetForm(
        el.form
    );

    AdminUI.clearError(
        'campaignError'
    );


    document.getElementById(
        'campaignModalTitle'
    ).innerText=
        item
            ?'Edit Campaign'
            :'New Campaign';


    el.saveButton.innerText=
        item
            ?'Update Campaign'
            :'Save Campaign';


    if(item){
        el.subject.value=
            item.subject??'';

        el.body.value=
            item.body??'';
    }


    AdminUI.openModal(
        'campaignModal'
    );
};


window.closeCampaignModal=
function(){
    AdminUI.closeModal(
        'campaignModal'
    );

    editingCampaign=null;
};


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

window.editCampaign=
function(id){
    const item=
        campaigns.find(
            item=>
                Number(item.id)===
                Number(id)
        );


    if(!item){
        Toast.error(
            'Campaign not found.'
        );

        return;
    }


    openCampaignModal(item);
};


/*
|--------------------------------------------------------------------------
| Save Campaign
|--------------------------------------------------------------------------
*/

el.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'campaignError'
        );


        const data={
            subject:
                el.subject.value.trim(),

            body:
                el.body.value.trim()
        };


        if(
            !data.subject||
            !data.body
        ){
            AdminUI.showError(
                'campaignError',
                'Subject and content are required.'
            );

            return;
        }


        AdminUI.setLoading(
            el.saveButton,
            editingCampaign
                ?'Updating...'
                :'Saving...'
        );


        try{
            const editing=
                Boolean(
                    editingCampaign
                );

            const pageAfterSave=
                editing
                    ?currentPage
                    :1;


            await api(
                editing
                    ?`/api/mail-campaigns/${editingCampaign.id}`
                    :'/api/mail-campaigns',
                {
                    method:
                        editing
                            ?'PUT'
                            :'POST',

                    body:
                        JSON.stringify(
                            data
                        )
                }
            );


            closeCampaignModal();


            Toast.success(
                editing
                    ?'Campaign updated successfully.'
                    :'Campaign created successfully.'
            );


            await loadCampaigns(
                pageAfterSave
            );

        }catch(error){
            AdminUI.showError(
                'campaignError',
                AdminUI.extractError(
                    error
                )
            );

        }finally{
            AdminUI.resetLoading(
                el.saveButton
            );
        }
    }
);


/*
|--------------------------------------------------------------------------
| Open Recipient Modal
|--------------------------------------------------------------------------
*/

window.openRecipientModal=
function(id){
    const campaign=
        campaigns.find(
            item=>
                Number(item.id)===
                Number(id)
        );


    if(!campaign){
        Toast.error(
            'Campaign not found.'
        );

        return;
    }


    selectedUsers.clear();


    document.getElementById(
        'recipientCampaignId'
    ).value=
        id;


    document.getElementById(
        'recipientCampaignInfo'
    ).innerText=
        campaign.subject;


    el.audienceType.value=
        'all_active_members';


    el.memberSearch.value='';

    el.manualName.value='';
    el.manualEmail.value='';


    AdminUI.clearError(
        'recipientError'
    );


    updateAudienceSections();


    AdminUI.openModal(
        'recipientModal'
    );
};


/*
|--------------------------------------------------------------------------
| Close Recipient Modal
|--------------------------------------------------------------------------
*/

window.closeRecipientModal=
function(){
    AdminUI.closeModal(
        'recipientModal'
    );

    selectedUsers.clear();

    el.memberSearch.value='';
    el.manualName.value='';
    el.manualEmail.value='';
};


/*
|--------------------------------------------------------------------------
| Audience Sections
|--------------------------------------------------------------------------
*/

function updateAudienceSections(){
    const type=
        el.audienceType.value;


    document.getElementById(
        'selectedMembersSection'
    ).classList.toggle(
        'hidden',
        type!=='selected_members'
    );


    document.getElementById(
        'manualSection'
    ).classList.toggle(
        'hidden',
        type!=='manual'
    );


    AdminUI.clearError(
        'recipientError'
    );


    if(type==='selected_members'){
        loadMembers();
    }
}


/*
|--------------------------------------------------------------------------
| Load Members
|--------------------------------------------------------------------------
*/

async function loadMembers(search=''){
    el.memberResults.innerHTML=`
        <div class="p-5 text-center text-xs text-slate-400">
            Loading members...
        </div>
    `;


    try{
        const response=
            await api(
                `/api/mail-campaigns/recipients?${
                    AdminUI.query({
                        search
                    })
                }`
            );


        const users=
            response.data??[];


        if(!users.length){
            el.memberResults.innerHTML=`
                <div class="p-5 text-center text-xs text-slate-400">
                    No members found.
                </div>
            `;

            return;
        }


        el.memberResults.innerHTML=
            users.map(user=>`

                <label class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 transition hover:bg-slate-50">

                    <input
                        type="checkbox"
                        value="${user.id}"
                        ${selectedUsers.has(Number(user.id))?'checked':''}
                        onchange="toggleUser(${user.id},this.checked)"
                        class="h-4 w-4 shrink-0 rounded border-slate-300 text-indigo-600"
                    >


                    <div class="min-w-0 flex-1">

                        <p
                            class="truncate text-xs font-semibold text-slate-700"
                            title="${AdminUI.escapeHtml(user.name??'')}"
                        >
                            ${AdminUI.escapeHtml(
                                user.name??
                                'Member'
                            )}
                        </p>


                        <p
                            class="truncate text-[10px] text-slate-400"
                            title="${AdminUI.escapeHtml(user.email??'')}"
                        >
                            ${AdminUI.escapeHtml(
                                user.email??
                                ''
                            )}
                        </p>

                    </div>

                </label>

            `).join('');

    }catch(error){
        el.memberResults.innerHTML=`
            <div class="p-5 text-center text-xs text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| Toggle User
|--------------------------------------------------------------------------
*/

window.toggleUser=
function(id,checked){
    id=Number(id);

    if(checked){
        selectedUsers.add(id);
    }else{
        selectedUsers.delete(id);
    }
};


/*
|--------------------------------------------------------------------------
| Save Recipients
|--------------------------------------------------------------------------
*/

window.saveRecipients=
async function(){
    const id=
        document.getElementById(
            'recipientCampaignId'
        ).value;


    const type=
        el.audienceType.value;


    const data={
        audience_type:
            type
    };


    AdminUI.clearError(
        'recipientError'
    );


    if(type==='selected_members'){
        if(!selectedUsers.size){
            AdminUI.showError(
                'recipientError',
                'Select at least one member.'
            );

            return;
        }

        data.user_ids=[
            ...selectedUsers
        ];
    }


    if(type==='manual'){
        const email=
            el.manualEmail.value.trim();


        if(!email){
            AdminUI.showError(
                'recipientError',
                'Recipient email is required.'
            );

            return;
        }


        data.manual_emails=[{
            name:
                el.manualName.value.trim()
                ||null,

            email
        }];
    }


    const button=
        document.getElementById(
            'saveRecipientsButton'
        );


    AdminUI.setLoading(
        button,
        'Adding...'
    );


    try{
        await api(
            `/api/mail-campaigns/${id}/recipients`,
            {
                method:'POST',

                body:
                    JSON.stringify(
                        data
                    )
            }
        );


        closeRecipientModal();


        Toast.success(
            'Recipients added successfully.'
        );


        await loadCampaigns(
            currentPage
        );

    }catch(error){
        AdminUI.showError(
            'recipientError',
            AdminUI.extractError(
                error
            )
        );

    }finally{
        AdminUI.resetLoading(
            button
        );
    }
};


/*
|--------------------------------------------------------------------------
| View Campaign
|--------------------------------------------------------------------------
*/

window.viewCampaign=
async function(id){
    try{
        const response=
            await api(
                `/api/mail-campaigns/${id}`
            );


        const item=
            response.data??{};


        document.getElementById(
            'detailsTitle'
        ).innerText=
            item.subject??
            'Campaign';


        document.getElementById(
            'detailsSubtitle'
        ).innerText=
            `${item.recipients_count??0} recipients • ${formatStatus(item.status)}`;


        document.getElementById(
            'detailsContent'
        ).innerHTML=`

            <div class="grid grid-cols-3 gap-3">

                <div class="rounded-md bg-slate-50 p-3">

                    <p class="text-[10px] text-slate-400">
                        Recipients
                    </p>

                    <p class="mt-1 text-lg font-bold text-slate-800">
                        ${item.recipients_count??0}
                    </p>

                </div>


                <div class="rounded-md bg-emerald-50 p-3">

                    <p class="text-[10px] text-emerald-500">
                        Sent
                    </p>

                    <p class="mt-1 text-lg font-bold text-emerald-700">
                        ${item.sent_count??0}
                    </p>

                </div>


                <div class="rounded-md bg-red-50 p-3">

                    <p class="text-[10px] text-red-500">
                        Failed
                    </p>

                    <p class="mt-1 text-lg font-bold text-red-700">
                        ${item.failed_count??0}
                    </p>

                </div>

            </div>


            <div class="mt-4 rounded-md border border-slate-200 p-4">

                <div class="flex items-center justify-between gap-3">

                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        Email Content
                    </p>

                    ${AdminUI.statusBadge(item.status)}

                </div>


                <div class="mt-3 whitespace-pre-wrap break-words text-xs leading-6 text-slate-600">
                    ${AdminUI.escapeHtml(
                        item.body??
                        ''
                    )}
                </div>

            </div>


            <div class="mt-4">

                <div class="mb-2 flex items-center justify-between">

                    <p class="text-xs font-semibold text-slate-700">
                        Recipients
                    </p>

                    <span class="text-[10px] text-slate-400">
                        ${item.recipients?.length??0} shown
                    </span>

                </div>


                <div class="overflow-hidden rounded-md border border-slate-200">

                    ${
                        (item.recipients??[]).length
                            ?(item.recipients??[])
                                .map(recipient=>`

                                    <div class="flex min-w-0 items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 last:border-b-0">

                                        <div class="min-w-0 flex-1">

                                            <p
                                                class="truncate text-xs font-semibold text-slate-700"
                                                title="${AdminUI.escapeHtml(recipient.name??recipient.email??'')}"
                                            >
                                                ${AdminUI.escapeHtml(
                                                    recipient.name??
                                                    recipient.email??
                                                    'Recipient'
                                                )}
                                            </p>


                                            <p
                                                class="truncate text-[10px] text-slate-400"
                                                title="${AdminUI.escapeHtml(recipient.email??'')}"
                                            >
                                                ${AdminUI.escapeHtml(
                                                    recipient.email??
                                                    ''
                                                )}
                                            </p>

                                        </div>


                                        <div class="shrink-0">
                                            ${AdminUI.statusBadge(
                                                recipient.status
                                            )}
                                        </div>

                                    </div>

                                `).join('')
                            :`
                                <div class="p-6 text-center text-xs text-slate-400">
                                    No recipients added.
                                </div>
                            `
                    }

                </div>

            </div>

        `;


        AdminUI.openModal(
            'detailsModal'
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(
                error
            )
        );
    }
};


/*
|--------------------------------------------------------------------------
| Send Campaign
|--------------------------------------------------------------------------
*/

window.sendCampaign=
function(id){
    const item=
        campaigns.find(
            campaign=>
                Number(campaign.id)===
                Number(id)
        );


    if(!item){
        Toast.error(
            'Campaign not found.'
        );

        return;
    }


    if(
        Number(item.recipients_count??0)<=0
    ){
        Toast.error(
            'Add at least one recipient before sending.'
        );

        return;
    }


    AdminUI.request(
        `/api/mail-campaigns/${id}/send`,
        {
            method:'POST',

            confirmMessage:
                `Send "${item.subject}" to ${item.recipients_count} recipient(s)?`,

            successMessage:
                'Campaign sent successfully.',

            onSuccess:async()=>{
                await loadCampaigns(
                    currentPage
                );
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Delete Campaign
|--------------------------------------------------------------------------
*/

window.deleteCampaign=
function(id){
    const item=
        campaigns.find(
            campaign=>
                Number(campaign.id)===
                Number(id)
        );


    if(!item){
        Toast.error(
            'Campaign not found.'
        );

        return;
    }


    AdminUI.deleteRequest(
        `/api/mail-campaigns/${id}`,
        {
            message:
                `Delete "${item.subject}" permanently? This action cannot be undone.`,

            successMessage:
                'Campaign deleted successfully.',

            onSuccess:async()=>{

                if(
                    campaigns.length===1&&
                    currentPage>1
                ){
                    currentPage--;
                }


                await loadCampaigns(
                    currentPage
                );
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function formatStatus(value){
    return String(value??'')
        .replace(/_/g,' ')
        .replace(
            /\b\w/g,
            character=>
                character.toUpperCase()
        );
}


/*
|--------------------------------------------------------------------------
| Clear Filters
|--------------------------------------------------------------------------
*/

window.clearFilters=
function(){
    el.search.value='';
    el.statusFilter.value='';

    loadCampaigns(1);
};


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initMailingPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initMailingPage,
            50
        );

        return;
    }


    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadCampaigns(1)
        )
    );


    el.statusFilter.addEventListener(
        'change',
        ()=>loadCampaigns(1)
    );


    el.audienceType.addEventListener(
        'change',
        updateAudienceSections
    );


    el.memberSearch.addEventListener(
        'input',
        AdminUI.debounce(
            event=>
                loadMembers(
                    event.target.value.trim()
                )
        )
    );


    await loadCampaigns();
}


if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initMailingPage
    );
}else{
    initMailingPage();
}
</script>

@endpush