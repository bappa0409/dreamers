@extends('layouts.admin')

@section('title','System Settings')
@section('page_title','System Settings')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div
        class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-gear"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">
                    System Settings
                </h1>

                <p class=" text-xs 2xl:text-sm text-slate-500">
                    Manage organization, system and finance configuration.
                </p>
            </div>
        </div>

        <div id="loadingBadge" class="hidden items-center gap-2 text-xs 2xl:text-sm font-semibold text-slate-400">
            <i class="bi bi-arrow-repeat animate-spin"></i>
            Loading settings...
        </div>
    </div>

    {{-- Settings Wrapper --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Tabs --}}
        <div id="settingsTabs" class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50/50 p-2">
        </div>

        {{-- Loading Skeleton --}}
        <div id="settingsSkeleton" class="space-y-3 p-5">

            @for($i=0;$i<4;$i++) <div class="h-16 animate-pulse rounded-md bg-slate-100">
        </div>
        @endfor

    </div>

    {{-- Panels --}}
    <div id="settingsPanels" class="hidden">
    </div>
</div>

</div>

@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded',function(){

    const canUpdate=@json(
        auth()->user()->hasPermission('Setting.update')
    );

    const tabsEl=
        document.getElementById('settingsTabs');

    const panelsEl=
        document.getElementById('settingsPanels');

    const skeletonEl=
        document.getElementById('settingsSkeleton');

    const loadingBadge=
        document.getElementById('loadingBadge');

    let settingsData={};
    let activeGroup=null;

    const groupIcons={
        general:'bi-sliders',
        system:'bi-hdd-stack',
        loan: 'bi-bank',
        finance:'bi-cash-coin',
        backup:'bi-database-down'
    };

    const groupLabels={
        general:'General',
        system:'System',
        loan: 'Loan',
        finance:'Finance',
        backup:'Backup'
    };

    const groupOrder=[
        'general',
        'loan',
        'finance',
        'system',
        'backup'
    ];

    const organizationKeys=[
        'organization_name',
        'organization_type',
        'organization_registration_no',
        'organization_registration_authority',
        'organization_registration_date',
        'organization_established_date',
        'organization_email',
        'organization_phone',
        'organization_alternative_phone',
        'organization_website',
        'organization_address',
        'organization_district',
        'organization_country',
        'organization_description'
    ];

    const shareKeys=[
        'share_enabled',
        'default_share_value'
    ];

    const subscriptionKeys=[
        'subscription_generate_day',
        'subscription_fine_enabled'
    ];

    const membershipKeys=[
        'auto_activate_member'
    ];

    const generalPrefixKeys=[
        'member_code_prefix',
        'investment_code_prefix',
        'land_code_prefix',
        'project_code_prefix'
    ];

    const generalImageKeys=[
        'site_logo',
        'site_favicon',
        'site_logo_other',
        'logo',
        'favicon'
    ];

    const systemAppKeys=[
        'date_format',
        'time_format',
    ];

    const mailKeys=[
        'mail_mailer',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'mail_from_address',
        'mail_from_name'
    ];

    const securityKeys=[
        'session_lifetime_minutes',
        'max_login_attempts',
        'password_reset_expiry_minutes'
    ];

    const maintenanceKeys=[
        'maintenance_mode',
        'maintenance_message'
    ];

    // Display label shown above each image upload field. Falls back
    // to a title-cased version of the key when a setting's key isn't
    // listed here, so a newly seeded image setting never silently
    // inherits another field's label.
    const imageFieldTitles={
        site_logo:'Organization Logo',
        logo:'Organization Logo',
        site_favicon:'Browser Favicon',
        favicon:'Browser Favicon',
        site_logo_other:'Others'
    };

    // Short explanatory line shown directly under an image field's
    // label (e.g. what the image is used for). Only set where extra
    // context is genuinely needed.
    const imageFieldSubtitles={
        site_logo_other:'For Receipt, Invoice, Voucher.'
    };

    async function loadSettings(){
        loadingBadge.classList.remove('hidden');
        loadingBadge.classList.add('flex');

        try{
            const response=
                await api('/api/settings');

            const settings=
                response.data??[];

            settingsData={};

            settings.forEach(setting=>{
                let group=
                    setting.group??
                    'general';

                if(
                    group==='organization'||
                    group==='branding'
                ){
                    group='general';
                }

                if(
                    generalPrefixKeys.includes(
                        setting.key
                    )
                ){
                    group='general';
                }

                if(
                    generalImageKeys.includes(
                        setting.key
                    )
                ){
                    group='general';
                }

                setting.group=group;

                if(!settingsData[group]){
                    settingsData[group]=[];
                }

                settingsData[group].push(
                    setting
                );
            });

            const groups=
                Object.keys(settingsData)
                    .sort((a,b)=>{
                        const ai=
                            groupOrder.indexOf(a);

                        const bi=
                            groupOrder.indexOf(b);

                        return(
                            (ai===-1?999:ai)-
                            (bi===-1?999:bi)
                        );
                    });

            if(!groups.length){
                skeletonEl.innerHTML=`
                    <div class="p-10 text-center text-base text-slate-400">
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

            skeletonEl.classList.add(
                'hidden'
            );

            panelsEl.classList.remove(
                'hidden'
            );

        }catch(error){
            skeletonEl.innerHTML=`
                <div class="rounded-md border border-red-200 bg-red-50 p-5 text-base text-red-600">
                    ${
                        AdminUI.escapeHtml(
                            AdminUI.extractError(
                                error
                            )
                        )
                    }
                </div>
            `;
        }finally{
            loadingBadge.classList.add(
                'hidden'
            );

            loadingBadge.classList.remove(
                'flex'
            );
        }
    }

    function renderTabs(groups){
        tabsEl.innerHTML=
            groups.map(group=>{
                const active=
                    group===activeGroup;

                return`
                    <button
                        type="button"
                        data-group="${AdminUI.escapeHtml(group)}"
                        class="settings-tab-btn inline-flex cursor-pointer items-center gap-2 rounded-md px-3.5 py-2 text-xs 2xl:text-sm font-semibold transition ${
                            active
                                ?'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200'
                                :'text-slate-500 hover:bg-white hover:text-slate-700'
                        }">

                        <i class="bi ${
                            groupIcons[group]??
                            'bi-sliders'
                        } text-[13px]"></i>

                        ${
                            AdminUI.escapeHtml(
                                groupLabels[group]??
                                AdminUI.titleCase(group)
                            )
                        }
                    </button>
                `;
            }).join('');

        tabsEl
            .querySelectorAll(
                '.settings-tab-btn'
            )
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

    function renderPanel(){
        const fields=[
            ...(settingsData[
                activeGroup
            ]??[])
        ];

        if(activeGroup==='general'){
            renderGeneralPanel(fields);
            return;
        }

        if(activeGroup==='system'){
            renderSystemPanel(fields);
            return;
        }

        renderStandardPanel(fields);
    }

    function renderGeneralPanel(fields){
        const organizationFields=
            orderedFields(
                fields,
                organizationKeys
            );

        const imageFields=
            fields.filter(
                setting=>
                    setting.type==='image'
            );

        const prefixFields=
            orderedFields(
                fields,
                generalPrefixKeys
            );

        const shareFields=
            orderedFields(
                fields,
                shareKeys
            );

        const subscriptionFields=
            orderedFields(
                fields,
                subscriptionKeys
            );

        const membershipFields=
            orderedFields(
                fields,
                membershipKeys
            );

        panelsEl.innerHTML=`
            <div class="space-y-6 p-5">

                <div
                    id="settingsAlert"
                    class="hidden rounded-md border px-4 py-3 text-base">
                </div>

                ${
                    organizationFields.length
                        ?sectionForm({
                            section:'organization',
                            icon:'bi-building',
                            title:'Organization Information',
                            subtitle:'Identity, registration, contact and legal information of the organization.',
                            fields:organizationFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        organizationFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Organization'
                        })
                        :''
                }

                ${
                    imageFields.length
                        ?sectionBlock({
                            icon:'bi-image',
                            title:'Logo & Favicon',
                            subtitle:'Organization branding used throughout the application.',
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    ${
                                        imageFields
                                            .map(renderImageField)
                                            .join('')
                                    }
                                </div>
                            `
                        })
                        :''
                }

                ${
                    prefixFields.length
                        ?sectionForm({
                            section:'prefixes',
                            icon:'bi-upc-scan',
                            title:'Code Prefixes',
                            subtitle:'Configure automatically generated system reference codes.',
                            fields:prefixFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                                    ${
                                        prefixFields
                                            .map(renderPrefixField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Prefixes'
                        })
                        :''
                }

                ${
                    shareFields.length
                        ?sectionForm({
                            section:'shares',
                            icon:'bi-layers',
                            title:'Share Configuration',
                            subtitle:'Configure association share purchasing and the fixed value of one share.',
                            fields:shareFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        shareFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Share Settings'
                        })
                        :''
                }

                ${
                    subscriptionFields.length
                        ?sectionForm({
                            section:'subscriptions',
                            icon:'bi-calendar-check',
                            title:'Subscription Configuration',
                            subtitle:'Configure overdue subscription fines and automatic fine rules.',
                            fields:subscriptionFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        subscriptionFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Subscription Settings'
                        })
                        :''
                }

                ${
                    membershipFields.length
                        ?sectionForm({
                            section:'membership',
                            icon:'bi-people',
                            title:'Membership Configuration',
                            subtitle:'Configure how new members are activated.',
                            fields:membershipFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        membershipFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Membership Settings'
                        })
                        :''
                }

            </div>
        `;

        bindGeneralSectionEvents({
            organization:organizationFields,
            prefixes:prefixFields,
            shares:shareFields,
            subscriptions:subscriptionFields,
            membership:membershipFields
        });

        bindImageEvents(
            imageFields
        );

        bindPasswordToggles();

        setTimeout(()=>{
            if(
                typeof window.initDatePickers===
                'function'
            ){
                window.initDatePickers();
            }
        },0);
    }

    function renderSystemPanel(fields){
        const appFields=
            orderedFields(
                fields,
                systemAppKeys
            );

        const mailFields=
            orderedFields(
                fields,
                mailKeys
            );

        const securityFields=
            orderedFields(
                fields,
                securityKeys
            );

        const maintenanceFields=
            orderedFields(
                fields,
                maintenanceKeys
            );

        panelsEl.innerHTML=`
            <div class="space-y-6 p-5">

                <div
                    id="settingsAlert"
                    class="hidden rounded-md border px-4 py-3 text-base">
                </div>

                ${
                    appFields.length
                        ?sectionForm({
                            section:'app',
                            icon:'bi-sliders',
                            title:'Application Preferences',
                            subtitle:'Configure language, date and time display for the whole application.',
                            fields:appFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        appFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Application Settings'
                        })
                        :''
                }

                ${
                    mailFields.length
                        ?sectionForm({
                            section:'mail',
                            icon:'bi-envelope-at',
                            title:'Mail / SMTP',
                            subtitle:'Configure the mail driver used to send system emails.',
                            fields:mailFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        mailFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Mail Settings'
                        })
                        :''
                }

                ${
                    securityFields.length
                        ?sectionForm({
                            section:'security',
                            icon:'bi-shield-lock',
                            title:'Security',
                            subtitle:'Configure session lifetime and login protection rules.',
                            fields:securityFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        securityFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Security Settings'
                        })
                        :''
                }

                ${
                    maintenanceFields.length
                        ?sectionForm({
                            section:'maintenance',
                            icon:'bi-cone-striped',
                            title:'Maintenance',
                            subtitle:'Put the site into maintenance mode and customize the message shown to visitors.',
                            fields:maintenanceFields,
                            body:`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        maintenanceFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `,
                            buttonText:'Save Maintenance Settings'
                        })
                        :''
                }

            </div>
        `;

        bindGeneralSectionEvents({
            app:appFields,
            mail:mailFields,
            security:securityFields,
            maintenance:maintenanceFields
        });

        bindPasswordToggles();
    }

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
            <form
                id="settingsForm"
                data-group="${AdminUI.escapeHtml(activeGroup)}" novalidate data-js-validation="1">

                <div class="space-y-5 p-5">

                    <div
                        id="settingsAlert"
                        class="hidden rounded-md border px-4 py-3 text-base">
                    </div>

                    ${
                        imageFields.length
                            ?`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    ${
                                        imageFields
                                            .map(renderImageField)
                                            .join('')
                                    }
                                </div>
                            `
                            :''
                    }

                    ${
                        otherFields.length
                            ?`
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    ${
                                        otherFields
                                            .map(renderSettingField)
                                            .join('')
                                    }
                                </div>
                            `
                            :(
                                imageFields.length
                                    ?''
                                    :AdminUI.emptyState(
                                        'No settings in this group.'
                                    )
                            )
                    }

                    ${
                        canUpdate&&
                        otherFields.length
                            ?saveButton()
                            :''
                    }

                </div>
            </form>
        `;

        bindStandardPanelEvents(
            imageFields
        );
    }

    function orderedFields(
        fields,
        keys
    ){
        return keys
            .map(
                key=>
                    fields.find(
                        setting=>
                            setting.key===key
                    )
            )
            .filter(Boolean);
    }

    function sectionBlock({
        icon,
        title,
        subtitle,
        body
    }){
        return`
            <section class="rounded-md border border-slate-200 bg-white p-4">

                <div class="mb-4 flex items-center gap-2">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi ${icon} text-base"></i>
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

    function sectionForm({
        section,
        icon,
        title,
        subtitle,
        fields,
        body,
        buttonText
    }){
        return`
            <form
                class="settings-section-form rounded-md border border-slate-200 bg-white p-4"
                data-section="${AdminUI.escapeHtml(section)}" novalidate data-js-validation="1">

                <div class="mb-4 flex items-center gap-2">

                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi ${icon} text-base"></i>
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

                ${
                    canUpdate
                        ?`
                            <div class="mt-5 flex justify-end border-t border-slate-100 pt-4">

                                <button
                                    type="submit"
                                    class="settings-section-save inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">

                                    <i class="bi bi-check2-circle"></i>

                                    ${AdminUI.escapeHtml(buttonText)}

                                </button>

                            </div>
                        `
                        :''
                }

            </form>
        `;
    }

    function saveButton(){
        return`
            <div class="flex justify-end border-t border-slate-100 pt-5">

                <button
                    type="submit"
                    id="settingsSaveBtn"
                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">

                    <i class="bi bi-check2-circle"></i>

                    Save Changes

                </button>

            </div>
        `;
    }

    function renderSettingField(setting){
        if(setting.type==='boolean'){
            return renderToggleField(
                setting
            );
        }

        if(setting.type==='password'){
            return renderPasswordField(
                setting
            );
        }

        if(setting.type==='select'){
            return renderSelectField(
                setting
            );
        }

        return renderTextField(
            setting
        );
    }

    function renderLabel(setting){
        return AdminUI.escapeHtml(
            setting.description||
            AdminUI.titleCase(
                setting.key
            )
        );
    }

    function publicBadge(setting){
        if(!setting.is_public){
            return'';
        }

        return`
            <span class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-600">
                Public
            </span>
        `;
    }

    function inputType(setting){
    const dateKeys=[
        'organization_registration_date',
        'organization_established_date'
    ];

    if(dateKeys.includes(setting.key)){
        return'date';
    }

    if(setting.key.includes('email')){
        return'email';
    }

    if(setting.key.includes('website')){
        return'url';
    }

    if(
        setting.type==='integer'||
        setting.type==='float'
    ){
        return'number';
    }

    return'text';
}

    function renderTextField(setting){
    const isDate=[
        'organization_registration_date',
        'organization_established_date'
    ].includes(setting.key);

    let extra='';

    if(setting.type==='integer'){
        extra='step="1"';
    }

    if(setting.type==='float'){
        extra='step="0.01"';
    }

    const fullWidth=
        setting.key==='organization_description'||
        setting.key==='organization_address';

    if(isDate){
        return`
            <div class="min-w-0">

                <div class="mb-1.5 flex items-center justify-between gap-2">

                    <label class="truncate  text-xs 2xl:text-sm font-semibold text-slate-700">
                        ${renderLabel(setting)}
                    </label>

                    ${publicBadge(setting)}

                </div>

                <div class="relative">

                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs 2xl:text-sm text-slate-400"></i>

                    <input
                        type="text"
                        name="${AdminUI.escapeHtml(setting.key)}"
                        value="${AdminUI.escapeHtml(setting.value??'')}"
                        class="app-input js-date-picker !pl-9"
                        placeholder="Select date"
                        autocomplete="off"
                        ${canUpdate?'':'disabled'}>

                </div>

                <p class="mt-1 truncate font-mono text-[9px] text-slate-400">
                    ${AdminUI.escapeHtml(setting.key)}
                </p>

            </div>
        `;
    }

    const type=
        setting.key.includes('email')
            ?'email'
            :setting.key.includes('website')
                ?'url'
                :(
                    setting.type==='integer'||
                    setting.type==='float'
                )
                    ?'number'
                    :'text';

    return`
        <div class="min-w-0 ${
            fullWidth
                ?'md:col-span-2'
                :''
        }">

            <div class="mb-1.5 flex items-center justify-between gap-2">

                <label class="truncate  text-xs 2xl:text-sm font-semibold text-slate-700">
                    ${renderLabel(setting)}
                </label>

                ${publicBadge(setting)}

            </div>

            <input
                type="${type}"
                name="${AdminUI.escapeHtml(setting.key)}"
                value="${AdminUI.escapeHtml(setting.value??'')}"
                ${extra}
                ${canUpdate?'':'disabled'}
                class="app-input"
                placeholder="${AdminUI.escapeHtml(
                    AdminUI.titleCase(setting.key)
                )}">

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

        const fallback=
            fallbacks[setting.key]??
            'CODE';

        const value=
            String(
                setting.value||
                fallback
            ).toUpperCase();

        return`
            <div>

                <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-700">
                    ${
                        AdminUI.escapeHtml(
                            labels[setting.key]||
                            setting.description||
                            AdminUI.titleCase(
                                setting.key
                            )
                        )
                    }
                </label>

                <input
                    type="text"
                    name="${AdminUI.escapeHtml(setting.key)}"
                    value="${AdminUI.escapeHtml(value)}"
                    maxlength="20"
                    ${
                        canUpdate
                            ?''
                            :'disabled'
                    }
                    class="app-input uppercase"
                    placeholder="${AdminUI.escapeHtml(fallback)}">

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
        const options=
            Array.isArray(
                setting.options
            )
                ?setting.options
                :[];

        const optionsHtml=
            options.map(option=>{
                const selected=
                    String(setting.value)===
                    String(option)
                        ?'selected'
                        :'';

                let label=
                    AdminUI.titleCase(
                        option
                    );

                if(
                    setting.key===
                    'date_format'
                ){
                    label=
                        `${formatDatePreview(option)} (${option})`;
                }

                if(
                    setting.key===
                    'organization_type'
                ){
                    const typeLabels={
                        association:'Association',
                        cooperative:'Cooperative',
                        society:'Society',
                        club:'Club',
                        foundation:'Foundation',
                        trust:'Trust',
                        ngo:'NGO',
                        non_profit:'Non-Profit Organization',
                        community_organization:'Community Organization',
                        other:'Other'
                    };

                    label=
                        typeLabels[option]??
                        label;
                }

                return`
                    <option
                        value="${AdminUI.escapeHtml(option)}"
                        ${selected}>

                        ${AdminUI.escapeHtml(label)}

                    </option>
                `;
            }).join('');

        return`
            <div class="min-w-0">

                <div class="mb-1.5 flex items-center justify-between gap-2">

                    <label class="truncate  text-xs 2xl:text-sm font-semibold text-slate-700">
                        ${renderLabel(setting)}
                    </label>

                    ${publicBadge(setting)}

                </div>

                <select
                    name="${AdminUI.escapeHtml(setting.key)}"
                    ${
                        canUpdate
                            ?''
                            :'disabled'
                    }
                    class="app-input cursor-pointer">

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

                <label class="mb-1.5 block text-xs 2xl:text-sm font-semibold text-slate-700">
                    ${renderLabel(setting)}
                </label>

                <div class="relative">

                    <input
                        type="password"
                        name="${AdminUI.escapeHtml(setting.key)}"
                        value=""
                        autocomplete="new-password"
                        ${
                            canUpdate
                                ?''
                                :'disabled'
                        }
                        class="app-input pr-9"
                        placeholder="Leave blank to keep current value">

                    <button
                        type="button"
                        class="settings-password-toggle absolute right-2.5 top-1/2 -translate-y-1/2 cursor-pointer text-slate-400 transition hover:text-slate-600">

                        <i class="bi bi-eye text-sm"></i>

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

                    <p class="text-sm font-semibold text-slate-700">
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
                        ${
                            checked
                                ?'checked'
                                :''
                        }
                        ${
                            canUpdate
                                ?''
                                :'disabled'
                        }
                        class="peer sr-only">

                    <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-indigo-600 peer-disabled:opacity-50"></div>

                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></div>

                </label>

            </div>
        `;
    }

    function renderImageField(setting){
        const hasImage=
            Boolean(setting.value);

        const previewUrl=
            hasImage
                ?`/storage/${setting.value}`
                :null;

        const title=
            imageFieldTitles[setting.key]??
            AdminUI.titleCase(setting.key);

        const subtitle=
            imageFieldSubtitles[setting.key]??
            null;

        const caption=
            'PNG, JPG, WEBP or ICO. Max 2MB.';

        return`
            <div class="rounded-md border border-slate-200 p-3">

                <div class="mb-3">

                    <div class="flex items-center justify-between gap-2">

                        <label class="truncate  text-xs 2xl:text-sm font-semibold text-slate-700">
                            ${AdminUI.escapeHtml(title)}
                        </label>

                        ${publicBadge(setting)}

                    </div>

                    ${
                        subtitle
                            ?`
                                <p class="mt-0.5 text-[10px] text-slate-400">
                                    ${AdminUI.escapeHtml(subtitle)}
                                </p>
                            `
                            :''
                    }

                </div>

                <div class="flex items-center gap-3">

                    <div
                        id="preview_${AdminUI.escapeHtml(setting.key)}"
                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md border border-dashed border-slate-300 bg-slate-50">

                        ${
                            previewUrl
                                ?`
                                    <img
                                        src="${AdminUI.escapeHtml(previewUrl)}"
                                        alt="${AdminUI.escapeHtml(setting.key)}"
                                        class="h-full w-full object-contain">
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
                                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                                        <i class="bi bi-upload text-[11px]"></i>

                                        ${
                                            hasImage
                                                ?'Replace'
                                                :'Upload'
                                        }

                                        <input
                                            type="file"
                                            id="upload_${AdminUI.escapeHtml(setting.key)}"
                                            accept=".png,.jpg,.jpeg,.webp,.ico"
                                            class="hidden">

                                    </label>
                                `
                                :''
                        }

                        <p
                            id="uploadStatus_${AdminUI.escapeHtml(setting.key)}"
                            class="mt-1.5 text-[10px] text-slate-400">

                            ${AdminUI.escapeHtml(caption)}

                        </p>

                    </div>

                </div>

            </div>
        `;
    }

    function bindGeneralSectionEvents(sectionMap){
        document
            .querySelectorAll(
                '.settings-section-form'
            )
            .forEach(form=>{
                form.addEventListener(
                    'submit',
                    async event=>{
                        event.preventDefault();

                        const section=
                            form.dataset.section;

                        const fields=
                            sectionMap[section]??[];

                        await saveSection(
                            form,
                            fields
                        );
                    }
                );
            });
    }

    function bindStandardPanelEvents(
        imageFields=[]
    ){
        const form=
            document.getElementById(
                'settingsForm'
            );

        if(form){
            form.addEventListener(
                'submit',
                handleStandardSubmit
            );
        }

        bindImageEvents(
            imageFields
        );

        bindPasswordToggles();
    }

    function bindImageEvents(
        imageFields=[]
    ){
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
                        event.target.files?.[0]??null
                    );
                }
            );
        });
    }

    function bindPasswordToggles(){
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
                            button.querySelector(
                                'i'
                            );

                        if(
                            input.type===
                            'password'
                        ){
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

        if(
            file.size>
            2*1024*1024
        ){
            statusEl.textContent=
                'Image must be 2MB or smaller.';

            statusEl.className=
                'mt-1.5 text-[10px] text-red-600';

            return;
        }

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
                    alt="${AdminUI.escapeHtml(key)}">
            `;

            statusEl.textContent=
                'Uploaded successfully.';

            statusEl.className=
                'mt-1.5 text-[10px] text-emerald-600';

            Object.values(
                settingsData
            ).flat().forEach(setting=>{
                if(setting.key===key){
                    setting.value=
                        response.data.value;
                }
            });

            if(window.Toast){
                Toast.success(
                    'Image uploaded successfully.'
                );
            }

        }catch(error){
            statusEl.textContent=
                AdminUI.extractError(
                    error
                );

            statusEl.className=
                'mt-1.5 text-[10px] text-red-600';
        }
    }

    async function saveSection(
        form,
        fields
    ){
        if(!canUpdate){
            return;
        }

        const saveBtn=
            form.querySelector(
                '.settings-section-save'
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

                const value=
                    getFieldValue(
                        setting,
                        input
                    );

                if(
                    setting.type==='password'&&
                    value===null
                ){
                    continue;
                }

                await saveSetting(
                    setting,
                    value
                );

                if(
                    setting.type!==
                    'password'
                ){
                    setting.value=
                        value;
                }
            }

            showSettingsAlert(
                'Settings saved successfully.',
                'success'
            );

            if(window.Toast){
                Toast.success(
                    'Settings saved successfully.'
                );
            }

        }catch(error){
            showSettingsAlert(
                AdminUI.extractError(error),
                'error'
            );

        }finally{
            AdminUI.resetLoading(
                saveBtn
            );
        }
    }

    async function handleStandardSubmit(
        event
    ){
        event.preventDefault();

        if(!canUpdate){
            return;
        }

        const form=
            event.target;

        const group=
            form.dataset.group;

        const fields=
            (
                settingsData[
                    group
                ]??[]
            ).filter(
                setting=>
                    setting.type!=='image'
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

                const value=
                    getFieldValue(
                        setting,
                        input
                    );

                if(
                    setting.type==='password'&&
                    value===null
                ){
                    continue;
                }

                await saveSetting(
                    setting,
                    value
                );

                if(
                    setting.type!==
                    'password'
                ){
                    setting.value=
                        value;
                }
            }

            showSettingsAlert(
                'Settings saved successfully.',
                'success'
            );

            if(window.Toast){
                Toast.success(
                    'Settings saved successfully.'
                );
            }

        }catch(error){
            showSettingsAlert(
                AdminUI.extractError(error),
                'error'
            );

        }finally{
            AdminUI.resetLoading(
                saveBtn
            );
        }
    }

    function getFieldValue(
        setting,
        input
    ){
        if(
            setting.type===
            'boolean'
        ){
            return input.checked
                ?'1'
                :'0';
        }

        if(
            setting.type===
            'password'
        ){
            if(
                input.value===''
            ){
                return null;
            }

            return input.value;
        }

        let value=
            input.value;

        if(
            generalPrefixKeys.includes(
                setting.key
            )
        ){
            value=
                value
                    .trim()
                    .toUpperCase()
                    .replace(
                        /\s+/g,
                        ''
                    );

            input.value=
                value;
        }

        return value;
    }

    async function saveSetting(
        setting,
        value
    ){
        return api(
            '/api/settings',
            {
                method:'POST',
                body:JSON.stringify({
                    key:
                        setting.key,

                    value,

                    type:
                        setting.type,

                    group:
                        setting.group,

                    description:
                        setting.description,

                    is_public:
                        setting.is_public,

                    options:
                        setting.options??
                        null
                })
            }
        );
    }

    function showSettingsAlert(
        message,
        type='success'
    ){
        const alertBox=
            document.getElementById(
                'settingsAlert'
            );

        if(!alertBox){
            return;
        }

        alertBox.className=
            type==='success'
                ?'rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-700'
                :'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-base text-red-600';

        alertBox.textContent=
            message;

        alertBox.classList.remove(
            'hidden'
        );

        setTimeout(()=>{
            alertBox.classList.add(
                'hidden'
            );
        },3000);
    }

    function formatDatePreview(
        format
    ){
        const now=
            new Date();

        const tokens={
            d:String(
                now.getDate()
            ).padStart(2,'0'),

            m:String(
                now.getMonth()+1
            ).padStart(2,'0'),

            Y:
                now.getFullYear(),

            M:[
                'Jan','Feb','Mar',
                'Apr','May','Jun',
                'Jul','Aug','Sep',
                'Oct','Nov','Dec'
            ][now.getMonth()],

            F:[
                'January',
                'February',
                'March',
                'April',
                'May',
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December'
            ][now.getMonth()]
        };

        return String(format)
            .replace(
                /d|m|Y|M|F/g,
                token=>
                    tokens[token]??
                    token
            );
    }

    async function initSettingsPage(){
        if(
            typeof window.AdminUI===
                'undefined'||
            typeof window.api===
                'undefined'
        ){
            setTimeout(
                initSettingsPage,
                50
            );

            return;
        }

        await loadSettings();
    }

    initSettingsPage();

});
</script>

@endpush