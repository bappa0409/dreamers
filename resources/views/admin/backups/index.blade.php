@extends('layouts.admin')

@section('title','Database Backups')
@section('page_title','Database Backups')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-hdd-network text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">Database Backups</h1>
                <p class="text-sm text-slate-500">Create, download and manage database backup snapshots.</p>
            </div>
        </div>

        <div class="flex w-full flex-wrap items-center gap-2 md:w-fit">

            @if(auth()->user()->isSystemAnalyst() && setting('backup_import_enabled',false))
            @if(auth()->user()->hasPermission('Backup.import'))
                <input type="file" id="importFileInput" accept=".sql" class="hidden" onchange="handleImportFileSelected(event)">

                <button type="button" id="importDbBtn" onclick="document.getElementById('importFileInput').click()" class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-upload text-[12px]"></i>
                    Import Database
                </button>
            @endif
            @endif

            @if(auth()->user()->hasPermission('Backup.create'))
                <button type="button" id="runBackupBtn" onclick="openConfirmBackupModal()" class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    <i class="bi bi-play-circle text-[12px]"></i>
                    Run Backup Now
                </button>
            @endif

        </div>
    </div>

    {{-- Alert --}}
    <div id="backupsAlert" class="hidden rounded-md border px-4 py-3 text-sm"></div>

    {{-- Table --}}
    <div class="rounded-md border border-slate-200 bg-white">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/60 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3">Filename</th>
                        <th class="px-5 py-3">Type</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Size</th>
                        <th class="px-5 py-3">Created By</th>
                        <th class="px-5 py-3">Created At</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody id="backupsTableBody">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                            <i class="bi bi-arrow-repeat animate-spin"></i>
                            Loading backups...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="backupsPagination" class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-xs text-slate-500"></div>

    </div>

</div>

{{-- Confirm Run Backup Modal --}}
<div id="confirmBackupModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-sm overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-hdd-network"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Run Database Backup?</h2>
                </div>
            </div>

            <button type="button" onclick="closeConfirmBackupModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="px-5 py-5 text-sm text-slate-600">
            This will create a full snapshot of the current database. Depending on the database size, this may take a few moments to complete.
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <button
                type="button"
                onclick="closeConfirmBackupModal()"
                class="rounded-md border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                type="button"
                onclick="confirmRunBackup()"
                class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
            >
                <i class="bi bi-play-circle text-[12px]"></i>
                Yes, Run Backup
            </button>
        </div>

    </div>
</div>

{{-- Confirm Import Modal --}}
<div id="confirmImportModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-sm overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-600 text-white">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Import Database?</h2>
                </div>
            </div>

            <button type="button" onclick="closeConfirmImportModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="px-5 py-5 text-sm text-slate-600">
            Importing
            <span id="importFileName" class="font-semibold text-slate-800"></span>
            will overwrite all existing data in the current database. This action cannot be undone. Are you sure you want to continue?
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <button
                type="button"
                onclick="closeConfirmImportModal()"
                class="rounded-md border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                type="button"
                onclick="confirmRunImport()"
                class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-red-700"
            >
                <i class="bi bi-upload text-[12px]"></i>
                Yes, Import Database
            </button>
        </div>

    </div>
</div>

{{-- Processing / Progress Modal (import & backup share this) --}}
<div id="operationProgressModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-sm overflow-hidden rounded-md bg-white">

        <div class="flex flex-col items-center gap-4 px-6 py-8 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                <i class="bi bi-arrow-repeat animate-spin text-2xl"></i>
            </div>

            <div>
                <h3 id="progressModalTitle" class="text-base font-bold text-slate-800">Processing...</h3>
                <p id="progressModalMessage" class="mt-1.5 text-sm text-slate-500"></p>
            </div>

            <div class="flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700">
                <i class="bi bi-exclamation-triangle"></i>
                Please do not reload or close this page.
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const canDelete = @json(auth()->user()->hasPermission('Backup.delete'));
    const canView   = @json(auth()->user()->hasPermission('Backup.view'));

    const tableBody   = document.getElementById('backupsTableBody');
    const pagination  = document.getElementById('backupsPagination');
    const alertBox    = document.getElementById('backupsAlert');
    const runBtn      = document.getElementById('runBackupBtn');

    let currentPage = 1;
    let pollTimer   = null;

    async function loadBackups(page = 1) {
        currentPage = page;

        try {
            const response = await api(`/api/backups?page=${page}`);
            const backups  = response.data?.data || [];

            renderTable(backups);
            renderPagination(response.data);

            // Keep polling while any backup is still in progress.
            const hasInProgress = backups.some(b => b.status === 'in_progress');
            clearTimeout(pollTimer);
            if (hasInProgress) {
                pollTimer = setTimeout(() => loadBackups(currentPage), 4000);
            }

        } catch (error) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-red-500">
                        ${extractError(error)}
                    </td>
                </tr>
            `;
        }
    }

    function renderTable(backups) {
        if (!backups.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                        No backups found yet.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = backups.map(function (backup) {
            return `
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-file-earmark-zip text-slate-400"></i>
                            <span class="font-medium text-slate-700">${escapeHtml(backup.filename)}</span>
                        </div>
                    </td>

                    <td class="px-5 py-3">
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-600">
                            ${titleCase(backup.type)}
                        </span>
                    </td>

                    <td class="px-5 py-3">${statusBadge(backup.status)}</td>

                    <td class="px-5 py-3 text-slate-600">${formatBytes(backup.size)}</td>

                    <td class="px-5 py-3 text-slate-600">${escapeHtml(backup.creator?.name || '—')}</td>

                    <td class="px-5 py-3 text-slate-500">${formatDate(backup.created_at)}</td>

                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1.5">
                            ${canView && backup.status === 'completed' ? `
                                
                                    href="/api/backups/${backup.id}/download"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-indigo-600"
                                    title="Download"
                                >
                                    <i class="bi bi-download text-[13px]"></i>
                                </a>
                            ` : ''}

                            ${canDelete ? `
                                <button
                                    type="button"
                                    onclick="deleteBackup(${backup.id}, '${escapeHtml(backup.filename)}')"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-red-50 hover:text-red-600"
                                    title="Delete"
                                >
                                    <i class="bi bi-trash3 text-[13px]"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function statusBadge(status) {
        const map = {
            completed:   'bg-emerald-50 text-emerald-600',
            in_progress: 'bg-amber-50 text-amber-600',
            failed:      'bg-red-50 text-red-600',
        };

        const icons = {
            completed:   'bi-check-circle',
            in_progress: 'bi-arrow-repeat animate-spin',
            failed:      'bi-x-circle',
        };

        const cls  = map[status] || 'bg-slate-100 text-slate-600';
        const icon = icons[status] || 'bi-question-circle';

        return `
            <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-semibold ${cls}">
                <i class="bi ${icon} text-[11px]"></i>
                ${titleCase(status)}
            </span>
        `;
    }

    function renderPagination(meta) {
        if (!meta || meta.last_page <= 1) {
            pagination.innerHTML = '';
            return;
        }

        pagination.innerHTML = `
            <span>Page ${meta.current_page} of ${meta.last_page} — ${meta.total} total</span>

            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    ${meta.current_page <= 1 ? 'disabled' : ''}
                    onclick="window.__loadBackupsPage(${meta.current_page - 1})"
                    class="rounded-md border border-slate-200 px-2.5 py-1 disabled:opacity-40"
                >Prev</button>

                <button
                    type="button"
                    ${meta.current_page >= meta.last_page ? 'disabled' : ''}
                    onclick="window.__loadBackupsPage(${meta.current_page + 1})"
                    class="rounded-md border border-slate-200 px-2.5 py-1 disabled:opacity-40"
                >Next</button>
            </div>
        `;
    }

    window.__loadBackupsPage = function (page) {
        loadBackups(page);
    };

    /*
    |--------------------------------------------------------------------------
    | Progress Modal (shared between backup + import)
    |--------------------------------------------------------------------------
    */

    const progressModal   = document.getElementById('operationProgressModal');
    const progressTitle   = document.getElementById('progressModalTitle');
    const progressMessage = document.getElementById('progressModalMessage');

    let operationInProgress = false;

    function openProgressModal(title, message) {
        progressTitle.textContent = title;
        progressMessage.textContent = message;

        progressModal.classList.remove('hidden');
        progressModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        operationInProgress = true;
    }

    function closeProgressModal() {
        progressModal.classList.add('hidden');
        progressModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        operationInProgress = false;
    }

    // Warn the user (native browser prompt) if they try to reload/close
    // the tab while a backup or import is actively running.
    window.addEventListener('beforeunload', function (e) {
        if (operationInProgress) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Run Backup
    |--------------------------------------------------------------------------
    */

    window.openConfirmBackupModal = function () {
        const modal = document.getElementById('confirmBackupModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.closeConfirmBackupModal = function () {
        const modal = document.getElementById('confirmBackupModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    window.confirmRunBackup = function () {
        closeConfirmBackupModal();
        startBackup();
    };

    async function startBackup() {
        hideAlert();

        openProgressModal(
            'Creating Backup',
            'Backing up your database. This can take a few moments depending on its size.'
        );

        try {
            await api('/api/backups', { method: 'POST' });

            closeProgressModal();
            showAlert('success', 'Backup created successfully.');
            loadBackups(1);

        } catch (error) {
            closeProgressModal();
            showAlert('error', extractError(error));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Import Database
    |--------------------------------------------------------------------------
    */

    const importFileInput = document.getElementById('importFileInput');
    let selectedImportFile = null;

    window.handleImportFileSelected = function (event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;

        const validExt = /\.sql$/i.test(file.name);
        if (!validExt) {
            showAlert('error', 'Only .sql files are allowed.');
            importFileInput.value = '';
            return;
        }

        selectedImportFile = file;
        document.getElementById('importFileName').textContent = file.name;
        openConfirmImportModal();
    };

    window.openConfirmImportModal = function () {
        const modal = document.getElementById('confirmImportModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.closeConfirmImportModal = function () {
        const modal = document.getElementById('confirmImportModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        selectedImportFile = null;
        importFileInput.value = '';
    };

    window.confirmRunImport = function () {
        const file = selectedImportFile;

        // Close the confirm modal without resetting the input/selection.
        const modal = document.getElementById('confirmImportModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        startImport(file);
    };

    async function startImport(file) {
        if (!file) return;

        hideAlert();

        openProgressModal(
            'Importing Database',
            'Importing your database from "' + file.name + '". This can take a few moments.'
        );

        const formData = new FormData();
        formData.append('file', file);

        try {
            await api('/api/backups/import', { method: 'POST', body: formData });

            closeProgressModal();
            showAlert('success', 'Database imported successfully.');
            loadBackups(1);

        } catch (error) {
            closeProgressModal();
            showAlert('error', extractError(error));

        } finally {
            selectedImportFile = null;
            importFileInput.value = '';
        }
    }

    window.deleteBackup = async function (id, filename) {
        if (!confirm(`Delete backup "${filename}"? This cannot be undone.`)) return;

        try {
            await api(`/api/backups/${id}`, { method: 'DELETE' });
            showAlert('success', 'Backup deleted successfully.');
            loadBackups(currentPage);
        } catch (error) {
            showAlert('error', extractError(error));
        }
    };

    function showAlert(type, message) {
        alertBox.className = type === 'success'
            ? 'rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700'
            : 'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600';
        alertBox.textContent = message;
        alertBox.classList.remove('hidden');
    }

    function hideAlert() {
        alertBox.classList.add('hidden');
    }

    function titleCase(str) {
        return (str || '')
            .toString()
            .replace(/_/g, ' ')
            .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function formatBytes(bytes) {
        if (!bytes && bytes !== 0) return '—';
        if (bytes === 0) return '0 B';

        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));

        return `${(bytes / Math.pow(1024, i)).toFixed(1)} ${units[i]}`;
    }

    function formatDate(value) {
        if (!value) return '—';

        const date = new Date(value);
        return date.toLocaleString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit',
        });
    }

    function extractError(error) {
        if (error.data?.errors) {
            const errors = Object.values(error.data.errors).flat();
            if (errors.length) return errors.join(' ');
        }
        return error.data?.message || error.message || 'Something went wrong.';
    }

    loadBackups();
});
</script>
@endpush