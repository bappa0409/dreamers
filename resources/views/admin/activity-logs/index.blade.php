@extends('layouts.admin')

@section('title','Audit Logs')
@section('page_title','Audit Logs')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div
        class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-clock-history text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">Audit Logs</h1>
                <p class="text-sm text-slate-500">A record of who did what, when — across the system.</p>
            </div>
        </div>

        <button type="button" onclick="refreshLogs()"
            class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise text-[12px]"></i>
            Refresh
        </button>
    </div>

    {{-- Filters --}}
<div class="rounded-md border border-slate-200 bg-white p-4">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

        {{-- Search --}}
        <div class="lg:col-span-3">
            <label
                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Search
            </label>

            <div class="relative">
                <i
                    class="bi bi-search pointer-events-none absolute left-3 top-1/2
                           -translate-y-1/2 text-xs text-slate-400">
                </i>

                <input
                    id="searchInput"
                    type="text"
                    placeholder="Description or user..."
                    class="h-9 w-full rounded-md border border-slate-300 bg-white
                           pl-9 pr-3 text-sm text-slate-700 outline-none
                           placeholder:text-slate-400 transition
                           focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
            </div>
        </div>


        {{-- User --}}
        <div class="lg:col-span-2">
            <label
                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                User
            </label>

            <select
                id="userFilter"
                class="h-9 w-full rounded-md border border-slate-300 bg-white
                       px-2.5 text-sm text-slate-700 outline-none transition
                       focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
            >
                <option value="">All users</option>
            </select>
        </div>


        {{-- Module --}}
        <div class="lg:col-span-2">
            <label
                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Module
            </label>

            <select
                id="moduleFilter"
                class="h-9 w-full rounded-md border border-slate-300 bg-white
                       px-2.5 text-sm text-slate-700 outline-none transition
                       focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
            >
                <option value="">All modules</option>
            </select>
        </div>


        {{-- Action --}}
        <div class="lg:col-span-2">
            <label
                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Action
            </label>

            <select
                id="actionFilter"
                class="h-9 w-full rounded-md border border-slate-300 bg-white
                       px-2.5 text-sm text-slate-700 outline-none transition
                       focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
            >
                <option value="">All actions</option>
            </select>
        </div>


        {{-- Date Range --}}
        <div class="lg:col-span-2">
            <label
                class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Date Range
            </label>

            <div class="relative">
                <i
                    class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2
                           z-10 -translate-y-1/2 text-xs text-slate-400">
                </i>

                <input
                    id="dateRangeFilter"
                    type="text"
                    class="js-date-range h-9 w-full rounded-md border border-slate-300
                           bg-white pl-9 pr-3 text-sm text-slate-700 outline-none
                           placeholder:text-slate-400 transition
                           focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    placeholder="Select date range"
                    autocomplete="off"
                >
            </div>
        </div>


        {{-- Clear --}}
        <div class="lg:col-span-1">
            <button
                type="button"
                onclick="clearFilters()"
                class="h-9 w-full rounded-md border border-indigo-200
                       bg-indigo-50 px-3 text-xs font-semibold text-indigo-600
                       transition hover:border-indigo-300 hover:bg-indigo-100
                       hover:text-indigo-700 focus:outline-none
                       focus:ring-2 focus:ring-indigo-100"
            >
                <i class="bi bi-x-circle me-1"></i>
                Clear
            </button>
        </div>

    </div>
</div>

    {{-- Table --}}
    <div class="rounded-md border border-slate-200 bg-white">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr
                        class="border-b border-slate-100 bg-slate-50/60 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-5 py-3">Date &amp; Time</th>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Action</th>
                        <th class="px-5 py-3">Module</th>
                        <th class="px-5 py-3">Description</th>
                        <th class="px-5 py-3">IP Address</th>
                        <th class="px-5 py-3 text-right">Details</th>
                    </tr>
                </thead>

                <tbody id="logsTableBody">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                            <i class="bi bi-arrow-repeat animate-spin"></i>
                            Loading audit logs...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div id="logsPagination"
            class="flex items-center justify-between border-t border-slate-100 px-5 py-3 text-xs text-slate-500"></div>

    </div>

</div>

{{-- Detail Modal --}}
<div id="logDetailModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-file-text"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Log Details</h2>
                    <p id="logDetailSubtitle" class="text-xs text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closeLogDetailModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-5 py-5 text-sm text-slate-600">

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-slate-400">User</p>
                    <p id="detailUser" class="mt-0.5 font-medium text-slate-700">—</p>
                </div>
                <div>
                    <p class="text-slate-400">Date &amp; Time</p>
                    <p id="detailDate" class="mt-0.5 font-medium text-slate-700">—</p>
                </div>
                <div>
                    <p class="text-slate-400">IP Address</p>
                    <p id="detailIp" class="mt-0.5 font-medium text-slate-700">—</p>
                </div>
                <div>
                    <p class="text-slate-400">Subject</p>
                    <p id="detailSubject" class="mt-0.5 font-medium text-slate-700">—</p>
                </div>
            </div>

            <div class="mt-3">
                <p class="text-xs text-slate-400">User Agent</p>
                <p id="detailUserAgent" class="mt-0.5 break-words text-xs text-slate-600">—</p>
            </div>

            <div id="detailChangesWrap" class="mt-5 hidden">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Changes</p>
                <div class="overflow-hidden rounded-md border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/60 font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">Field</th>
                                <th class="px-3 py-2">Before</th>
                                <th class="px-3 py-2">After</th>
                            </tr>
                        </thead>
                        <tbody id="detailChangesBody"></tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

    const tableBody  = document.getElementById('logsTableBody');
    const pagination = document.getElementById('logsPagination');

    const searchInput     = document.getElementById('searchInput');
    const userFilter      = document.getElementById('userFilter');
    const moduleFilter    = document.getElementById('moduleFilter');
    const actionFilter    = document.getElementById('actionFilter');
    const dateRangeFilter = document.getElementById('dateRangeFilter');

    let currentPage  = 1;
    let searchTimer  = null;
    let selectedFrom = '';
    let selectedTo   = '';

    /*
    |--------------------------------------------------------------------------
    | Date Range Picker
    |--------------------------------------------------------------------------
    */

    const dateRangePicker = flatpickr(dateRangeFilter, {
        mode: 'range',
        dateFormat: 'Y-m-d',
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                selectedFrom = instance.formatDate(selectedDates[0], 'Y-m-d');
                selectedTo   = instance.formatDate(selectedDates[1], 'Y-m-d');
                loadLogs(1);
            } else if (selectedDates.length === 0) {
                selectedFrom = '';
                selectedTo   = '';
                loadLogs(1);
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Load Filter Options
    |--------------------------------------------------------------------------
    */

    async function loadFilterOptions() {
        try {
            const response = await api('/api/activity-logs/filters');
            const data = response || {};

            (data.users || []).forEach(function (user) {
                const opt = document.createElement('option');
                opt.value = user.id;
                opt.textContent = user.name;
                userFilter.appendChild(opt);
            });

            (data.modules || []).forEach(function (module) {
                const opt = document.createElement('option');
                opt.value = module;
                opt.textContent = titleCase(module);
                moduleFilter.appendChild(opt);
            });

            (data.actions || []).forEach(function (action) {
                const opt = document.createElement('option');
                opt.value = action;
                opt.textContent = titleCase(action);
                actionFilter.appendChild(opt);
            });

        } catch (error) {
            // Filters are a nice-to-have — the table still works without them.
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Load Logs
    |--------------------------------------------------------------------------
    */

    async function loadLogs(page = 1) {
        currentPage = page;

        const params = new URLSearchParams();
        params.set('page', page);

        if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
        if (userFilter.value) params.set('user_id', userFilter.value);
        if (moduleFilter.value) params.set('module', moduleFilter.value);
        if (actionFilter.value) params.set('action', actionFilter.value);
        if (selectedFrom) params.set('from', selectedFrom);
        if (selectedTo) params.set('to', selectedTo);

        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                    <i class="bi bi-arrow-repeat animate-spin"></i>
                    Loading audit logs...
                </td>
            </tr>
        `;

        try {
            const response = await api(`/api/activity-logs?${params.toString()}`);
            const logs = response.data || [];

            renderTable(logs);
            renderPagination(response);

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

    function renderTable(logs) {
        if (!logs.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">
                        No activity found for the selected filters.
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = logs.map(function (log) {
            return `
                <tr class="border-b border-slate-50 last:border-0 hover:bg-slate-50/50">
                    <td class="px-5 py-3 text-slate-500">${formatDate(log.created_at)}</td>

                    <td class="px-5 py-3">
                        <span class="font-medium text-slate-700">${escapeHtml(log.user?.name || 'System')}</span>
                    </td>

                    <td class="px-5 py-3">${actionBadge(log.action)}</td>

                    <td class="px-5 py-3">
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-medium text-slate-600">
                            ${escapeHtml(titleCase(log.module || '—'))}
                        </span>
                    </td>

                    <td class="px-5 py-3 max-w-xs truncate text-slate-600" title="${escapeHtml(log.description || '')}">
                        ${escapeHtml(log.description || '—')}
                    </td>

                    <td class="px-5 py-3 text-slate-500">${escapeHtml(log.ip_address || '—')}</td>

                    <td class="px-5 py-3 text-right">
                        <button
                            type="button"
                            onclick="viewLogDetail(${log.id})"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-indigo-600"
                            title="View details"
                        >
                            <i class="bi bi-eye text-[13px]"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function actionBadge(action) {
        const map = {
            created:       'bg-emerald-50 text-emerald-600',
            updated:       'bg-amber-50 text-amber-600',
            deleted:       'bg-red-50 text-red-600',
            login:         'bg-sky-50 text-sky-600',
            logout:        'bg-slate-100 text-slate-600',
            login_failed:  'bg-red-50 text-red-600',
        };

        const icons = {
            created:       'bi-plus-circle',
            updated:       'bi-pencil',
            deleted:       'bi-trash3',
            login:         'bi-box-arrow-in-right',
            logout:        'bi-box-arrow-right',
            login_failed:  'bi-exclamation-triangle',
        };

        const cls  = map[action] || 'bg-slate-100 text-slate-600';
        const icon = icons[action] || 'bi-dot';

        return `
            <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-semibold ${cls}">
                <i class="bi ${icon} text-[11px]"></i>
                ${titleCase(action)}
            </span>
        `;
    }

    function renderPagination(rawMeta) {
        // Laravel's paginate() puts current_page/last_page/total at the
        // top level of the JSON response, alongside `data`. Fall back to
        // safe defaults instead of letting undefined leak into the UI.
        const meta = rawMeta || {};

        const currentPage = Number(meta.current_page) || 1;
        const lastPage     = Number(meta.last_page) || 1;
        const total        = Number.isFinite(Number(meta.total)) ? Number(meta.total) : 0;

        // Always show the summary line — even with 0 results / 1 page —
        // so the user gets a clear "0 total" instead of a blank footer.
        if (lastPage <= 1) {
            pagination.innerHTML = `
                <span>Page ${currentPage} of ${lastPage} — ${total} total</span>
            `;
            return;
        }

        pagination.innerHTML = `
            <span>Page ${currentPage} of ${lastPage} — ${total} total</span>

            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    ${currentPage <= 1 ? 'disabled' : ''}
                    onclick="window.__loadLogsPage(${currentPage - 1})"
                    class="rounded-md border border-slate-200 px-2.5 py-1 disabled:opacity-40"
                >Prev</button>

                <button
                    type="button"
                    ${currentPage >= lastPage ? 'disabled' : ''}
                    onclick="window.__loadLogsPage(${currentPage + 1})"
                    class="rounded-md border border-slate-200 px-2.5 py-1 disabled:opacity-40"
                >Next</button>
            </div>
        `;
    }

    window.__loadLogsPage = function (page) {
        loadLogs(page);
    };

    window.refreshLogs = function () {
        loadLogs(currentPage);
    };

    window.clearFilters = function () {
        searchInput.value = '';
        userFilter.value = '';
        moduleFilter.value = '';
        actionFilter.value = '';
        selectedFrom = '';
        selectedTo = '';
        dateRangePicker.clear();
        loadLogs(1);
    };

    /*
    |--------------------------------------------------------------------------
    | Detail Modal
    |--------------------------------------------------------------------------
    */

    const detailModal = document.getElementById('logDetailModal');

    window.viewLogDetail = async function (id) {
        detailModal.classList.remove('hidden');
        detailModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        document.getElementById('detailUser').textContent = 'Loading...';
        document.getElementById('detailChangesWrap').classList.add('hidden');

        try {
            const response = await api(`/api/activity-logs/${id}`);
            const log = response;

            document.getElementById('logDetailSubtitle').textContent =
                `${titleCase(log.action)} · ${titleCase(log.module || '')}`;

            document.getElementById('detailUser').textContent = log.user?.name || 'System';
            document.getElementById('detailDate').textContent = formatDate(log.created_at);
            document.getElementById('detailIp').textContent = log.ip_address || '—';
            document.getElementById('detailSubject').textContent =
                log.subject_type ? `${log.subject_type.split('\\').pop()} #${log.subject_id}` : '—';
            document.getElementById('detailUserAgent').textContent = log.user_agent || '—';

            renderChanges(log.old_values, log.new_values);

        } catch (error) {
            document.getElementById('detailUser').textContent = extractError(error);
        }
    };

    window.closeLogDetailModal = function () {
        detailModal.classList.add('hidden');
        detailModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    function renderChanges(oldValues, newValues) {
        const wrap = document.getElementById('detailChangesWrap');
        const body = document.getElementById('detailChangesBody');

        const keys = new Set([
            ...Object.keys(oldValues || {}),
            ...Object.keys(newValues || {}),
        ]);

        if (!keys.size) {
            wrap.classList.add('hidden');
            return;
        }

        body.innerHTML = Array.from(keys).map(function (key) {
            const before = oldValues ? oldValues[key] : undefined;
            const after  = newValues ? newValues[key] : undefined;

            return `
                <tr class="border-b border-slate-50 last:border-0">
                    <td class="px-3 py-2 font-medium text-slate-600">${escapeHtml(titleCase(key))}</td>
                    <td class="px-3 py-2 text-red-500">${formatValue(before)}</td>
                    <td class="px-3 py-2 text-emerald-600">${formatValue(after)}</td>
                </tr>
            `;
        }).join('');

        wrap.classList.remove('hidden');
    }

    function formatValue(value) {
        if (value === null || value === undefined) return '<span class="text-slate-300">—</span>';
        if (typeof value === 'boolean') return value ? 'Yes' : 'No';
        if (typeof value === 'object') return escapeHtml(JSON.stringify(value));
        return escapeHtml(String(value));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Filter Bindings
    |--------------------------------------------------------------------------
    */

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadLogs(1), 400);
    });

    [userFilter, moduleFilter, actionFilter].forEach(function (el) {
        el.addEventListener('change', () => loadLogs(1));
    });

    loadFilterOptions();
    loadLogs();
});
</script>
@endpush