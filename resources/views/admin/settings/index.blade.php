@extends('layouts.admin')

@section('title','System Settings')
@section('page_title','System Settings')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-gear text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">System Settings</h1>
                <p class="mt-1 text-sm text-slate-500">Manage organization, system, membership and finance configuration.</p>
            </div>
        </div>

        <div id="loadingBadge" class="hidden items-center gap-2 text-xs font-semibold text-slate-400">
            <i class="bi bi-arrow-repeat animate-spin"></i>
            Loading settings...
        </div>
    </div>

    {{-- Tabs --}}
    <div class="rounded-md border border-slate-200 bg-white">

        <div id="settingsTabs" class="flex flex-wrap gap-1 border-b border-slate-200 p-2">
            {{-- JS renders tab buttons here --}}
        </div>

        {{-- Empty / Skeleton State --}}
        <div id="settingsSkeleton" class="space-y-4 p-5">
            @for($i = 0; $i < 4; $i++)
                <div class="h-16 animate-pulse rounded-md bg-slate-100"></div>
            @endfor
        </div>

        {{-- Panels --}}
        <div id="settingsPanels" class="hidden"></div>

    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const canUpdate = @json(auth()->user()->hasPermission('Setting.update'));

    const tabsEl      = document.getElementById('settingsTabs');
    const panelsEl     = document.getElementById('settingsPanels');
    const skeletonEl   = document.getElementById('settingsSkeleton');
    const loadingBadge = document.getElementById('loadingBadge');

    /*
    |--------------------------------------------------------------------------
    | Icons / Labels per group
    |--------------------------------------------------------------------------
    */

    const groupIcons = {
        organization : 'bi-building',
        system       : 'bi-hdd-stack',
        membership   : 'bi-people',
        finance      : 'bi-cash-coin',
        branding     : 'bi-palette',
        mail         : 'bi-envelope-at',
        security     : 'bi-shield-lock',
        maintenance  : 'bi-cone-striped',
    };

    const groupLabels = {
        organization : 'Organization',
        system       : 'System',
        membership   : 'Membership',
        finance      : 'Finance',
        branding     : 'Branding',
        mail         : 'Mail / SMTP',
        security     : 'Security',
        maintenance  : 'Maintenance',
    };

    let settingsData = {};   // { group: [ setting, ... ] }
    let activeGroup   = null;

    /*
    |--------------------------------------------------------------------------
    | Load Settings
    |--------------------------------------------------------------------------
    */

    async function loadSettings() {
        loadingBadge.classList.remove('hidden');
        loadingBadge.classList.add('flex');

        try {
            const response = await api('/api/settings');
            const settings = response.data || [];

            settingsData = {};

            settings.forEach(function (setting) {
                if (!settingsData[setting.group]) {
                    settingsData[setting.group] = [];
                }
                settingsData[setting.group].push(setting);
            });

            const groups = Object.keys(settingsData);

            if (!groups.length) {
                skeletonEl.innerHTML = '<p class="p-5 text-sm text-slate-400">No settings found.</p>';
                return;
            }

            activeGroup = groups[0];

            renderTabs(groups);
            renderPanels();

            skeletonEl.classList.add('hidden');
            panelsEl.classList.remove('hidden');

        } catch (error) {
            skeletonEl.innerHTML =
                '<p class="p-5 text-sm text-red-600">' + extractError(error) + '</p>';
        } finally {
            loadingBadge.classList.add('hidden');
            loadingBadge.classList.remove('flex');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Render Tabs
    |--------------------------------------------------------------------------
    */

    function renderTabs(groups) {
        tabsEl.innerHTML = groups.map(function (group) {
            const icon  = groupIcons[group] || 'bi-sliders';
            const label = groupLabels[group] || titleCase(group);

            const isActive = group === activeGroup;

            return `
                <button
                    type="button"
                    data-group="${group}"
                    onclick="window.__switchSettingsTab('${group}')"
                    class="settings-tab-btn inline-flex items-center gap-2 rounded-md px-3.5 py-2 text-xs font-semibold transition-colors ${
                        isActive
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                    }"
                >
                    <i class="bi ${icon} text-[13px]"></i>
                    ${label}
                </button>
            `;
        }).join('');
    }

    window.__switchSettingsTab = function (group) {
        activeGroup = group;
        renderTabs(Object.keys(settingsData));
        renderPanels();
    };

    /*
    |--------------------------------------------------------------------------
    | Render Active Panel
    |--------------------------------------------------------------------------
    */

    function renderPanels() {
        const fields = settingsData[activeGroup] || [];

        // Image fields upload independently — keep them out of the bulk save form.
        const imageFields = fields.filter(s => s.type === 'image');
        const otherFields  = fields.filter(s => s.type !== 'image');

        const imageFieldsHtml = imageFields.map(renderImageField).join('');

        const fieldsHtml = otherFields.map(function (setting) {
            if (setting.type === 'boolean') return renderToggleField(setting);
            if (setting.type === 'password') return renderPasswordField(setting);
            return renderTextField(setting);
        }).join('');

        panelsEl.innerHTML = `
            ${imageFields.length ? `
                <div class="grid grid-cols-1 gap-5 border-b border-slate-100 p-5 md:grid-cols-2">
                    ${imageFieldsHtml}
                </div>
            ` : ''}

            <form id="settingsForm" data-group="${activeGroup}" class="p-5">

                <div id="settingsAlert" class="mb-4 hidden rounded-md border px-4 py-3 text-sm"></div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    ${fieldsHtml || (imageFields.length ? '' : '<p class="text-sm text-slate-400">No settings in this group.</p>')}
                </div>

                ${canUpdate && otherFields.length ? `
                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-5">
                        <button
                            type="submit"
                            id="settingsSaveBtn"
                            class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                        >
                            <i class="bi bi-check2-circle text-[12px]"></i>
                            Save Changes
                        </button>
                    </div>
                ` : ''}
            </form>
        `;

        const form = document.getElementById('settingsForm');
        if (form) {
            form.addEventListener('submit', handleSubmit);
        }

        imageFields.forEach(function (setting) {
            const input = document.getElementById('upload_' + setting.key);
            if (input) {
                input.addEventListener('change', function (e) {
                    handleImageUpload(setting.key, e.target.files[0]);
                });
            }
        });
    }

    function renderTextField(setting) {
        return `
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="text-sm font-semibold text-slate-700">
                        ${setting.description || titleCase(setting.key)}
                    </label>

                    ${setting.is_public ? '<span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">Public</span>' : ''}
                </div>

                <input
                    type="text"
                    name="${setting.key}"
                    value="${escapeHtml(setting.value ?? '')}"
                    ${canUpdate ? '' : 'disabled'}
                    class="app-input"
                    placeholder="${titleCase(setting.key)}"
                >

                <p class="mt-1 text-[11px] text-slate-400">${setting.key}</p>
            </div>
        `;
    }

    function renderPasswordField(setting) {
        const hasValue = setting.value === '';           // '' = set but hidden, null = not set
        const placeholder = hasValue
            ? 'Leave blank to keep current password'
            : 'Not set';

        return `
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label class="text-sm font-semibold text-slate-700">
                        ${setting.description || titleCase(setting.key)}
                    </label>

                    ${hasValue ? '<span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500">Set</span>' : ''}
                </div>

                <div class="relative">
                    <input
                        type="password"
                        name="${setting.key}"
                        value=""
                        autocomplete="new-password"
                        ${canUpdate ? '' : 'disabled'}
                        class="app-input pr-9"
                        placeholder="${placeholder}"
                    >

                    <button
                        type="button"
                        onclick="window.__toggleSettingsPassword(this)"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                    >
                        <i class="bi bi-eye text-xs"></i>
                    </button>
                </div>

                <p class="mt-1 text-[11px] text-slate-400">${setting.key}</p>
            </div>
        `;
    }

    window.__toggleSettingsPassword = function (button) {
        const input = button.previousElementSibling;
        const icon  = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    };

    function renderToggleField(setting) {
        const checked = setting.value === '1' || setting.value === 1 || setting.value === true;

        return `
            <div class="flex items-start justify-between gap-3 rounded-md border border-slate-200 p-3.5">
                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        ${setting.description || titleCase(setting.key)}
                    </p>
                    <p class="mt-1 text-[11px] text-slate-400">${setting.key}</p>
                </div>

                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                    <input
                        type="checkbox"
                        name="${setting.key}"
                        ${checked ? 'checked' : ''}
                        ${canUpdate ? '' : 'disabled'}
                        class="peer sr-only"
                    >
                    <div class="h-6 w-11 rounded-full bg-slate-200 transition-colors peer-checked:bg-indigo-600 peer-disabled:opacity-50"></div>
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>
        `;
    }

    function renderImageField(setting) {
        const hasImage = !!setting.value;
        const previewUrl = hasImage ? `/storage/${setting.value}` : null;

        return `
            <div class="rounded-md border border-slate-200 p-4">
                <div class="mb-3 flex items-center justify-between">
                    <label class="text-sm font-semibold text-slate-700">
                        ${setting.description || titleCase(setting.key)}
                    </label>
                    <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">Public</span>
                </div>

                <div class="flex items-center gap-4">
                    <div id="preview_${setting.key}" class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md border border-dashed border-slate-300 bg-slate-50">
                        ${previewUrl
                            ? `<img src="${previewUrl}" class="h-full w-full object-contain" alt="${setting.key}">`
                            : '<i class="bi bi-image text-lg text-slate-300"></i>'
                        }
                    </div>

                    <div>
                        ${canUpdate ? `
                            <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <i class="bi bi-upload text-[11px]"></i>
                                ${hasImage ? 'Replace' : 'Upload'}
                                <input type="file" id="upload_${setting.key}" accept="image/*" class="hidden">
                            </label>
                        ` : ''}

                        <p id="uploadStatus_${setting.key}" class="mt-1.5 text-[11px] text-slate-400">
                            PNG, JPG or SVG. Max 2MB.
                        </p>
                    </div>
                </div>
            </div>
        `;
    }

    /*
    |--------------------------------------------------------------------------
    | Immediate Image Upload
    |--------------------------------------------------------------------------
    */

    async function handleImageUpload(key, file) {
        if (!file) return;

        const statusEl = document.getElementById('uploadStatus_' + key);
        const previewEl = document.getElementById('preview_' + key);

        statusEl.textContent = 'Uploading...';
        statusEl.className = 'mt-1.5 text-[11px] text-indigo-500';

        try {
            const formData = new FormData();
            formData.append('key', key);
            formData.append('file', file);

            const response = await api('/api/settings/upload', {
                method: 'POST',
                body: formData,
            });

            previewEl.innerHTML = `<img src="${response.data.url}" class="h-full w-full object-contain" alt="${key}">`;

            statusEl.textContent = 'Uploaded successfully.';
            statusEl.className = 'mt-1.5 text-[11px] text-emerald-600';

            // Keep local cache in sync
            const setting = (settingsData[activeGroup] || []).find(s => s.key === key);
            if (setting) setting.value = response.data.value;

        } catch (error) {
            statusEl.textContent = extractError(error);
            statusEl.className = 'mt-1.5 text-[11px] text-red-600';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Submit (save changed group — text/boolean/password fields)
    |--------------------------------------------------------------------------
    */

    async function handleSubmit(event) {
        event.preventDefault();

        if (!canUpdate) return;

        const form  = event.target;
        const group = form.dataset.group;
        const fields = (settingsData[group] || []).filter(s => s.type !== 'image');
        const alertBox = document.getElementById('settingsAlert');
        const saveBtn  = document.getElementById('settingsSaveBtn');

        const originalBtnHtml = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="bi bi-arrow-repeat animate-spin text-[12px]"></i> Saving...';

        try {
            for (const setting of fields) {
                const input = form.querySelector(`[name="${setting.key}"]`);
                let value;

                if (setting.type === 'boolean') {
                    value = input.checked ? '1' : '0';
                } else if (setting.type === 'password') {
                    // Empty = keep existing password, don't overwrite.
                    if (input.value === '') continue;
                    value = input.value;
                } else {
                    value = input.value;
                }

                await api('/api/settings', {
                    method: 'POST',
                    body: JSON.stringify({
                        key: setting.key,
                        value: value,
                        type: setting.type,
                        group: setting.group,
                        description: setting.description,
                        is_public: setting.is_public,
                    }),
                });

                if (setting.type !== 'password') {
                    setting.value = value;
                } else {
                    setting.value = ''; // mark as "set"
                }
            }

            alertBox.className = 'mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';
            alertBox.textContent = 'Settings updated successfully.';
            alertBox.classList.remove('hidden');

        } catch (error) {
            alertBox.className = 'mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600';
            alertBox.textContent = extractError(error);
            alertBox.classList.remove('hidden');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnHtml;
        }
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

    function extractError(error) {
        if (error.data?.errors) {
            const errors = Object.values(error.data.errors).flat();
            if (errors.length) return errors.join(' ');
        }
        return error.data?.message || error.message || 'Something went wrong.';
    }

    loadSettings();
});
</script>
@endpush