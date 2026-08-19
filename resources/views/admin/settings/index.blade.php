@extends('layouts.admin')

@section('title','System Settings')
@section('page_title','System Settings')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-gear"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">
                    System Settings
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage organization, system, membership and finance configuration.
                </p>
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
        <div id="settingsTabs" class="flex flex-wrap gap-1 border-b border-slate-200 p-2"></div>

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

const canUpdate=@json(
    auth()->user()->hasPermission('Setting.update')
);

const tabsEl=document.getElementById('settingsTabs');
const panelsEl=document.getElementById('settingsPanels');
const skeletonEl=document.getElementById('settingsSkeleton');
const loadingBadge=document.getElementById('loadingBadge');

let settingsData={};
let activeGroup=null;


/*
|--------------------------------------------------------------------------
| Group Configuration
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Load Settings
|--------------------------------------------------------------------------
*/

async function loadSettings(){
    loadingBadge.classList.remove('hidden');
    loadingBadge.classList.add('flex');

    try{
        const response=await api('/api/settings');
        const settings=response.data??[];

        settingsData={};

        settings.forEach(setting=>{
            let group=setting.group??'general';

            /*
            |--------------------------------------------------------------------------
            | Merge Organization + Branding into General
            |--------------------------------------------------------------------------
            */

            if(
                group==='organization'||
                group==='branding'
            ){
                group='general';
            }

            /*
            |--------------------------------------------------------------------------
            | Prefixes always belong to General
            |--------------------------------------------------------------------------
            */

            if(generalPrefixKeys.includes(setting.key)){
                group='general';
            }

            /*
            |--------------------------------------------------------------------------
            | Logo / Favicon always belong to General
            |--------------------------------------------------------------------------
            */

            if(generalImageKeys.includes(setting.key)){
                group='general';
            }

            setting.group=group;

            if(!settingsData[group]){
                settingsData[group]=[];
            }

            settingsData[group].push(setting);
        });

        let groups=Object.keys(settingsData);

        groups.sort((a,b)=>{
            const ai=groupOrder.indexOf(a);
            const bi=groupOrder.indexOf(b);

            return (
                (ai===-1?999:ai)-
                (bi===-1?999:bi)
            );
        });

        if(!groups.length){
            skeletonEl.innerHTML=`
                <div class="p-8 text-center text-sm text-slate-400">
                    No settings found.
                </div>
            `;

            return;
        }

        if(
            !activeGroup||
            !groups.includes(activeGroup)
        ){
            activeGroup=
                groups.includes('general')
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
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }finally{
        loadingBadge.classList.add('hidden');
        loadingBadge.classList.remove('flex');
    }
}


/*
|--------------------------------------------------------------------------
| Render Tabs
|--------------------------------------------------------------------------
*/

function renderTabs(groups){
    tabsEl.innerHTML=groups.map(group=>{
        const icon=
            groupIcons[group]??'bi-sliders';

        const label=
            groupLabels[group]??titleCase(group);

        const active=
            group===activeGroup;

        return `
            <button
                type="button"
                data-group="${AdminUI.escapeHtml(group)}"
                class="settings-tab-btn inline-flex cursor-pointer items-center gap-2 rounded-md px-3.5 py-2 text-xs font-semibold transition ${
                    active
                        ?'bg-indigo-50 text-indigo-700'
                        :'text-slate-500 hover:bg-slate-50 hover:text-slate-700'
                }"
            >
                <i class="bi ${icon} text-[13px]"></i>
                ${AdminUI.escapeHtml(label)}
            </button>
        `;
    }).join('');

    tabsEl
        .querySelectorAll('.settings-tab-btn')
        .forEach(button=>{
            button.addEventListener(
                'click',
                ()=>{
                    activeGroup=
                        button.dataset.group;

                    renderTabs(groups);
                    renderPanel();
                }
            );
        });
}


/*
|--------------------------------------------------------------------------
| Render Panel
|--------------------------------------------------------------------------
*/

function renderPanel(){
    const fields=[
        ...(settingsData[activeGroup]??[])
    ];

    if(activeGroup==='general'){
        renderGeneralPanel(fields);
        return;
    }

    renderStandardPanel(fields);
}


/*
|--------------------------------------------------------------------------
| General Panel
|--------------------------------------------------------------------------
*/

function renderGeneralPanel(fields){
    const imageFields=
        fields.filter(
            setting=>
                setting.type==='image'
        );

    const prefixFields=
        fields.filter(
            setting=>
                generalPrefixKeys.includes(
                    setting.key
                )
        );

    const organizationFields=
        fields.filter(setting=>{
            return (
                setting.type!=='image'&&
                !generalPrefixKeys.includes(
                    setting.key
                )
            );
        });

    panelsEl.innerHTML=`
        <form
            id="settingsForm"
            data-group="general"
        >
            <div class="p-5">

                <div
                    id="settingsAlert"
                    class="mb-4 hidden rounded-md border px-4 py-3 text-sm"
                ></div>


                {{-- Organization --}}
                ${
                    organizationFields.length
                        ?`
                            <section>
                                <div class="mb-4 flex items-center gap-2">

                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                        <i class="bi bi-building"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-800">
                                            Organization
                                        </h3>

                                        <p class="text-[11px] text-slate-400">
                                            Basic organization information.
                                        </p>
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    ${
                                        organizationFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            </section>
                        `
                        :''
                }


                {{-- Logo + Favicon --}}
                ${
                    imageFields.length
                        ?`
                            <section class="mt-6 border-t border-slate-100 pt-6">

                                <div class="mb-4 flex items-center gap-2">

                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                                        <i class="bi bi-image"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-800">
                                            Logo & Favicon
                                        </h3>

                                        <p class="text-[11px] text-slate-400">
                                            Organization logo and browser favicon.
                                        </p>
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        imageFields
                                            .map(renderImageField)
                                            .join('')
                                    }
                                </div>

                            </section>
                        `
                        :''
                }


                {{-- Prefixes --}}
                ${
                    prefixFields.length
                        ?`
                            <section class="mt-6 border-t border-slate-100 pt-6">

                                <div class="mb-4 flex items-center gap-2">

                                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                                        <i class="bi bi-upc-scan"></i>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-800">
                                            Code Prefixes
                                        </h3>

                                        <p class="text-[11px] text-slate-400">
                                            Configure automatically generated system codes.
                                        </p>
                                    </div>

                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    ${
                                        prefixFields
                                            .sort(
                                                (a,b)=>
                                                    generalPrefixKeys.indexOf(a.key)-
                                                    generalPrefixKeys.indexOf(b.key)
                                            )
                                            .map(renderPrefixField)
                                            .join('')
                                    }
                                </div>

                            </section>
                        `
                        :''
                }


                ${
                    canUpdate&&(
                        organizationFields.length||
                        prefixFields.length
                    )
                        ?`
                            <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">

                                <button
                                    type="submit"
                                    id="settingsSaveBtn"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                                >
                                    <i class="bi bi-check2-circle"></i>
                                    Save Changes
                                </button>

                            </div>
                        `
                        :''
                }

            </div>
        </form>
    `;

    bindPanelEvents(
        imageFields
    );
}


/*
|--------------------------------------------------------------------------
| Standard Panel
|--------------------------------------------------------------------------
*/

function renderStandardPanel(fields){
    const imageFields=
        fields.filter(
            setting=>
                setting.type==='image'
        );

    const otherFields=
        fields.filter(
            setting=>
                setting.type!=='image'
        );

    panelsEl.innerHTML=`
        ${
            imageFields.length
                ?`
                    <div class="grid grid-cols-1 gap-4 border-b border-slate-100 p-5 md:grid-cols-2">
                        ${
                            imageFields
                                .map(renderImageField)
                                .join('')
                        }
                    </div>
                `
                :''
        }

        <form
            id="settingsForm"
            data-group="${AdminUI.escapeHtml(activeGroup)}"
            class="p-5"
        >

            <div
                id="settingsAlert"
                class="mb-4 hidden rounded-md border px-4 py-3 text-sm"
            ></div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                ${
                    otherFields.length
                        ?otherFields
                            .map(renderSettingField)
                            .join('')
                        :(
                            imageFields.length
                                ?''
                                :`
                                    <p class="text-sm text-slate-400">
                                        No settings in this group.
                                    </p>
                                `
                        )
                }
            </div>

            ${
                canUpdate&&otherFields.length
                    ?`
                        <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">

                            <button
                                type="submit"
                                id="settingsSaveBtn"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                            >
                                <i class="bi bi-check2-circle"></i>
                                Save Changes
                            </button>

                        </div>
                    `
                    :''
            }

        </form>
    `;

    bindPanelEvents(
        imageFields
    );
}


/*
|--------------------------------------------------------------------------
| Field Router
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Text Field
|--------------------------------------------------------------------------
*/

function renderTextField(setting){
    return `
        <div>
            <div class="mb-1.5 flex items-center justify-between">

                <label class="text-sm font-semibold text-slate-700">
                    ${
                        AdminUI.escapeHtml(
                            setting.description||
                            titleCase(setting.key)
                        )
                    }
                </label>

                ${
                    setting.is_public
                        ?`
                            <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">
                                Public
                            </span>
                        `
                        :''
                }

            </div>

            <input
                type="text"
                name="${AdminUI.escapeHtml(setting.key)}"
                value="${AdminUI.escapeHtml(setting.value??'')}"
                ${canUpdate?'':'disabled'}
                class="app-input"
                placeholder="${AdminUI.escapeHtml(titleCase(setting.key))}"
            >

            <p class="mt-1 text-[10px] text-slate-400">
                ${AdminUI.escapeHtml(setting.key)}
            </p>
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Prefix Field
|--------------------------------------------------------------------------
*/

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

    const fallback=
        fallbacks[setting.key]??'CODE';

    const value=
        String(
            setting.value||
            fallback
        ).toUpperCase();

    return `
        <div>

            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ${
                    AdminUI.escapeHtml(
                        labels[setting.key]||
                        setting.description||
                        titleCase(setting.key)
                    )
                }
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


/*
|--------------------------------------------------------------------------
| Select
|--------------------------------------------------------------------------
*/

function renderSelectField(setting){
    const options=
        Array.isArray(setting.options)
            ?setting.options
            :[];

    const optionsHtml=
        options.map(option=>{
            const selected=
                String(setting.value)===
                String(option)
                    ?'selected'
                    :'';

            const label=
                setting.key==='date_format'
                    ?`${formatDatePreview(option)} (${option})`
                    :titleCase(option);

            return `
                <option
                    value="${AdminUI.escapeHtml(option)}"
                    ${selected}
                >
                    ${AdminUI.escapeHtml(label)}
                </option>
            `;
        }).join('');

    return `
        <div>

            <div class="mb-1.5 flex items-center justify-between">

                <label class="text-sm font-semibold text-slate-700">
                    ${
                        AdminUI.escapeHtml(
                            setting.description||
                            titleCase(setting.key)
                        )
                    }
                </label>

                ${
                    setting.is_public
                        ?`
                            <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">
                                Public
                            </span>
                        `
                        :''
                }

            </div>

            <select
                name="${AdminUI.escapeHtml(setting.key)}"
                ${canUpdate?'':'disabled'}
                class="app-input"
            >
                ${optionsHtml}
            </select>

            <p class="mt-1 text-[10px] text-slate-400">
                ${AdminUI.escapeHtml(setting.key)}
            </p>

        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Date Preview
|--------------------------------------------------------------------------
*/

function formatDatePreview(format){
    const now=new Date();

    const d=
        String(
            now.getDate()
        ).padStart(2,'0');

    const m=
        String(
            now.getMonth()+1
        ).padStart(2,'0');

    const Y=
        now.getFullYear();

    const monthShort=[
        'Jan','Feb','Mar','Apr',
        'May','Jun','Jul','Aug',
        'Sep','Oct','Nov','Dec'
    ];

    const monthFull=[
        'January','February','March','April',
        'May','June','July','August',
        'September','October','November','December'
    ];

    const M=
        monthShort[now.getMonth()];

    const F=
        monthFull[now.getMonth()];

    const tokens={
        d,
        m,
        Y,
        M,
        F
    };

    return String(format).replace(
        /d|m|Y|M|F/g,
        token=>
            tokens[token]!==undefined
                ?String(tokens[token])
                :token
    );
}


/*
|--------------------------------------------------------------------------
| Password
|--------------------------------------------------------------------------
*/

function renderPasswordField(setting){
    return `
        <div>

            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                ${
                    AdminUI.escapeHtml(
                        setting.description||
                        titleCase(setting.key)
                    )
                }
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
                    class="settings-password-toggle absolute right-2.5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 hover:text-slate-600"
                >
                    <i class="bi bi-eye text-xs"></i>
                </button>

            </div>

            <p class="mt-1 text-[10px] text-slate-400">
                ${AdminUI.escapeHtml(setting.key)}
            </p>

        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Boolean
|--------------------------------------------------------------------------
*/

function renderToggleField(setting){
    const checked=
        setting.value==='1'||
        setting.value===1||
        setting.value===true;

    return `
        <div class="flex items-start justify-between gap-3 rounded-md border border-slate-200 p-3.5">

            <div>
                <p class="text-sm font-semibold text-slate-700">
                    ${
                        AdminUI.escapeHtml(
                            setting.description||
                            titleCase(setting.key)
                        )
                    }
                </p>

                <p class="mt-1 text-[10px] text-slate-400">
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


/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

function renderImageField(setting){
    const hasImage=
        Boolean(setting.value);

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
                        titleCase(setting.key)
                    )
            );

    return `
        <div class="rounded-md border border-slate-200 p-4">

            <div class="mb-3 flex items-center justify-between">

                <label class="text-sm font-semibold text-slate-700">
                    ${AdminUI.escapeHtml(title)}
                </label>

                ${
                    setting.is_public
                        ?`
                            <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">
                                Public
                            </span>
                        `
                        :''
                }

            </div>


            <div class="flex items-center gap-4">

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


                <div>
                    ${
                        canUpdate
                            ?`
                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">

                                    <i class="bi bi-upload text-[11px]"></i>

                                    ${
                                        hasImage
                                            ?'Replace'
                                            :'Upload'
                                    }

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


/*
|--------------------------------------------------------------------------
| Bind Events
|--------------------------------------------------------------------------
*/

function bindPanelEvents(imageFields=[]){
    const form=
        document.getElementById(
            'settingsForm'
        );

    if(form){
        form.addEventListener(
            'submit',
            handleSubmit
        );
    }


    imageFields.forEach(setting=>{
        const input=
            document.getElementById(
                'upload_'+setting.key
            );

        if(!input){
            return;
        }

        input.addEventListener(
            'change',
            event=>{
                handleImageUpload(
                    setting.key,
                    event.target.files[0]
                );
            }
        );
    });


    document
        .querySelectorAll(
            '.settings-password-toggle'
        )
        .forEach(button=>{
            button.addEventListener(
                'click',
                ()=>{
                    const input=
                        button.previousElementSibling;

                    const icon=
                        button.querySelector('i');

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
                }
            );
        });
}


/*
|--------------------------------------------------------------------------
| Image Upload
|--------------------------------------------------------------------------
*/

async function handleImageUpload(
    key,
    file
){
    if(!file){
        return;
    }

    const statusEl=
        document.getElementById(
            'uploadStatus_'+key
        );

    const previewEl=
        document.getElementById(
            'preview_'+key
        );

    statusEl.textContent=
        'Uploading...';

    statusEl.className=
        'mt-1.5 text-[10px] text-indigo-500';

    try{
        const formData=
            new FormData();

        formData.append(
            'key',
            key
        );

        formData.append(
            'file',
            file
        );

        const response=
            await api(
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

        statusEl.textContent=
            'Uploaded successfully.';

        statusEl.className=
            'mt-1.5 text-[10px] text-emerald-600';

        const settings=
            settingsData[activeGroup]??[];

        const setting=
            settings.find(
                item=>
                    item.key===key
            );

        if(setting){
            setting.value=
                response.data.value;
        }

        if(typeof window.Toast!=='undefined'){
            Toast.success(
                'Image uploaded successfully.'
            );
        }

    }catch(error){
        statusEl.textContent=
            AdminUI.extractError(error);

        statusEl.className=
            'mt-1.5 text-[10px] text-red-600';
    }
}


/*
|--------------------------------------------------------------------------
| Save Settings
|--------------------------------------------------------------------------
*/

async function handleSubmit(event){
    event.preventDefault();

    if(!canUpdate){
        return;
    }

    const form=
        event.target;

    const group=
        form.dataset.group;

    const fields=
        (settingsData[group]??[])
            .filter(
                setting=>
                    setting.type!=='image'
            );

    const alertBox=
        document.getElementById(
            'settingsAlert'
        );

    const saveBtn=
        document.getElementById(
            'settingsSaveBtn'
        );

    if(!saveBtn){
        return;
    }

    AdminUI.setLoading(
        saveBtn,
        'Saving...'
    );

    try{
        for(const setting of fields){
            const input=
                form.querySelector(
                    `[name="${setting.key}"]`
                );

            if(!input){
                continue;
            }

            let value;

            if(setting.type==='boolean'){
                value=
                    input.checked
                        ?'1'
                        :'0';

            }else if(setting.type==='password'){
                if(input.value===''){
                    continue;
                }

                value=input.value;

            }else{
                value=input.value;

                if(
                    generalPrefixKeys.includes(
                        setting.key
                    )
                ){
                    value=
                        value
                            .trim()
                            .toUpperCase()
                            .replace(/\s+/g,'');

                    input.value=value;
                }
            }


            await api(
                '/api/settings',
                {
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
                }
            );


            if(setting.type!=='password'){
                setting.value=value;
            }
        }


        alertBox.className=
            'mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';

        alertBox.textContent=
            'Settings updated successfully.';

        alertBox.classList.remove(
            'hidden'
        );


        if(typeof window.Toast!=='undefined'){
            Toast.success(
                'Settings updated successfully.'
            );
        }


        setTimeout(()=>{
            alertBox.classList.add(
                'hidden'
            );
        },3000);

    }catch(error){
        alertBox.className=
            'mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600';

        alertBox.textContent=
            AdminUI.extractError(error);

        alertBox.classList.remove(
            'hidden'
        );

    }finally{
        AdminUI.resetLoading(
            saveBtn
        );
    }
}


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
| Start
|--------------------------------------------------------------------------
*/

loadSettings();

});
</script>

@endpush