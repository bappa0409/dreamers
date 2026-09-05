@extends('layouts.admin')

@section('title','Database Backups')
@section('page_title','Database Backups')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">

        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-hdd-network text-base"></i>
            </div>

            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">
                    Database Backups
                </h1>

                <p class="text-xs text-slate-500">
                    Create, download and manage database backup snapshots.
                </p>
            </div>
        </div>


        <div class="flex w-full flex-wrap items-center gap-2 md:w-fit">

            @if(auth()->user()->isSystemAnalyst() && setting('backup_import_enabled',false))
                @if(auth()->user()->hasPermission('Backup.import'))
                    <input
                        type="file"
                        id="importFileInput"
                        accept=".sql"
                        class="hidden"
                        onchange="handleImportFileSelected(event)"
                    >

                    <button
                        type="button"
                        onclick="document.getElementById('importFileInput').click()"
                        class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        <i class="bi bi-upload text-[12px]"></i>
                        Import Database
                    </button>
                @endif
            @endif


            @if(auth()->user()->hasPermission('Backup.create'))
                <button
                    type="button"
                    onclick="openConfirmBackupModal()"
                    class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                >
                    <i class="bi bi-play-circle text-[12px]"></i>
                    Run Backup Now
                </button>
            @endif

        </div>
    </div>


    {{-- =========================================================
    TABLE
    ========================================================== --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-left text-base">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[28%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Filename
                        </th>

                        <th class="w-[10%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Type
                        </th>

                        <th class="w-[13%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="w-[10%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Size
                        </th>

                        <th class="w-[14%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Created By
                        </th>

                        <th class="w-[17%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Created At
                        </th>

                        <th class="w-[8%] px-3 py-3 text-right text-xs font-semibold text-slate-600">
                            Actions
                        </th>
                    </tr>
                </thead>


                <tbody id="backupsTableBody">
                    <tr>
                        <td
                            colspan="7"
                            class="px-5 py-10 text-center text-base text-slate-400"
                        >
                            Loading backups...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>


        <div
            id="backupsPagination"
            class="border-t border-slate-200 px-4 py-3"
        ></div>

    </div>

</div>


{{-- =============================================================
CONFIRM BACKUP MODAL
============================================================= --}}
<div
    id="confirmBackupModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel w-full max-w-sm overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-hdd-network"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Run Database Backup?
                    </h2>

                    <p class="text-xs text-slate-500">
                        Create a new database snapshot.
                    </p>
                </div>

            </div>


            <button
                type="button"
                onclick="closeConfirmBackupModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div class="px-5 py-5 text-base leading-6 text-slate-600">
            This will create a full snapshot of the current database.
            Depending on the database size, this may take a few moments.
        </div>


        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">

            <button
                type="button"
                onclick="closeConfirmBackupModal()"
                class="cursor-pointer rounded-md border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                id="confirmBackupButton"
                type="button"
                onclick="confirmRunBackup()"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-play-circle text-[12px]"></i>
                Yes, Run Backup
            </button>

        </div>

    </div>
</div>


{{-- =============================================================
CONFIRM IMPORT MODAL
============================================================= --}}
<div
    id="confirmImportModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel w-full max-w-sm overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Import Database?
                    </h2>

                    <p class="text-xs text-slate-500">
                        This operation will replace current data.
                    </p>
                </div>

            </div>


            <button
                type="button"
                onclick="closeConfirmImportModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div class="px-5 py-5 text-base leading-6 text-slate-600">

            Importing

            <span
                id="importFileName"
                class="font-semibold text-slate-800"
            ></span>

            will overwrite all existing data in the current database.

            <div class="mt-3 rounded-md border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700">
                This action cannot be undone.
            </div>

        </div>


        <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-5 py-4">

            <button
                type="button"
                onclick="closeConfirmImportModal()"
                class="cursor-pointer rounded-md border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                id="confirmImportButton"
                type="button"
                onclick="confirmRunImport()"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-red-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
            >
                <i class="bi bi-upload text-[12px]"></i>
                Yes, Import Database
            </button>

        </div>

    </div>
</div>


{{-- =============================================================
PROGRESS MODAL
============================================================= --}}
<div
    id="operationProgressModal"
    class="app-modal-overlay fixed inset-0 z-[70] hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel w-full max-w-sm overflow-hidden rounded-md bg-white">

        <div class="flex flex-col items-center gap-4 px-6 py-8 text-center">

            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                <i class="bi bi-arrow-repeat animate-spin text-2xl"></i>
            </div>


            <div>
                <h3
                    id="progressModalTitle"
                    class="text-base font-bold text-slate-800"
                >
                    Processing...
                </h3>

                <p
                    id="progressModalMessage"
                    class="mt-1.5 text-base leading-5 text-slate-500"
                ></p>
            </div>


            <div class="flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700">
                <i class="bi bi-exclamation-triangle"></i>
                Please do not reload or close this page.
            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded',function(){

const canDelete=@json(
    auth()->user()->hasPermission('Backup.delete')
);

const canView=@json(
    auth()->user()->hasPermission('Backup.view')
);

const tableBody=
    document.getElementById(
        'backupsTableBody'
    );

const importFileInput=
    document.getElementById(
        'importFileInput'
    );

let currentPage=1;
let lastPage=1;
let total=0;
let pollTimer=null;
let selectedImportFile=null;
let operationInProgress=false;


/*
|--------------------------------------------------------------------------
| Load Backups
|--------------------------------------------------------------------------
*/

async function loadBackups(page=1){
    currentPage=page;

    tableBody.innerHTML=
        AdminUI.loadingState(
            'Loading backups...',
            7
        );


    try{
        const response=
            await api(
                `/api/backups?${AdminUI.query({page})}`
            );


        const paginator=
            response.data??{};

        const backups=
            paginator.data??[];


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


        renderTable(
            backups
        );


        AdminUI.renderPagination({
            container:'backupsPagination',
            currentPage,
            lastPage,
            total,
            onPageChange:loadBackups
        });


        /*
        |--------------------------------------------------------------------------
        | Poll only while backup is running
        |--------------------------------------------------------------------------
        */

        const hasInProgress=
            backups.some(
                backup=>
                    backup.status==='in_progress'
            );


        clearTimeout(
            pollTimer
        );


        if(hasInProgress){
            pollTimer=
                setTimeout(
                    ()=>{
                        loadBackups(
                            currentPage
                        );
                    },
                    4000
                );
        }

    }catch(error){
        tableBody.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                7
            );


        AdminUI.renderPagination({
            container:'backupsPagination',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadBackups
        });
    }
}


/*
|--------------------------------------------------------------------------
| Render Table
|--------------------------------------------------------------------------
*/

function renderTable(backups){
    if(!backups.length){
        tableBody.innerHTML=
            AdminUI.emptyState(
                'No backups found yet.',
                7
            );

        return;
    }


    tableBody.innerHTML=
        backups.map(backup=>`
            <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50/60">

                <td class="min-w-0 overflow-hidden px-3 py-3">

                    <div class="flex min-w-0 items-center gap-2">

                        <i class="bi bi-file-earmark-zip shrink-0 text-slate-400"></i>

                        <span
                            class="truncate text-xs font-semibold text-slate-700"
                            title="${AdminUI.escapeHtml(backup.filename??'')}"
                        >
                            ${AdminUI.escapeHtml(
                                backup.filename??
                                '—'
                            )}
                        </span>

                    </div>

                </td>


                <td class="overflow-hidden px-3 py-3">

                    <span
                        class="block truncate rounded-md bg-slate-100 px-1.5 py-1 text-center text-[10px] font-medium text-slate-600"
                    >
                        ${AdminUI.escapeHtml(
                            titleCase(
                                backup.type
                            )
                        )}
                    </span>

                </td>


                <td class="overflow-hidden px-3 py-3">
                    ${statusBadge(
                        backup.status
                    )}
                </td>


                <td class="overflow-hidden px-3 py-3">

                    <p
                        class="truncate text-xs text-slate-600"
                        title="${AdminUI.escapeHtml(AdminUI.formatBytes(backup.size))}"
                    >
                        ${AdminUI.formatBytes(
                            backup.size
                        )}
                    </p>

                </td>


                <td class="min-w-0 overflow-hidden px-3 py-3">

                    <p
                        class="truncate text-xs text-slate-600"
                        title="${AdminUI.escapeHtml(backup.creator?.name??'—')}"
                    >
                        ${AdminUI.escapeHtml(
                            backup.creator?.name??
                            '—'
                        )}
                    </p>

                </td>


                <td class="overflow-hidden px-3 py-3">

                    <p
                        class="truncate whitespace-nowrap text-[11px] text-slate-500"
                        title="${AdminUI.escapeHtml(AdminUI.formatDate(backup.created_at,true))}"
                    >
                        ${AdminUI.formatDate(
                            backup.created_at,
                            true
                        )}
                    </p>

                </td>


                <td class="px-3 py-3">

                    <div class="flex items-center justify-end gap-1">

                        ${
                            canView&&
                            backup.status==='completed'
                                ?`
                                    <a
                                        href="/api/backups/${backup.id}/download"
                                        title="Download Backup"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                    >
                                        <i class="bi bi-download text-sm"></i>
                                    </a>
                                `
                                :''
                        }


                        ${
                            canDelete
                                ?`
                                    <button
                                        type="button"
                                        onclick="deleteBackup(${backup.id})"
                                        title="Delete Backup"
                                        class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                    >
                                        <i class="bi bi-trash3 text-sm"></i>
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
| Status Badge
|--------------------------------------------------------------------------
*/

function statusBadge(status){
    const map={
        completed:{
            className:
                'bg-emerald-50 text-emerald-700',

            icon:
                'bi-check-circle'
        },

        in_progress:{
            className:
                'bg-amber-50 text-amber-700',

            icon:
                'bi-arrow-repeat animate-spin'
        },

        failed:{
            className:
                'bg-red-50 text-red-700',

            icon:
                'bi-x-circle'
        }
    };


    const config=
        map[status]??{
            className:
                'bg-slate-100 text-slate-600',

            icon:
                'bi-question-circle'
        };


    return `
        <span
            class="inline-flex max-w-full items-center gap-1 rounded-md px-1.5 py-1 text-[10px] font-semibold ${config.className}"
        >
            <i class="bi ${config.icon} shrink-0 text-[10px]"></i>

            <span class="truncate">
                ${AdminUI.escapeHtml(
                    titleCase(status)
                )}
            </span>
        </span>
    `;
}


/*
|--------------------------------------------------------------------------
| Backup Confirm Modal
|--------------------------------------------------------------------------
*/

window.openConfirmBackupModal=
function(){
    AdminUI.openModal(
        'confirmBackupModal'
    );
};


window.closeConfirmBackupModal=
function(){
    AdminUI.closeModal(
        'confirmBackupModal'
    );
};


window.confirmRunBackup=
function(){
    closeConfirmBackupModal();
    startBackup();
};


/*
|--------------------------------------------------------------------------
| Start Backup
|--------------------------------------------------------------------------
*/

async function startBackup(){
    openProgressModal(
        'Creating Backup',
        'Backing up your database. This can take a few moments depending on its size.'
    );


    try{
        await api(
            '/api/backups',
            {
                method:'POST',
                body:JSON.stringify({})
            }
        );


        closeProgressModal();


        Toast.success(
            'Backup created successfully.'
        );


        await loadBackups(1);

    }catch(error){
        closeProgressModal();


        Toast.error(
            AdminUI.extractError(error)
        );
    }
}


/*
|--------------------------------------------------------------------------
| Import File Selection
|--------------------------------------------------------------------------
*/

window.handleImportFileSelected=
function(event){
    const file=
        event.target.files?.[0];


    if(!file){
        return;
    }


    if(!/\.sql$/i.test(file.name)){
        Toast.error(
            'Only .sql files are allowed.'
        );


        if(importFileInput){
            importFileInput.value='';
        }


        return;
    }


    selectedImportFile=
        file;


    document.getElementById(
        'importFileName'
    ).textContent=
        file.name;


    AdminUI.openModal(
        'confirmImportModal'
    );
};


/*
|--------------------------------------------------------------------------
| Import Modal
|--------------------------------------------------------------------------
*/

window.closeConfirmImportModal=
function(){
    AdminUI.closeModal(
        'confirmImportModal'
    );


    selectedImportFile=null;


    if(importFileInput){
        importFileInput.value='';
    }
};


window.confirmRunImport=
function(){
    const file=
        selectedImportFile;


    if(!file){
        Toast.error(
            'No import file selected.'
        );

        return;
    }


    AdminUI.closeModal(
        'confirmImportModal'
    );


    startImport(
        file
    );
};


/*
|--------------------------------------------------------------------------
| Import Database
|--------------------------------------------------------------------------
*/

async function startImport(file){
    openProgressModal(
        'Importing Database',
        `Importing your database from "${file.name}". This can take a few moments.`
    );


    const formData=
        new FormData();


    formData.append(
        'file',
        file
    );


    try{
        await api(
            '/api/backups/import',
            {
                method:'POST',
                body:formData
            }
        );


        closeProgressModal();


        Toast.success(
            'Database imported successfully.'
        );


        await loadBackups(1);

    }catch(error){
        closeProgressModal();


        Toast.error(
            AdminUI.extractError(error)
        );

    }finally{
        selectedImportFile=null;


        if(importFileInput){
            importFileInput.value='';
        }
    }
}


/*
|--------------------------------------------------------------------------
| Progress Modal
|--------------------------------------------------------------------------
*/

function openProgressModal(
    title,
    message
){
    document.getElementById(
        'progressModalTitle'
    ).textContent=
        title;


    document.getElementById(
        'progressModalMessage'
    ).textContent=
        message;


    operationInProgress=true;


    AdminUI.openModal(
        'operationProgressModal'
    );
}


function closeProgressModal(){
    operationInProgress=false;


    AdminUI.closeModal(
        'operationProgressModal'
    );
}


/*
|--------------------------------------------------------------------------
| Before Unload Safety
|--------------------------------------------------------------------------
*/

window.addEventListener(
    'beforeunload',
    function(event){
        if(!operationInProgress){
            return;
        }


        event.preventDefault();

        event.returnValue='';
    }
);


/*
|--------------------------------------------------------------------------
| Delete Backup
|--------------------------------------------------------------------------
*/

window.deleteBackup=
function(id){
    AdminUI.deleteRequest(
        `/api/backups/${id}`,
        {
            message:
                'Delete this backup permanently?',

            successMessage:
                'Backup deleted successfully.',

            onSuccess:async()=>{
                await loadBackups(
                    currentPage
                );
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function titleCase(value){
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
| Initialize
|--------------------------------------------------------------------------
*/

async function initBackupPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initBackupPage,
            50
        );

        return;
    }


    await loadBackups();
}


initBackupPage();

});
</script>

@endpush