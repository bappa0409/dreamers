@extends('layouts.admin')

@section('title','System Settings')
@section('page_title','System Settings')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-gear"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">System Settings</h1>
                <p class="text-sm text-slate-500">Manage organization, system, membership and finance configuration.</p>
            </div>
        </div>

        <div id="loadingBadge" class="hidden items-center gap-2 text-xs font-semibold text-slate-400">
            <i class="bi bi-arrow-repeat animate-spin"></i>
            Loading settings...
        </div>
    </div>

    {{-- Settings --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        {{-- Tabs --}}
        <div id="settingsTabs" class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50/50 p-2"></div>

        {{-- Skeleton --}}
        <div id="settingsSkeleton" class="space-y-3 p-5">
            @for($i=0;$i<4;$i++)
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
document.addEventListener('DOMContentLoaded',function(){
    const canUpdate=@json(auth()->user()->hasPermission('Setting.update'));

    const tabsEl=document.getElementById('settingsTabs');
    const panelsEl=document.getElementById('settingsPanels');
    const skeletonEl=document.getElementById('settingsSkeleton');
    const loadingBadge=document.getElementById('loadingBadge');

    let settingsData={};
    let activeGroup=null;

    const groupIcons={
        general:'bi-sliders',
        system:'bi-hdd-stack',
        membership:'bi-people',
        finance:'bi-cash-coin',
        mail:'bi-envelope-at',
        security:'bi-shield-lock',
        maintenance:'bi-cone-striped'
    };

    const groupLabels={
        general:'General',
        system:'System',
        membership:'Membership',
        finance:'Finance',
        mail:'Mail / SMTP',
        security:'Security',
        maintenance:'Maintenance'
    };

    const groupOrder=[
        'general',
        'system',
        'membership',
        'finance',
        'mail',
        'security',
        'maintenance'
    ];

    const generalPrefixKeys=[
        'member_code_prefix',
        'investment_code_prefix',
        'land_code_prefix',
        'project_code_prefix'
    ];

    const generalImageKeys=[
        'logo',
        'site_logo',
        'favicon'
    ];

    async function loadSettings(){
        loadingBadge.classList.remove('hidden');
        loadingBadge.classList.add('flex');

        try{
            const response=await api('/api/settings');
            const settings=response.data??[];

            settingsData={};

            settings.forEach(setting=>{
                let group=setting.group??'general';

                if(group==='organization'||group==='branding'){
                    group='general';
                }

                if(generalPrefixKeys.includes(setting.key)){
                    group='general';
                }

                if(generalImageKeys.includes(setting.key)){
                    group='general';
                }

                setting.group=group;

                if(!settingsData[group]){
                    settingsData[group]=[];
                }

                settingsData[group].push(setting);
            });

            const groups=Object.keys(settingsData).sort((a,b)=>{
                const ai=groupOrder.indexOf(a);
                const bi=groupOrder.indexOf(b);

                return(ai===-1?999:ai)-(bi===-1?999:bi);
            });

            if(!groups.length){
                skeletonEl.innerHTML=`
                    <div class="p-10 text-center text-sm text-slate-400">
                        No settings found.
                    </div>
                `;
                return;
            }

            if(!activeGroup||!groups.includes(activeGroup)){
                activeGroup=groups.includes('general')
                    ?'general'
                    :groups[0];
            }

            renderTabs(groups);
            renderPanel();

            skeletonEl.classList.add('hidden');
            panelsEl.classList.remove('hidden');
        }catch(error){
            skeletonEl.innerHTML=`
                <div class="rounded-md border border-red-200 bg-red-50 p-5 text-sm text-red-600">
                    ${AdminUI.escapeHtml(AdminUI.extractError(error))}
                </div>
            `;
        }finally{
            loadingBadge.classList.add('hidden');
            loadingBadge.classList.remove('flex');
        }
    }

    function renderTabs(groups){
        tabsEl.innerHTML=groups.map(group=>{
            const active=group===activeGroup;

            return`
                <button
                    type="button"
                    data-group="${AdminUI.escapeHtml(group)}"
                    class="settings-tab-btn inline-flex cursor-pointer items-center gap-2 rounded-md px-3.5 py-2 text-xs font-semibold transition ${
                        active
                            ?'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200'
                            :'text-slate-500 hover:bg-white hover:text-slate-700'
                    }"
                >
                    <i class="bi ${groupIcons[group]??'bi-sliders'} text-[13px]"></i>
                    ${AdminUI.escapeHtml(groupLabels[group]??AdminUI.titleCase(group))}
                </button>
            `;
        }).join('');

        tabsEl.querySelectorAll('.settings-tab-btn').forEach(button=>{
            button.addEventListener('click',()=>{
                activeGroup=button.dataset.group;
                renderTabs(groups);
                renderPanel();
            });
        });
    }

    function renderPanel(){
        const fields=[...(settingsData[activeGroup]??[])];

        if(activeGroup==='general'){
            renderGeneralPanel(fields);
            return;
        }

        renderStandardPanel(fields);
    }

    function renderGeneralPanel(fields){
        const imageFields=fields.filter(setting=>setting.type==='image');

        const prefixFields=fields
            .filter(setting=>generalPrefixKeys.includes(setting.key))
            .sort(
                (a,b)=>
                    generalPrefixKeys.indexOf(a.key)-
                    generalPrefixKeys.indexOf(b.key)
            );

        const organizationFields=fields.filter(setting=>
            setting.type!=='image'&&
            !generalPrefixKeys.includes(setting.key)
        );

        panelsEl.innerHTML=`
            <form id="settingsForm" data-group="general">
                <div class="space-y-6 p-5">
                    <div id="settingsAlert" class="hidden rounded-md border px-4 py-3 text-sm"></div>

                    ${
                        organizationFields.length
                            ?sectionBlock({
                                icon:'bi-building',
                                title:'Organization',
                                subtitle:'Basic organization and application information.',
                                body:`
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        ${organizationFields.map(renderSettingField).join('')}
                                    </div>
                                `
                            })
                            :''
                    }

                    ${
                        imageFields.length
                            ?sectionBlock({
                                icon:'bi-image',
                                title:'Logo & Favicon',
                                subtitle:'Organization logo and browser favicon.',
                                body:`
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        ${imageFields.map(renderImageField).join('')}
                                    </div>
                                `
                            })
                            :''
                    }

                    ${
                        prefixFields.length
                            ?sectionBlock({
                                icon:'bi-upc-scan',
                                title:'Code Prefixes',
                                subtitle:'Configure automatically generated system codes.',
                                body:`
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                        ${prefixFields.map(renderPrefixField).join('')}
                                    </div>
                                `
                            })
                            :''
                    }

                    ${
                        canUpdate&&(organizationFields.length||prefixFields.length)
                            ?saveButton()
                            :''
                    }
                </div>
            </form>
        `;

        bindPanelEvents(imageFields);
    }

    function renderStandardPanel(fields){
        const imageFields=fields.filter(setting=>setting.type==='image');
        const otherFields=fields.filter(setting=>setting.type!=='image');

        panelsEl.innerHTML=`
            <form id="settingsForm" data-group="${AdminUI.escapeHtml(activeGroup)}">
                <div class="space-y-5 p-5">
                    <div id="settingsAlert" class="hidden rounded-md border px-4 py-3 text-sm"></div>

                    ${
                        imageFields.length
                            ?`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    ${imageFields.map(renderImageField).join('')}
                                </div>
                            `
                            :''
                    }

                    ${
                        otherFields.length
                            ?`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${otherFields.map(renderSettingField).join('')}
                                </div>
                            `
                            :(
                                imageFields.length
                                    ?''
                                    :AdminUI.emptyState('No settings in this group.')
                            )
                    }

                    ${
                        canUpdate&&otherFields.length
                            ?saveButton()
                            :''
                    }
                </div>
            </form>
        `;

        bindPanelEvents(imageFields);
    }

    function sectionBlock({icon,title,subtitle,body}){
        return`
            <section class="rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center gap-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi ${icon} text-sm"></i>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">
                            ${AdminUI.escapeHtml(title)}
                        </h3>

                        <p class="text-[11px] text-slate-400">
                            ${AdminUI.escapeHtml(subtitle)}
                        </p>
                    </div>
                </div>

                ${body}
            </section>
        `;
    }

    function saveButton(){
        return`
            <div class="flex justify-end border-t border-slate-100 pt-5">
                <button
                    type="submit"
                    id="settingsSaveBtn"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                >
                    <i class="bi bi-check2-circle"></i>
                    Save Changes
                </button>
            </div>
        `;
    }

    function renderSettingField(setting){
        if(setting.type==='boolean'){
            return renderToggleField(setting);
        }

        if(setting.type==='password'){
            return renderPasswordField(setting);
        }

        if(setting.type==='select'){
            return renderSelectField(setting);
        }

        return renderTextField(setting);
    }

    function renderLabel(setting){
        return AdminUI.escapeHtml(
            setting.description||
            AdminUI.titleCase(setting.key)
        );
    }

    function publicBadge(setting){
        if(!setting.is_public)return'';

        return`
            <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">
                Public
            </span>
        `;
    }

    function renderTextField(setting){
        return`
            <div class="min-w-0">
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <label class="truncate text-xs font-semibold text-slate-700">
                        ${renderLabel(setting)}
                    </label>

                    ${publicBadge(setting)}
                </div>

                <input
                    type="text"
                    name="${AdminUI.escapeHtml(setting.key)}"
                    value="${AdminUI.escapeHtml(setting.value??'')}"
                    ${canUpdate?'':'disabled'}
                    class="app-input"
                    placeholder="${AdminUI.escapeHtml(AdminUI.titleCase(setting.key))}"
                >

                <p class="mt-1 truncate font-mono text-[9px] text-slate-400">
                    ${AdminUI.escapeHtml(setting.key)}
                </p>
            </div>
        `;
    }

    function renderPrefixField(setting){
        const labels={
            member_code_prefix:'Member Prefix',
            investment_code_prefix:'Investment Prefix',
            land_code_prefix:'Land Prefix',
            project_code_prefix:'Project Prefix'
        };

        const fallbacks={
            member_code_prefix:'DA',
            investment_code_prefix:'INV',
            land_code_prefix:'LAND',
            project_code_prefix:'PROJ'
        };

        const fallback=fallbacks[setting.key]??'CODE';

        const value=String(
            setting.value||fallback
        ).toUpperCase();

        return`
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                    ${AdminUI.escapeHtml(
                        labels[setting.key]||
                        setting.description||
                        AdminUI.titleCase(setting.key)
                    )}
                </label>

                <input
                    type="text"
                    name="${AdminUI.escapeHtml(setting.key)}"
                    value="${AdminUI.escapeHtml(value)}"
                    maxlength="20"
                    ${canUpdate?'':'disabled'}
                    class="app-input uppercase"
                    placeholder="${AdminUI.escapeHtml(fallback)}"
                >

                <p class="mt-1 text-[10px] text-slate-400">
                    Example:
                    <span class="font-mono">
                        ${AdminUI.escapeHtml(value)}-000001
                    </span>
                </p>
            </div>
        `;
    }

    function renderSelectField(setting){
        const options=Array.isArray(setting.options)
            ?setting.options
            :[];

        const optionsHtml=options.map(option=>{
            const selected=
                String(setting.value)===String(option)
                    ?'selected'
                    :'';

            const label=
                setting.key==='date_format'
                    ?`${formatDatePreview(option)} (${option})`
                    :AdminUI.titleCase(option);

            return`
                <option
                    value="${AdminUI.escapeHtml(option)}"
                    ${selected}
                >
                    ${AdminUI.escapeHtml(label)}
                </option>
            `;
        }).join('');

        return`
            <div class="min-w-0">
                <div class="mb-1.5 flex items-center justify-between gap-2">
                    <label class="truncate text-xs font-semibold text-slate-700">
                        ${renderLabel(setting)}
                    </label>

                    ${publicBadge(setting)}
                </div>

                <select
                    name="${AdminUI.escapeHtml(setting.key)}"
                    ${canUpdate?'':'disabled'}
                    class="app-input cursor-pointer"
                >
                    ${optionsHtml}
                </select>

                <p class="mt-1 truncate font-mono text-[9px] text-slate-400">
                    ${AdminUI.escapeHtml(setting.key)}
                </p>
            </div>
        `;
    }

    function renderPasswordField(setting){
        return`
            <div>
                <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                    ${renderLabel(setting)}
                </label>

                <div class="relative">
                    <input
                        type="password"
                        name="${AdminUI.escapeHtml(setting.key)}"
                        value=""
                        autocomplete="new-password"
                        ${canUpdate?'':'disabled'}
                        class="app-input pr-9"
                        placeholder="Leave blank to keep current value"
                    >

                    <button
                        type="button"
                        class="settings-password-toggle absolute right-2.5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 transition hover:text-slate-600"
                    >
                        <i class="bi bi-eye text-xs"></i>
                    </button>
                </div>

                <p class="mt-1 truncate font-mono text-[9px] text-slate-400">
                    ${AdminUI.escapeHtml(setting.key)}
                </p>
            </div>
        `;
    }

    function renderToggleField(setting){
        const checked=
            setting.value==='1'||
            setting.value===1||
            setting.value===true;

        return`
            <div class="flex min-w-0 items-start justify-between gap-4 rounded-md border border-slate-200 p-3.5">
                <div class="min-w-0">
                    <p class="truncate text-xs font-semibold text-slate-700">
                        ${renderLabel(setting)}
                    </p>

                    <p class="mt-1 truncate font-mono text-[9px] text-slate-400">
                        ${AdminUI.escapeHtml(setting.key)}
                    </p>
                </div>

                <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                    <input
                        type="checkbox"
                        name="${AdminUI.escapeHtml(setting.key)}"
                        ${checked?'checked':''}
                        ${canUpdate?'':'disabled'}
                        class="peer sr-only"
                    >

                    <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-indigo-600 peer-disabled:opacity-50"></div>

                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>
                </label>
            </div>
        `;
    }

    function renderImageField(setting){
        const hasImage=Boolean(setting.value);

        const previewUrl=
            hasImage
                ?`/storage/${setting.value}`
                :null;

        const title=
            setting.key==='favicon'
                ?'Favicon'
                :(
                    setting.key==='site_logo'||
                    setting.key==='logo'
                        ?'Logo'
                        :(
                            setting.description||
                            AdminUI.titleCase(setting.key)
                        )
                );

        return`
            <div class="rounded-md border border-slate-200 p-3">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <label class="truncate text-xs font-semibold text-slate-700">
                        ${AdminUI.escapeHtml(title)}
                    </label>

                    ${publicBadge(setting)}
                </div>

                <div class="flex items-center gap-3">
                    <div
                        id="preview_${AdminUI.escapeHtml(setting.key)}"
                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md border border-dashed border-slate-300 bg-slate-50"
                    >
                        ${
                            previewUrl
                                ?`
                                    <img
                                        src="${AdminUI.escapeHtml(previewUrl)}"
                                        alt="${AdminUI.escapeHtml(setting.key)}"
                                        class="h-full w-full object-contain"
                                    >
                                `
                                :`
                                    <i class="bi bi-image text-lg text-slate-300"></i>
                                `
                        }
                    </div>

                    <div class="min-w-0">
                        ${
                            canUpdate
                                ?`
                                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                        <i class="bi bi-upload text-[11px]"></i>

                                        ${hasImage?'Replace':'Upload'}

                                        <input
                                            type="file"
                                            id="upload_${AdminUI.escapeHtml(setting.key)}"
                                            accept="image/*"
                                            class="hidden"
                                        >
                                    </label>
                                `
                                :''
                        }

                        <p
                            id="uploadStatus_${AdminUI.escapeHtml(setting.key)}"
                            class="mt-1.5 text-[10px] text-slate-400"
                        >
                            PNG, JPG or SVG. Max 2MB.
                        </p>
                    </div>
                </div>
            </div>
        `;
    }

    function bindPanelEvents(imageFields=[]){
        const form=document.getElementById('settingsForm');

        if(form){
            form.addEventListener('submit',handleSubmit);
        }

        imageFields.forEach(setting=>{
            const input=document.getElementById(
                'upload_'+setting.key
            );

            if(!input)return;

            input.addEventListener('change',event=>{
                handleImageUpload(
                    setting.key,
                    event.target.files?.[0]??null
                );
            });
        });

        document
            .querySelectorAll('.settings-password-toggle')
            .forEach(button=>{
                button.addEventListener('click',()=>{
                    const input=button.previousElementSibling;
                    const icon=button.querySelector('i');

                    if(input.type==='password'){
                        input.type='text';
                        icon.classList.replace(
                            'bi-eye',
                            'bi-eye-slash'
                        );
                    }else{
                        input.type='password';
                        icon.classList.replace(
                            'bi-eye-slash',
                            'bi-eye'
                        );
                    }
                });
            });
    }

    async function handleImageUpload(key,file){
        if(!file)return;

        const statusEl=document.getElementById(
            'uploadStatus_'+key
        );

        const previewEl=document.getElementById(
            'preview_'+key
        );

        if(file.size>2*1024*1024){
            statusEl.textContent='Image must be 2MB or smaller.';
            statusEl.className='mt-1.5 text-[10px] text-red-600';
            return;
        }

        statusEl.textContent='Uploading...';
        statusEl.className='mt-1.5 text-[10px] text-indigo-500';

        try{
            const formData=new FormData();

            formData.append('key',key);
            formData.append('file',file);

            const response=await api(
                '/api/settings/upload',
                {
                    method:'POST',
                    body:formData
                }
            );

            previewEl.innerHTML=`
                <img
                    src="${AdminUI.escapeHtml(response.data.url)}"
                    class="h-full w-full object-contain"
                    alt="${AdminUI.escapeHtml(key)}"
                >
            `;

            statusEl.textContent='Uploaded successfully.';
            statusEl.className='mt-1.5 text-[10px] text-emerald-600';

            const setting=
                (settingsData[activeGroup]??[])
                    .find(item=>item.key===key);

            if(setting){
                setting.value=response.data.value;
            }

            if(window.Toast){
                Toast.success('Image uploaded successfully.');
            }
        }catch(error){
            statusEl.textContent=AdminUI.extractError(error);
            statusEl.className='mt-1.5 text-[10px] text-red-600';
        }
    }

    async function handleSubmit(event){
        event.preventDefault();

        if(!canUpdate)return;

        const form=event.target;
        const group=form.dataset.group;

        const fields=(settingsData[group]??[])
            .filter(setting=>setting.type!=='image');

        const alertBox=document.getElementById('settingsAlert');
        const saveBtn=document.getElementById('settingsSaveBtn');

        if(!saveBtn)return;

        AdminUI.setLoading(saveBtn,'Saving...');

        try{
            for(const setting of fields){
                const input=form.querySelector(
                    `[name="${setting.key}"]`
                );

                if(!input)continue;

                let value;

                if(setting.type==='boolean'){
                    value=input.checked?'1':'0';
                }else if(setting.type==='password'){
                    if(input.value==='')continue;
                    value=input.value;
                }else{
                    value=input.value;

                    if(generalPrefixKeys.includes(setting.key)){
                        value=value
                            .trim()
                            .toUpperCase()
                            .replace(/\s+/g,'');

                        input.value=value;
                    }
                }

                await api('/api/settings',{
                    method:'POST',
                    body:JSON.stringify({
                        key:setting.key,
                        value,
                        type:setting.type,
                        group:setting.group,
                        description:setting.description,
                        is_public:setting.is_public,
                        options:setting.options??null
                    })
                });

                if(setting.type!=='password'){
                    setting.value=value;
                }
            }

            alertBox.className=
                'rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';

            alertBox.textContent='Settings updated successfully.';
            alertBox.classList.remove('hidden');

            if(window.Toast){
                Toast.success('Settings updated successfully.');
            }

            setTimeout(()=>{
                alertBox.classList.add('hidden');
            },3000);
        }catch(error){
            alertBox.className=
                'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600';

            alertBox.textContent=AdminUI.extractError(error);
            alertBox.classList.remove('hidden');
        }finally{
            AdminUI.resetLoading(saveBtn);
        }
    }

    function formatDatePreview(format){
        const now=new Date();

        const tokens={
            d:String(now.getDate()).padStart(2,'0'),
            m:String(now.getMonth()+1).padStart(2,'0'),
            Y:now.getFullYear(),
            M:[
                'Jan','Feb','Mar','Apr','May','Jun',
                'Jul','Aug','Sep','Oct','Nov','Dec'
            ][now.getMonth()],
            F:[
                'January','February','March','April','May','June',
                'July','August','September','October','November','December'
            ][now.getMonth()]
        };

        return String(format).replace(
            /d|m|Y|M|F/g,
            token=>tokens[token]??token
        );
    }

    async function initSettingsPage(){
        if(
            typeof window.AdminUI==='undefined'||
            typeof window.api==='undefined'
        ){
            setTimeout(initSettingsPage,50);
            return;
        }

        await loadSettings();
    }

    initSettingsPage();
});
</script>
@endpush