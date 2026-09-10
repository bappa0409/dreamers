@extends('layouts.admin')

@section('title','Website Page')
@section('page_title','Website Page')

@section('content')

<div class="space-y-3">

    {{-- Header --}}
    <div
        class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                <i class="bi bi-window-stack"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">
                    Website Page
                </h1>

                <p class=" text-xs 2xl:text-sm text-slate-500">
                    Show or hide sections on the public homepage. Changes apply instantly.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <i class="bi bi-box-arrow-up-right"></i>
                View Live Site
            </a>

            <div id="loadingBadge" class="hidden items-center gap-2 text-sm font-semibold text-slate-500">
                <i class="bi bi-arrow-repeat animate-spin"></i>
                Loading...
            </div>
        </div>
    </div>

    {{-- Loading Skeleton --}}
    <div id="sectionsSkeleton" class="space-y-3">
        @for($i=0;$i<4;$i++)
        <div class="h-24 animate-pulse rounded-md border border-slate-200 bg-white"></div>
        @endfor
    </div>

    {{-- Sections --}}
    <div id="sectionsList" class="hidden space-y-3"></div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof window.AdminUI === 'undefined' || typeof window.api === 'undefined') {
        return;
    }

    const canUpdate = @json(auth()->user()->hasPermission('Setting.update'));

    const skeletonEl = document.getElementById('sectionsSkeleton');
    const listEl = document.getElementById('sectionsList');
    const loadingBadge = document.getElementById('loadingBadge');

    const sectionIcons = {
        hero: 'bi-flag',
        stats: 'bi-bar-chart',
        about: 'bi-info-circle',
        activities: 'bi-briefcase',
        transparency: 'bi-shield-check',
        faq: 'bi-patch-question',
        cta: 'bi-megaphone',
        contact: 'bi-envelope',
    };

    let sectionsData = [];

    async function loadSections() {
        loadingBadge.classList.remove('hidden');
        loadingBadge.classList.add('flex');

        try {
            const response = await api('/api/website-page-sections');
            sectionsData = response.data;
            renderSections();

            skeletonEl.classList.add('hidden');
            listEl.classList.remove('hidden');
        } catch (error) {
            if (window.Toast) {
                Toast.error(AdminUI.extractError(error));
            }
        } finally {
            loadingBadge.classList.add('hidden');
            loadingBadge.classList.remove('flex');
        }
    }

    function statRow(item, index) {
        const value = AdminUI.escapeHtml(item?.value ?? '');
        const label = AdminUI.escapeHtml(item?.label ?? '');

        return `
            <div class="grid grid-cols-2 gap-2">
                <input type="text" data-stat-value="${index}" value="${value}"
                    placeholder="Value (e.g. 25)"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                <input type="text" data-stat-label="${index}" value="${label}"
                    placeholder="Label"
                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </div>
        `;
    }

    function sectionCard(section) {
        const icon = sectionIcons[section.section_key] || 'bi-square';
        const disabled = canUpdate ? '' : 'disabled';
        const isStats = section.section_key === 'stats';
        const isContact = section.section_key === 'contact';

        const titleHint = ['hero', 'about', 'transparency'].includes(section.section_key)
            ? `<p class="mt-1 text-[11px] text-slate-500">Use <code class="rounded bg-slate-100 px-1">|</code> to split the heading into two lines &mdash; the second line is highlighted in teal.</p>`
            : '';

        let bodyHtml = '';

        if (isStats) {
            const items = (section.settings && section.settings.items) || [];
            const rows = [0, 1, 2, 3].map(i => statRow(items[i], i)).join('');

            bodyHtml = `
                <div>
                    <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Stat Cards (4)</label>
                    <div class="space-y-2" data-stats-wrap>${rows}</div>
                </div>
            `;
        } else {
            bodyHtml = `
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Eyebrow / Badge Text</label>
                        <input type="text" data-field="subtitle" value="${AdminUI.escapeHtml(section.subtitle ?? '')}"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Heading</label>
                        <input type="text" data-field="title" value="${AdminUI.escapeHtml(section.title ?? '')}"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        ${titleHint}
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Description</label>
                    <textarea data-field="content" rows="3"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">${AdminUI.escapeHtml(section.content ?? '')}</textarea>
                </div>

                ${!isContact ? `
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Button Text</label>
                        <input type="text" data-field="button_text" value="${AdminUI.escapeHtml(section.button_text ?? '')}"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Button URL</label>
                        <input type="text" data-field="button_url" value="${AdminUI.escapeHtml(section.button_url ?? '')}"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs 2xl:text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                    </div>
                </div>
                ` : ''}
            `;
        }

        const imageHtml = section.section_key === 'hero' ? `
            <div>
                <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-600">Section Image</label>
                <div class="flex items-center gap-3">
                    <div id="preview_${section.section_key}"
                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-slate-50 text-slate-300">
                        ${section.image_url
                            ? `<img src="${AdminUI.escapeHtml(section.image_url)}" class="h-full w-full object-cover" alt="">`
                            : `<i class="bi bi-image text-lg"></i>`}
                    </div>
                    <div>
                        <input type="file" accept="image/png,image/jpeg,image/webp"
                            data-image-input="${section.section_key}" ${disabled}
                            class="block text-xs 2xl:text-sm text-slate-500 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file: text-xs 2xl:text-sm file:font-semibold file:text-slate-600 hover:file:bg-slate-200">
                        <p data-upload-status="${section.section_key}" class="mt-1 text-[10px] text-slate-500">PNG, JPG or WEBP, up to 4MB.</p>
                    </div>
                </div>
            </div>
        ` : '';

        return `
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white" data-section-card="${section.section_key}">
                <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i class="bi ${icon}"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">${AdminUI.escapeHtml(section.label)}</h2>
                            <p class="text-[11px] text-slate-500">Section key: ${section.section_key}</p>
                        </div>
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <span class="text-[11px] font-semibold text-slate-500">Visible</span>
                        <input type="checkbox" data-field="is_active" ${section.is_active ? 'checked' : ''} ${disabled}
                            class="h-4 w-8 cursor-pointer appearance-none rounded-full bg-slate-300 transition-colors checked:bg-teal-600 relative before:absolute before:left-0.5 before:top-0.5 before:h-3 before:w-3 before:rounded-full before:bg-white before:transition-transform checked:before:translate-x-4">
                    </label>
                </div>

                <div class="space-y-3 px-5 py-4">
                    ${bodyHtml}
                    ${imageHtml}
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-5 py-3">
                    <p data-save-status="${section.section_key}" class="text-[11px] text-slate-500"></p>
                    <button type="button" data-save-btn="${section.section_key}" ${disabled}
                        class="inline-flex items-center gap-2 rounded-md bg-teal-700 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="bi bi-check2"></i>
                        Save Changes
                    </button>
                </div>
            </div>
        `;
    }

    function renderSections() {
        listEl.innerHTML = sectionsData.map(sectionCard).join('');

        sectionsData.forEach(section => {
            const card = listEl.querySelector(`[data-section-card="${section.section_key}"]`);
            if (!card) return;

            const saveBtn = card.querySelector(`[data-save-btn="${section.section_key}"]`);
            if (saveBtn) {
                saveBtn.addEventListener('click', () => saveSection(section.section_key, card));
            }

            const imageInput = card.querySelector(`[data-image-input="${section.section_key}"]`);
            if (imageInput) {
                imageInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) uploadImage(section.section_key, file);
                });
            }
        });
    }

    async function saveSection(key, card) {
        const statusEl = card.querySelector(`[data-save-status="${key}"]`);
        const btn = card.querySelector(`[data-save-btn="${key}"]`);

        const payload = { is_active: card.querySelector('[data-field="is_active"]').checked };

        if (key === 'stats') {
            const items = [0, 1, 2, 3].map(i => ({
                value: card.querySelector(`[data-stat-value="${i}"]`)?.value ?? '',
                label: card.querySelector(`[data-stat-label="${i}"]`)?.value ?? '',
            }));
            payload.settings = { items };
        } else {
            ['subtitle', 'title', 'content', 'button_text', 'button_url'].forEach(field => {
                const input = card.querySelector(`[data-field="${field}"]`);
                if (input) payload[field] = input.value;
            });
        }

        btn.disabled = true;
        statusEl.textContent = 'Saving...';
        statusEl.className = 'text-[11px] text-indigo-500';

        try {
            const response = await api(`/api/website-page-sections/${key}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });

            const updated = sectionsData.find(s => s.section_key === key);
            if (updated) Object.assign(updated, response.data);

            statusEl.textContent = 'Saved just now.';
            statusEl.className = 'text-[11px] text-emerald-600';

            if (window.Toast) {
                Toast.success('Section updated successfully.');
            }
        } catch (error) {
            statusEl.textContent = AdminUI.extractError(error);
            statusEl.className = 'text-[11px] text-red-600';

            if (window.Toast) {
                Toast.error(AdminUI.extractError(error));
            }
        } finally {
            btn.disabled = !canUpdate;
        }
    }

    async function uploadImage(key, file) {
        const statusEl = document.querySelector(`[data-upload-status="${key}"]`);
        const previewEl = document.getElementById(`preview_${key}`);

        if (file.size > 4 * 1024 * 1024) {
            statusEl.textContent = 'Image must be 4MB or smaller.';
            statusEl.className = 'mt-1 text-[10px] text-red-600';
            return;
        }

        statusEl.textContent = 'Uploading...';
        statusEl.className = 'mt-1 text-[10px] text-indigo-500';

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await api(`/api/website-page-sections/${key}/upload`, {
                method: 'POST',
                body: formData,
            });

            previewEl.innerHTML = `<img src="${AdminUI.escapeHtml(response.data.image_url)}" class="h-full w-full object-cover" alt="">`;

            statusEl.textContent = 'Uploaded successfully.';
            statusEl.className = 'mt-1 text-[10px] text-emerald-600';

            const updated = sectionsData.find(s => s.section_key === key);
            if (updated) {
                updated.image = response.data.image;
                updated.image_url = response.data.image_url;
            }

            if (window.Toast) {
                Toast.success('Image uploaded successfully.');
            }
        } catch (error) {
            statusEl.textContent = AdminUI.extractError(error);
            statusEl.className = 'mt-1 text-[10px] text-red-600';
        }
    }

    loadSections();
});
</script>
@endpush
