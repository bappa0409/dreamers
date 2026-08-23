/*
|--------------------------------------------------------------------------
| Global Confirmation Modal
|--------------------------------------------------------------------------
*/

function ensureAdminConfirmModal(){
    if(document.getElementById('adminConfirmModal'))return;

    document.body.insertAdjacentHTML('beforeend',`
        <div id="adminConfirmModal"
            class="app-modal-overlay fixed inset-0 z-[9999] hidden items-center justify-center p-3 sm:p-5">
            <div class="app-modal-panel w-full max-w-md overflow-hidden rounded-md bg-white">
                <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <div id="adminConfirmIconWrap"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i id="adminConfirmIcon" class="bi bi-question-circle"></i>
                        </div>

                        <div class="min-w-0">
                            <h2 id="adminConfirmTitle"
                                class="text-base font-bold text-slate-800">
                                Confirm Action
                            </h2>

                            <p id="adminConfirmSubtitle"
                                class="mt-0.5 text-xs text-slate-500">
                                Please confirm before continuing.
                            </p>
                        </div>
                    </div>

                    <button type="button"
                        id="adminConfirmClose"
                        class="app-modal-close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="px-5 py-5">
                    <p id="adminConfirmMessage"
                        class="whitespace-pre-line break-words text-sm leading-6 text-slate-600">
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-4">
                    <button type="button"
                        id="adminConfirmCancel"
                        class="cursor-pointer rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                        Cancel
                    </button>

                    <button type="button"
                        id="adminConfirmSubmit"
                        class="cursor-pointer rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    `);
}

function adminConfirmTheme(type){
    const themes={
        primary:{
            icon:'bi-question-circle',
            iconWrap:'bg-indigo-50 text-indigo-600',
            button:'bg-indigo-600 hover:bg-indigo-700'
        },
        success:{
            icon:'bi-check-circle',
            iconWrap:'bg-emerald-50 text-emerald-600',
            button:'bg-emerald-600 hover:bg-emerald-700'
        },
        warning:{
            icon:'bi-exclamation-triangle',
            iconWrap:'bg-amber-50 text-amber-600',
            button:'bg-amber-600 hover:bg-amber-700'
        },
        danger:{
            icon:'bi-trash3',
            iconWrap:'bg-red-50 text-red-600',
            button:'bg-red-600 hover:bg-red-700'
        }
    };

    return themes[type]??themes.primary;
}

/*
|--------------------------------------------------------------------------
| Admin UI
|--------------------------------------------------------------------------
*/

window.AdminUI={
    /*
    |--------------------------------------------------------------------------
    | Common
    |--------------------------------------------------------------------------
    */

    escapeHtml(value){
        if(value===null||value===undefined)return '';

        return String(value)
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'","&#039;");
    },

    extractError(error){
        if(error?.data?.errors){
            const errors=Object.values(
                error.data.errors
            ).flat();

            if(errors.length){
                return errors.join(' ');
            }
        }

        return error?.data?.message||
            error?.data?.error||
            error?.message||
            'Something went wrong.';
    },

    debounce(callback,delay=350){
        let timer;

        return(...args)=>{
            clearTimeout(timer);

            timer=setTimeout(
                ()=>callback(...args),
                delay
            );
        };
    },

    query(params={}){
        const query=new URLSearchParams();

        Object.entries(params).forEach(([key,value])=>{
            if(
                value===null||
                value===undefined||
                value===''
            ){
                return;
            }

            query.set(
                key,
                value
            );
        });

        return query.toString();
    },

    formData(data={}){
        const form=new FormData();

        Object.entries(data).forEach(([key,value])=>{
            if(
                value===null||
                value===undefined||
                value===''
            ){
                return;
            }

            if(Array.isArray(value)){
                value.forEach(item=>{
                    form.append(
                        `${key}[]`,
                        item
                    );
                });

                return;
            }

            if(typeof value==='boolean'){
                form.append(
                    key,
                    value?'1':'0'
                );

                return;
            }

            form.append(
                key,
                value
            );
        });

        return form;
    },

    /*
    |--------------------------------------------------------------------------
    | Modal
    |--------------------------------------------------------------------------
    */

    openModal(id){
        const modal=document.getElementById(id);

        if(!modal)return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.body.classList.add(
            'overflow-hidden'
        );

        requestAnimationFrame(()=>{
            const autofocus=modal.querySelector(
                '[autofocus]'
            );

            autofocus?.focus?.();
        });
    },

    closeModal(id){
        const modal=document.getElementById(id);

        if(!modal)return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if(
            !document.querySelector(
                '.app-modal-overlay.flex'
            )
        ){
            document.body.classList.remove(
                'overflow-hidden'
            );
        }
    },

    closeAllModals(){
        document
            .querySelectorAll('.app-modal-overlay')
            .forEach(modal=>{
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

        document.body.classList.remove(
            'overflow-hidden'
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Loading Button
    |--------------------------------------------------------------------------
    */

    setLoading(button,text='Processing...'){
        if(typeof button==='string'){
            button=document.getElementById(
                button
            );
        }

        if(!button)return;

        if(!button.dataset.originalHtml){
            button.dataset.originalHtml=
                button.innerHTML;
        }

        button.disabled=true;

        button.innerHTML=`
            <span class="inline-flex items-center gap-1.5">
                <i class="bi bi-arrow-repeat animate-spin"></i>
                ${this.escapeHtml(text)}
            </span>
        `;
    },

    resetLoading(button){
        if(typeof button==='string'){
            button=document.getElementById(
                button
            );
        }

        if(!button)return;

        button.disabled=false;

        if(button.dataset.originalHtml){
            button.innerHTML=
                button.dataset.originalHtml;

            delete button.dataset.originalHtml;
        }
    },

    /*
    |--------------------------------------------------------------------------
    | General Error Box
    |--------------------------------------------------------------------------
    */

    showError(id,message){
        const box=document.getElementById(id);

        if(!box)return;

        box.textContent=message;
        box.classList.remove('hidden');
    },

    clearError(id){
        const box=document.getElementById(id);

        if(!box)return;

        box.textContent='';
        box.classList.add('hidden');
    },

    /*
    |--------------------------------------------------------------------------
    | Field Validation
    |--------------------------------------------------------------------------
    */

    getForm(form){
        if(typeof form==='string'){
            return document.getElementById(
                form
            );
        }

        return form;
    },

    getField(field){
        if(typeof field==='string'){
            return document.getElementById(
                field
            );
        }

        return field;
    },

    clearFieldError(field){
        const input=this.getField(field);

        if(!input)return;

        input.classList.remove(
            'is-invalid',
            'border-red-400',
            'border-red-500',
            'focus:border-red-500',
            'focus:ring-red-100'
        );

        input.removeAttribute(
            'aria-invalid'
        );

        const form=input.closest('form');

        if(!form||!input.id)return;

        const error=form.querySelector(
            `[data-field-error="${CSS.escape(input.id)}"]`
        );

        if(error){
            error.textContent='';
            error.classList.add('hidden');
        }
    },

    clearFieldError(field){
        const input=this.getField(field);

        if(!input)return;

        input.classList.remove('is-invalid');
        input.removeAttribute('aria-invalid');

        const form=input.closest('form');

        if(!form||!input.id)return;

        const error=form.querySelector(
            `[data-field-error="${CSS.escape(input.id)}"]`
        );

        if(error){
            error.textContent='';
            error.classList.add('hidden');
        }
    },

    setFieldError(field,message){
        const input=this.getField(field);

        if(!input)return false;

        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        input.setAttribute('aria-invalid','true');

        const form=input.closest('form');

        if(!form||!input.id){
            return true;
        }

        const error=form.querySelector(
            `[data-field-error="${CSS.escape(input.id)}"]`
        );

        if(error){
            error.textContent=String(
                message??'Invalid value.'
            );
            error.classList.remove('hidden');
        }

        return true;
    },

    showFieldError(field,message){
        return this.setFieldError(
            field,
            message
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Alias
    |--------------------------------------------------------------------------
    |
    | Some pages use AdminUI.showFieldError().
    | Internally both use the same validation logic.
    |
    */

    showFieldError(field,message){
        return this.setFieldError(
            field,
            message
        );
    },

    showValidationErrors(
        form,
        error,
        fieldMap={}
    ){
        form=this.getForm(form);

        if(!form){
            return false;
        }

        const errors=
            error?.data?.errors;

        if(
            !errors||
            typeof errors!=='object'
        ){
            return false;
        }

        this.clearFieldErrors(form);

        let firstInvalid=null;
        let handled=false;

        Object.entries(errors)
            .forEach(([field,messages])=>{
                const inputId=
                    fieldMap[field]||
                    field;

                const message=
                    Array.isArray(messages)
                        ?messages[0]
                        :messages;

                const input=
                    document.getElementById(
                        inputId
                    );

                if(!input){
                    return;
                }

                this.setFieldError(
                    input,
                    message
                );

                handled=true;

                if(!firstInvalid){
                    firstInvalid=input;
                }
            });

        if(firstInvalid){
            requestAnimationFrame(()=>{
                firstInvalid.scrollIntoView({
                    behavior:'smooth',
                    block:'center'
                });

                firstInvalid.focus?.({
                    preventScroll:true
                });
            });
        }

        return handled;
    },

    bindFieldValidation(form){
        form=this.getForm(form);

        if(
            !form||
            form.dataset.validationBound==='1'
        ){
            return;
        }

        form.dataset.validationBound='1';

        const clear=event=>{
            const input=event.target;

            if(
                !input||
                !input.id||
                !input.classList?.contains(
                    'is-invalid'
                )
            ){
                return;
            }

            this.clearFieldError(
                input
            );
        };

        form.addEventListener(
            'input',
            clear
        );

        form.addEventListener(
            'change',
            clear
        );
    },

    clearFieldErrors(form){
    form=this.getForm(form);

    if(!form)return;

    form.querySelectorAll('.is-invalid')
        .forEach(input=>{
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        });

    form.querySelectorAll('[data-field-error]')
        .forEach(error=>{
            error.textContent='';
            error.classList.add('hidden');
        });
},

    /*
    |--------------------------------------------------------------------------
    | Required Validation
    |--------------------------------------------------------------------------
    */

    validateRequired(
        form,
        messages={}
    ){
        form=this.getForm(form);

        if(!form){
            return true;
        }

        this.clearFieldErrors(form);

        let valid=true;
        let firstInvalid=null;

        form.querySelectorAll(
            '[required]'
        ).forEach(input=>{
            if(input.disabled){
                return;
            }

            const value=
                input.type==='checkbox'
                    ?input.checked
                    :String(
                        input.value??''
                    ).trim();

            const invalid=
                input.type==='checkbox'
                    ?!value
                    :value==='';

            if(!invalid){
                return;
            }

            valid=false;

            this.setFieldError(
                input,
                messages[input.id]||
                'This field is required.'
            );

            if(!firstInvalid){
                firstInvalid=input;
            }
        });

        if(firstInvalid){
            firstInvalid.scrollIntoView({
                behavior:'smooth',
                block:'center'
            });

            firstInvalid.focus?.({
                preventScroll:true
            });
        }

        return valid;
    },

    /*
    |--------------------------------------------------------------------------
    | Formatters
    |--------------------------------------------------------------------------
    */

    formatDate(value,withTime=false){
        if(!value)return 'N/A';

        let date;

        if(
            typeof value==='string'&&
            /^\d{4}-\d{2}-\d{2}$/.test(value)
        ){
            const[
                year,
                month,
                day
            ]=value
                .split('-')
                .map(Number);

            date=new Date(
                year,
                month-1,
                day
            );

        }else{
            date=new Date(value);
        }

        if(
            Number.isNaN(
                date.getTime()
            )
        ){
            return 'N/A';
        }

        const options={
            day:'2-digit',
            month:'short',
            year:'numeric'
        };

        if(withTime){
            options.hour='2-digit';
            options.minute='2-digit';
        }

        return date.toLocaleString(
            'en-GB',
            options
        );
    },

    formatBytes(bytes){
        const value=Number(bytes??0);

        if(!Number.isFinite(value)){
            return '0 B';
        }

        if(value<1024){
            return `${value} B`;
        }

        if(value<1048576){
            return `${(
                value/1024
            ).toFixed(1)} KB`;
        }

        if(value<1073741824){
            return `${(
                value/1048576
            ).toFixed(1)} MB`;
        }

        return `${(
            value/1073741824
        ).toFixed(1)} GB`;
    },

    formatNumber(value,decimals=0){
        return Number(
            value??0
        ).toLocaleString(
            'en-US',
            {
                minimumFractionDigits:decimals,
                maximumFractionDigits:decimals
            }
        );
    },

    titleCase(value){
        return String(value??'')
            .replaceAll('_',' ')
            .replace(
                /\b\w/g,
                char=>char.toUpperCase()
            );
    },

    /*
    |--------------------------------------------------------------------------
    | Badges
    |--------------------------------------------------------------------------
    */

    statusBadge(status){
        const map={
            active:
                'bg-emerald-50 text-emerald-700',

            approved:
                'bg-emerald-50 text-emerald-700',

            completed:
                'bg-emerald-50 text-emerald-700',

            sent:
                'bg-emerald-50 text-emerald-700',

            paid:
                'bg-emerald-50 text-emerald-700',

            verified:
                'bg-emerald-50 text-emerald-700',

            published:
                'bg-emerald-50 text-emerald-700',

            purchased:
                'bg-emerald-50 text-emerald-700',

            pending:
                'bg-amber-50 text-amber-700',

            partial:
                'bg-amber-50 text-amber-700',

            overdue:
                'bg-amber-50 text-amber-700',

            processing:
                'bg-amber-50 text-amber-700',

            sending:
                'bg-amber-50 text-amber-700',

            negotiating:
                'bg-amber-50 text-amber-700',

            on_hold:
                'bg-amber-50 text-amber-700',

            rejected:
                'bg-red-50 text-red-700',

            suspended:
                'bg-red-50 text-red-700',

            failed:
                'bg-red-50 text-red-700',

            urgent:
                'bg-red-50 text-red-700',

            inactive:
                'bg-slate-100 text-slate-600',

            cancelled:
                'bg-slate-100 text-slate-600',

            draft:
                'bg-slate-100 text-slate-600',

            waived:
                'bg-slate-100 text-slate-600',

            retired:
                'bg-slate-100 text-slate-600',

            transferred:
                'bg-sky-50 text-sky-700',

            planned:
                'bg-slate-100 text-slate-600',

            ended:
                'bg-slate-100 text-slate-600',

            upcoming:
                'bg-indigo-50 text-indigo-700',

            scheduled:
                'bg-indigo-50 text-indigo-700',

            sold:
                'bg-sky-50 text-sky-700',

            disposed:
                'bg-sky-50 text-sky-700',

            reopened:
                'bg-sky-50 text-sky-700'
        };

        const value=String(
            status??'unknown'
        );

        return `
            <span class="inline-flex max-w-full items-center rounded-md px-2 py-1 text-[10px] font-semibold ${map[value]??'bg-slate-100 text-slate-600'}">
                <span class="truncate">
                    ${this.escapeHtml(
                        this.titleCase(
                            value
                        )
                    )}
                </span>
            </span>
        `;
    },

    priorityBadge(priority){
        const map={
            low:
                'bg-sky-50 text-sky-700',

            normal:
                'bg-slate-100 text-slate-600',

            high:
                'bg-amber-50 text-amber-700',

            urgent:
                'bg-red-50 text-red-700'
        };

        const value=String(
            priority??'normal'
        );

        return `
            <span class="inline-flex max-w-full items-center rounded-md px-2 py-1 text-[10px] font-semibold ${map[value]??map.normal}">
                <span class="truncate">
                    ${this.escapeHtml(
                        this.titleCase(
                            value
                        )
                    )}
                </span>
            </span>
        `;
    },

    visibilityBadge(value){
        const map={
            public:
                'bg-emerald-50 text-emerald-700',

            members:
                'bg-indigo-50 text-indigo-700',

            internal:
                'bg-amber-50 text-amber-700',

            private:
                'bg-red-50 text-red-700'
        };

        const visibility=String(
            value??'internal'
        );

        return `
            <span class="inline-flex max-w-full items-center rounded-md px-2 py-1 text-[9px] font-semibold ${map[visibility]??map.internal}">
                <span class="truncate">
                    ${this.escapeHtml(
                        this.titleCase(
                            visibility
                        )
                    )}
                </span>
            </span>
        `;
    },

    /*
    |--------------------------------------------------------------------------
    | Empty / Loading State
    |--------------------------------------------------------------------------
    */

    emptyState(
        message='No data found.',
        colspan=null
    ){
        if(colspan){
            return `
                <tr>
                    <td colspan="${colspan}"
                        class="px-6 py-10 text-center text-sm text-slate-400">
                        ${this.escapeHtml(message)}
                    </td>
                </tr>
            `;
        }

        return `
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
                ${this.escapeHtml(message)}
            </div>
        `;
    },

    loadingState(
        message='Loading...',
        colspan=null
    ){
        if(colspan){
            return `
                <tr>
                    <td colspan="${colspan}"
                        class="px-6 py-10 text-center text-sm text-slate-400">
                        <div class="inline-flex items-center gap-2">
                            <i class="bi bi-arrow-repeat animate-spin"></i>
                            ${this.escapeHtml(message)}
                        </div>
                    </td>
                </tr>
            `;
        }

        return `
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
                <div class="inline-flex items-center gap-2">
                    <i class="bi bi-arrow-repeat animate-spin"></i>
                    ${this.escapeHtml(message)}
                </div>
            </div>
        `;
    },

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    renderPagination(
        paginator,
        container=null,
        onPageChange=null
    ){
        let config;

        if(arguments.length>=2){
            config={
                container,
                currentPage:
                    paginator?.current_page??1,

                lastPage:
                    paginator?.last_page??1,

                total:
                    paginator?.total??null,

                onPageChange
            };

        }else{
            config=paginator||{};
        }

        const{
            currentPage=1,
            lastPage=1,
            total=null
        }=config;

        const element=
            typeof config.container==='string'
                ?document.getElementById(
                    config.container
                )
                :config.container;

        if(!element)return;

        if(lastPage<=1){
            element.innerHTML='';
            return;
        }

        const pages=[];
        const start=Math.max(
            1,
            currentPage-2
        );

        const end=Math.min(
            lastPage,
            currentPage+2
        );

        for(
            let page=start;
            page<=end;
            page++
        ){
            pages.push(page);
        }

        element.innerHTML=`
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <span class="text-xs text-slate-500">
                    Page ${currentPage} of ${lastPage}
                    ${
                        total!==null
                            ?` • ${total} records`
                            :''
                    }
                </span>

                <div class="flex flex-wrap items-center gap-1.5">
                    <button
                        type="button"
                        ${currentPage<=1?'disabled':''}
                        data-page="${currentPage-1}"
                        class="admin-page-btn cursor-pointer rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                        Previous
                    </button>

                    ${
                        start>1
                            ?`
                                <button
                                    type="button"
                                    data-page="1"
                                    class="admin-page-btn h-8 min-w-8 rounded-md border border-slate-300 bg-white px-2 text-xs font-semibold text-slate-600">
                                    1
                                </button>
                                ${
                                    start>2
                                        ?'<span class="px-1 text-xs text-slate-400">...</span>'
                                        :''
                                }
                            `
                            :''
                    }

                    ${pages.map(page=>`
                        <button
                            type="button"
                            data-page="${page}"
                            class="admin-page-btn h-8 min-w-8 rounded-md border px-2 text-xs font-semibold ${
                                page===currentPage
                                    ?'border-indigo-600 bg-indigo-600 text-white'
                                    :'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'
                            }">
                            ${page}
                        </button>
                    `).join('')}

                    ${
                        end<lastPage
                            ?`
                                ${
                                    end<lastPage-1
                                        ?'<span class="px-1 text-xs text-slate-400">...</span>'
                                        :''
                                }

                                <button
                                    type="button"
                                    data-page="${lastPage}"
                                    class="admin-page-btn h-8 min-w-8 rounded-md border border-slate-300 bg-white px-2 text-xs font-semibold text-slate-600">
                                    ${lastPage}
                                </button>
                            `
                            :''
                    }

                    <button
                        type="button"
                        ${currentPage>=lastPage?'disabled':''}
                        data-page="${currentPage+1}"
                        class="admin-page-btn cursor-pointer rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                        Next
                    </button>
                </div>
            </div>
        `;

        element
            .querySelectorAll(
                '.admin-page-btn'
            )
            .forEach(button=>{
                button.addEventListener(
                    'click',
                    ()=>{
                        const page=Number(
                            button.dataset.page
                        );

                        if(
                            page>=1&&
                            page<=lastPage&&
                            typeof config.onPageChange==='function'
                        ){
                            config.onPageChange(
                                page
                            );
                        }
                    }
                );
            });
    },

    /*
    |--------------------------------------------------------------------------
    | Confirmation
    |--------------------------------------------------------------------------
    */

    async confirm(options={}){
        ensureAdminConfirmModal();

        if(typeof options==='string'){
            options={
                message:options
            };
        }

        const config={
            title:'Confirm Action',
            subtitle:
                'Please confirm before continuing.',

            message:
                'Are you sure you want to continue?',

            confirmText:'Confirm',
            cancelText:'Cancel',
            type:'primary',

            ...options
        };

        const modal=
            document.getElementById(
                'adminConfirmModal'
            );

        const title=
            document.getElementById(
                'adminConfirmTitle'
            );

        const subtitle=
            document.getElementById(
                'adminConfirmSubtitle'
            );

        const message=
            document.getElementById(
                'adminConfirmMessage'
            );

        const confirmButton=
            document.getElementById(
                'adminConfirmSubmit'
            );

        const cancelButton=
            document.getElementById(
                'adminConfirmCancel'
            );

        const closeButton=
            document.getElementById(
                'adminConfirmClose'
            );

        const icon=
            document.getElementById(
                'adminConfirmIcon'
            );

        const iconWrap=
            document.getElementById(
                'adminConfirmIconWrap'
            );

        const theme=
            adminConfirmTheme(
                config.type
            );

        title.textContent=
            config.title;

        subtitle.textContent=
            config.subtitle;

        message.textContent=
            config.message;

        confirmButton.textContent=
            config.confirmText;

        cancelButton.textContent=
            config.cancelText;

        icon.className=
            `bi ${theme.icon}`;

        iconWrap.className=
            `flex h-10 w-10 shrink-0 items-center justify-center rounded-md ${theme.iconWrap}`;

        confirmButton.className=
            `cursor-pointer rounded-md px-3.5 py-2 text-xs font-semibold text-white transition ${theme.button}`;

        return new Promise(resolve=>{
            let finished=false;

            const cleanup=()=>{
                confirmButton.removeEventListener(
                    'click',
                    onConfirm
                );

                cancelButton.removeEventListener(
                    'click',
                    onCancel
                );

                closeButton.removeEventListener(
                    'click',
                    onCancel
                );

                modal.removeEventListener(
                    'click',
                    onOverlay
                );

                document.removeEventListener(
                    'keydown',
                    onEscape
                );
            };

            const finish=value=>{
                if(finished)return;

                finished=true;

                cleanup();

                modal.classList.add(
                    'hidden'
                );

                modal.classList.remove(
                    'flex'
                );

                if(
                    !document.querySelector(
                        '.app-modal-overlay.flex'
                    )
                ){
                    document.body.classList.remove(
                        'overflow-hidden'
                    );
                }

                resolve(value);
            };

            const onConfirm=()=>{
                finish(true);
            };

            const onCancel=()=>{
                finish(false);
            };

            const onOverlay=event=>{
                if(event.target===modal){
                    finish(false);
                }
            };

            const onEscape=event=>{
                if(event.key==='Escape'){
                    finish(false);
                }
            };

            confirmButton.addEventListener(
                'click',
                onConfirm
            );

            cancelButton.addEventListener(
                'click',
                onCancel
            );

            closeButton.addEventListener(
                'click',
                onCancel
            );

            modal.addEventListener(
                'click',
                onOverlay
            );

            document.addEventListener(
                'keydown',
                onEscape
            );

            modal.classList.remove(
                'hidden'
            );

            modal.classList.add(
                'flex'
            );

            document.body.classList.add(
                'overflow-hidden'
            );
        });
    },

    /*
    |--------------------------------------------------------------------------
    | Clipboard
    |--------------------------------------------------------------------------
    */

    async copy(value){
        await navigator.clipboard.writeText(
            String(value??'')
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Reset Form
    |--------------------------------------------------------------------------
    */

    resetForm(id){
        const form=this.getForm(id);

        if(form?.reset){
            form.reset();

            this.clearFieldErrors(
                form
            );
        }
    },

    /*
    |--------------------------------------------------------------------------
    | Delete Request
    |--------------------------------------------------------------------------
    */

    async deleteRequest(
        url,
        {
            title='Delete Item?',
            message=
                'This item will be permanently deleted. This action cannot be undone.',

            confirmText='Delete',
            cancelText='Cancel',

            successMessage=
                'Deleted successfully.',

            onSuccess=null,
            onError=null
        }={}
    ){
        const confirmed=
            await this.confirm({
                title,
                message,
                confirmText,
                cancelText,
                type:'danger'
            });

        if(!confirmed){
            return false;
        }

        try{
            const response=
                await api(
                    url,
                    {
                        method:'DELETE'
                    }
                );

            if(
                window.Toast&&
                successMessage
            ){
                Toast.success(
                    successMessage
                );
            }

            if(
                typeof onSuccess==='function'
            ){
                await onSuccess(
                    response
                );
            }

            return response;

        }catch(error){
            if(window.Toast){
                Toast.error(
                    this.extractError(
                        error
                    )
                );

            }else{
                console.error(error);
            }

            if(
                typeof onError==='function'
            ){
                await onError(error);
            }

            return false;
        }
    },

    /*
    |--------------------------------------------------------------------------
    | Generic Request
    |--------------------------------------------------------------------------
    */

    async request(
        url,
        {
            method='POST',
            data={},
            confirmMessage=null,
            confirmation=null,
            successMessage=null,
            onSuccess=null,
            onError=null
        }={}
    ){
        let confirmOptions=null;

        if(confirmation){
            confirmOptions=
                confirmation;

        }else if(confirmMessage){
            confirmOptions={
                message:confirmMessage
            };
        }

        if(confirmOptions){
            const confirmed=
                await this.confirm(
                    confirmOptions
                );

            if(!confirmed){
                return null;
            }
        }

        try{
            const options={
                method
            };

            if(
                data!==null&&
                data!==undefined
            ){
                options.body=
                    data instanceof FormData
                        ?data
                        :JSON.stringify(data);
            }

            const response=
                await api(
                    url,
                    options
                );

            if(
                window.Toast&&
                successMessage
            ){
                Toast.success(
                    successMessage
                );
            }

            if(
                typeof onSuccess==='function'
            ){
                await onSuccess(
                    response
                );
            }

            return response;

        }catch(error){
            if(window.Toast){
                Toast.error(
                    this.extractError(
                        error
                    )
                );

            }else{
                console.error(error);
            }

            if(
                typeof onError==='function'
            ){
                await onError(error);
            }

            return null;
        }
    }
};

/*
|--------------------------------------------------------------------------
| Escape Key
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    event=>{
        if(event.key!=='Escape'){
            return;
        }

        const confirmModal=
            document.getElementById(
                'adminConfirmModal'
            );

        if(
            confirmModal&&
            confirmModal.classList.contains(
                'flex'
            )
        ){
            return;
        }

        AdminUI.closeAllModals();
    }
);

/*
|--------------------------------------------------------------------------
| Overlay Click
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    event=>{
        const overlay=
            event.target.closest(
                '.app-modal-overlay'
            );

        if(
            !overlay||
            event.target!==overlay||
            overlay.id==='adminConfirmModal'
        ){
            return;
        }

        AdminUI.closeModal(
            overlay.id
        );
    }
);