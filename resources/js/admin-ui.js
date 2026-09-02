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
    | Mobile Number
    |--------------------------------------------------------------------------
    */

    isValidMobile(value){
        return /^01[3-9]\d{8}$/.test(
            String(value??'').trim()
        );
    },

    /*
    |--------------------------------------------------------------------------
    | Field Validation
    |--------------------------------------------------------------------------
    */

    getForm(form){
        if(typeof form==='string'){
            return document.getElementById(form);
        }

        return form;
    },

    getField(field){
        if(typeof field==='string'){
            return document.getElementById(field);
        }

        return field;
    },

    ensureFieldId(input){
        if(!input)return '';
        if(input.id)return input.id;

        const base=String(input.name||'field')
            .replace(/\[\]/g,'')
            .replace(/[^a-zA-Z0-9_-]/g,'_')||'field';

        let id=base;
        let index=1;

        while(document.getElementById(id)){
            id=`${base}_${index++}`;
        }

        input.id=id;
        return id;
    },

    fieldLabel(input){
        input=this.getField(input);
        if(!input)return 'This field';

        const form=input.closest('form');
        const id=this.ensureFieldId(input);
        let label=null;

        if(form&&id){
            label=form.querySelector(
                `label[for="${CSS.escape(id)}"]`
            );
        }

        if(!label){
            label=input.closest('label');
        }

        if(!label){
            let parent=input.parentElement;
            let depth=0;

            while(parent&&parent!==form&&depth<3){
                label=Array.from(
                    parent.children||[]
                ).find(child=>
                    child.tagName==='LABEL'&&
                    child.querySelector('input,select,textarea')!==input
                )||null;

                if(label)break;

                parent=parent.parentElement;
                depth++;
            }
        }

        const text=String(label?.textContent||input.name||'This field')
            .replace(/\*/g,'')
            .replace(/\s+/g,' ')
            .trim();

        return text||'This field';
    },

    requiredConditionMatches(input){
        const rule=String(input?.dataset?.requiredWhen||'').trim();
        if(!rule)return false;

        return rule.split(';').every(part=>{
            const [rawField,...rawValue]=part.split('=');
            const fieldId=String(rawField||'').trim();
            const expected=rawValue.join('=').trim();

            if(!fieldId)return true;

            const form=input.closest('form');
            const field=document.getElementById(fieldId)||
                form?.querySelector(`[name="${CSS.escape(fieldId)}"]`);

            if(!field)return false;

            if(field.type==='checkbox'){
                return String(field.checked)===expected||
                    (expected==='1'&&field.checked)||
                    (expected==='0'&&!field.checked);
            }

            return String(field.value??'')===expected;
        });
    },

    isVisuallyRequired(input){
        input=this.getField(input);
        if(!input||input.disabled)return false;

        if(this.requiredConditionMatches(input))return true;

        if(
            input.required||
            input.getAttribute('aria-required')==='true'||
            input.dataset.required==='true'||
            input.dataset.required==='1'
        ){
            return true;
        }

        const form=input.closest('form');
        const id=this.ensureFieldId(input);
        let label=null;

        if(form&&id){
            label=form.querySelector(
                `label[for="${CSS.escape(id)}"]`
            );
        }

        if(!label){
            label=input.closest('label');
        }

        if(!label){
            let parent=input.parentElement;
            let depth=0;

            while(parent&&parent!==form&&depth<3){
                label=Array.from(
                    parent.children||[]
                ).find(child=>child.tagName==='LABEL')||null;

                if(label)break;

                parent=parent.parentElement;
                depth++;
            }
        }

        if(!label)return false;

        return Boolean(
            label.querySelector(
                '.text-red-500,.text-red-600,[data-required-marker]'
            )||
            label.textContent?.includes('*')
        );
    },

    ensureFieldError(input){
        input=this.getField(input);
        if(!input)return null;

        const form=input.closest('form');
        const id=this.ensureFieldId(input);

        if(!form||!id)return null;

        let error=form.querySelector(
            `[data-field-error="${CSS.escape(id)}"]`
        );

        if(error)return error;

        error=document.createElement('p');
        error.dataset.fieldError=id;
        error.className='mt-1 hidden text-xs text-red-600';
        error.setAttribute('role','alert');
        error.setAttribute('aria-live','polite');

        const describedBy=`${id}-error`;
        error.id=describedBy;

        const existing=input.getAttribute('aria-describedby');
        input.setAttribute(
            'aria-describedby',
            existing?`${existing} ${describedBy}`:describedBy
        );

        input.insertAdjacentElement('afterend',error);
        return error;
    },

    clearFieldError(field){
        const input=this.getField(field);
        if(!input)return;

        input.classList.remove(
            'is-invalid',
            'is-valid',
            'border-red-400',
            'border-red-500',
            'focus:border-red-500',
            'focus:ring-red-100'
        );

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

        const error=this.ensureFieldError(input);

        if(error){
            error.textContent=String(message??'Invalid value.');
            error.classList.remove('hidden');
        }

        return true;
    },

    showFieldError(field,message){
        return this.setFieldError(field,message);
    },

    clearFieldErrors(form){
        form=this.getForm(form);
        if(!form)return;

        form.querySelectorAll('.is-invalid,.is-valid')
            .forEach(input=>{
                input.classList.remove('is-invalid','is-valid');
                input.removeAttribute('aria-invalid');
            });

        form.querySelectorAll('[data-field-error]')
            .forEach(error=>{
                error.textContent='';
                error.classList.add('hidden');
            });
    },

    validationMessage(input,type='required'){
        const label=this.fieldLabel(input);

        const custom={
            required:input.dataset.validationRequiredMessage,
            email:input.dataset.validationEmailMessage,
            number:input.dataset.validationNumberMessage,
            min:input.dataset.validationMinMessage,
            max:input.dataset.validationMaxMessage,
            minlength:input.dataset.validationMinlengthMessage,
            maxlength:input.dataset.validationMaxlengthMessage,
            pattern:input.dataset.validationPatternMessage,
            match:input.dataset.validationMatchMessage,
            mobile:input.dataset.validationMobileMessage
        }[type];

        if(custom)return custom;

        if(type==='required'){
            if(input.type==='checkbox'){
                return `Please confirm ${label.toLowerCase()}.`;
            }

            if(input.tagName==='SELECT'||input.type==='file'){
                return `Please select ${label.toLowerCase()}.`;
            }

            return `${label} is required.`;
        }

        if(type==='email')return 'Please enter a valid email address.';
        if(type==='mobile')return 'Please enter a valid 11 digit mobile number (e.g. 01XXXXXXXXX).';
        if(type==='number')return `${label} must be a valid number.`;
        if(type==='min')return `${label} must be at least ${input.min}.`;
        if(type==='max')return `${label} must not exceed ${input.max}.`;
        if(type==='minlength')return `${label} must be at least ${input.minLength} characters.`;
        if(type==='maxlength')return `${label} must not exceed ${input.maxLength} characters.`;
        if(type==='pattern')return `${label} has an invalid format.`;
        if(type==='match')return `${label} does not match.`;

        return `${label} is invalid.`;
    },

    isFieldActive(input){
        input=this.getField(input);
        if(!input||input.disabled||input.type==='hidden')return false;

        if(
            input.dataset.validationIgnore==='1'||
            input.dataset.validationIgnore==='true'||
            input.hidden||
            input.closest('[hidden]')||
            input.parentElement?.closest?.('.hidden')
        ){
            return false;
        }

        return true;
    },

    fieldValue(input){
        if(input.type==='checkbox'){
            return input.checked?'1':'';
        }

        if(input.type==='radio'){
            const form=input.closest('form');
            const name=input.name;

            if(!form||!name){
                return input.checked?String(input.value??'1'):'';
            }

            const checked=form.querySelector(
                `input[type="radio"][name="${CSS.escape(name)}"]:checked`
            );

            return checked?String(checked.value??'1'):'';
        }

        if(input.type==='file'){
            return input.files?.length?'1':'';
        }

        return String(input.value??'').trim();
    },

    validateField(input,{required=false,message=null}={}){
        input=this.getField(input);

        if(
            !this.isFieldActive(input)||
            input.type==='submit'||
            input.type==='button'||
            input.type==='reset'
        ){
            return true;
        }

        this.clearFieldError(input);

        const value=this.fieldValue(input);
        const mustFill=required||this.isVisuallyRequired(input);

        if(mustFill&&value===''){
            this.setFieldError(
                input,
                message||this.validationMessage(input,'required')
            );
            return false;
        }

        if(value==='')return true;

        if(input.type==='email'){
            const email=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!email.test(value)){
                this.setFieldError(input,this.validationMessage(input,'email'));
                return false;
            }
        }

        if(
            input.type==='tel'||
            input.dataset.mobile==='true'
        ){
            if(!this.isValidMobile(value)){
                this.setFieldError(input,this.validationMessage(input,'mobile'));
                return false;
            }
        }

        if(input.type==='number'||input.dataset.numeric==='true'){
            const number=Number(value);

            if(!Number.isFinite(number)){
                this.setFieldError(input,this.validationMessage(input,'number'));
                return false;
            }

            if(input.min!==''&&number<Number(input.min)){
                this.setFieldError(input,this.validationMessage(input,'min'));
                return false;
            }

            if(input.max!==''&&number>Number(input.max)){
                this.setFieldError(input,this.validationMessage(input,'max'));
                return false;
            }
        }

        if(input.minLength>0&&value.length<input.minLength){
            this.setFieldError(input,this.validationMessage(input,'minlength'));
            return false;
        }

        if(input.maxLength>0&&value.length>input.maxLength){
            this.setFieldError(input,this.validationMessage(input,'maxlength'));
            return false;
        }

        if(input.pattern){
            try{
                const pattern=new RegExp(`^(?:${input.pattern})$`);

                if(!pattern.test(value)){
                    this.setFieldError(input,this.validationMessage(input,'pattern'));
                    return false;
                }
            }catch(error){
                console.warn('Invalid validation pattern:',input.pattern,error);
            }
        }

        if(input.type==='url'){
            try{
                new URL(value);
            }catch(error){
                this.setFieldError(input,`${this.fieldLabel(input)} must be a valid URL.`);
                return false;
            }
        }

        if(input.type==='file'&&input.files?.length){
            const accept=String(input.accept||'')
                .split(',')
                .map(item=>item.trim().toLowerCase())
                .filter(Boolean);

            if(accept.length){
                const invalid=Array.from(input.files).some(file=>{
                    const name=String(file.name||'').toLowerCase();
                    const type=String(file.type||'').toLowerCase();

                    return !accept.some(rule=>{
                        if(rule.startsWith('.'))return name.endsWith(rule);
                        if(rule.endsWith('/*'))return type.startsWith(rule.slice(0,-1));
                        return type===rule;
                    });
                });

                if(invalid){
                    this.setFieldError(
                        input,
                        input.dataset.validationFileMessage||'Please select an allowed file type.'
                    );
                    return false;
                }
            }

            const maxSize=Number(input.dataset.maxSize||0);
            if(maxSize>0&&Array.from(input.files).some(file=>file.size>maxSize)){
                this.setFieldError(
                    input,
                    input.dataset.validationFileSizeMessage||'Selected file is too large.'
                );
                return false;
            }
        }

        let matchTarget=input.dataset.match||'';

        if(!matchTarget){
            const key=String(input.id||input.name||'');
            const suffix=key.match(/^(.*?)(?:_confirmation|Confirmation|_confirm|Confirm)$/);
            if(suffix?.[1])matchTarget=suffix[1];
        }

        if(matchTarget){
            const form=input.closest('form');
            const other=document.getElementById(matchTarget)||
                form?.querySelector(`[name="${CSS.escape(matchTarget)}"]`);

            if(other&&value!==String(other.value??'')){
                this.setFieldError(input,this.validationMessage(input,'match'));
                return false;
            }
        }

        const compareDate=(targetId,mode)=>{
            if(!targetId)return true;

            const other=document.getElementById(targetId);
            if(!other||!String(other.value??'').trim())return true;

            const current=new Date(value);
            const target=new Date(String(other.value).trim());

            if(Number.isNaN(current.getTime())||Number.isNaN(target.getTime()))return true;

            if(mode==='after')return current>target;
            if(mode==='onOrAfter')return current>=target;
            if(mode==='before')return current<target;
            if(mode==='onOrBefore')return current<=target;
            return true;
        };

        const comparisons=[
            [input.dataset.after,'after',`must be after ${this.fieldLabel(document.getElementById(input.dataset.after))}`],
            [input.dataset.onOrAfter,'onOrAfter',`must be on or after ${this.fieldLabel(document.getElementById(input.dataset.onOrAfter))}`],
            [input.dataset.before,'before',`must be before ${this.fieldLabel(document.getElementById(input.dataset.before))}`],
            [input.dataset.onOrBefore,'onOrBefore',`must be on or before ${this.fieldLabel(document.getElementById(input.dataset.onOrBefore))}`]
        ];

        for(const [targetId,mode,message] of comparisons){
            if(targetId&&!compareDate(targetId,mode)){
                this.setFieldError(
                    input,
                    input.dataset.validationCompareMessage||`${this.fieldLabel(input)} ${message}.`
                );
                return false;
            }
        }

        return true;
    },

    validateForm(form,messages={}){
        form=this.getForm(form);
        if(!form)return true;

        this.clearFieldErrors(form);

        const controls=Array.from(
            form.querySelectorAll('input,select,textarea')
        );

        let valid=true;
        let firstInvalid=null;
        const seenRadioGroups=new Set();

        controls.forEach(input=>{
            if(
                input.type==='radio'&&
                input.name
            ){
                if(seenRadioGroups.has(input.name))return;
                seenRadioGroups.add(input.name);
            }

            const id=this.ensureFieldId(input);
            const hasExplicitMessage=Boolean(messages[id]);
            const shouldValidate=
                hasExplicitMessage||
                this.isVisuallyRequired(input)||
                input.type==='email'||
                input.type==='url'||
                input.type==='number'||
                input.type==='file'||
                input.type==='tel'||
                input.dataset.mobile==='true'||
                Boolean(input.pattern)||
                input.minLength>0||
                input.maxLength>0||
                Boolean(input.dataset.match)||
                Boolean(input.dataset.after)||
                Boolean(input.dataset.onOrAfter)||
                Boolean(input.dataset.before)||
                Boolean(input.dataset.onOrBefore)||
                /(?:_confirmation|Confirmation|_confirm|Confirm)$/.test(String(input.id||input.name||''));

            if(!shouldValidate)return;

            const ok=this.validateField(input,{
                required:hasExplicitMessage||this.isVisuallyRequired(input),
                message:messages[id]||null
            });

            if(!ok){
                valid=false;
                if(!firstInvalid)firstInvalid=input;
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

        return valid;
    },

    showValidationErrors(form,error,fieldMap={}){
        form=this.getForm(form);
        if(!form)return false;

        const errors=error?.data?.errors;
        if(!errors||typeof errors!=='object')return false;

        this.clearFieldErrors(form);

        let firstInvalid=null;
        let handled=false;

        Object.entries(errors).forEach(([field,messages])=>{
            const mapped=fieldMap[field]||field;
            const camel=String(field).replace(
                /_([a-z])/g,
                (_,char)=>char.toUpperCase()
            );

            const input=
                document.getElementById(mapped)||
                document.getElementById(camel)||
                form.querySelector(`[name="${CSS.escape(field)}"]`)||
                form.querySelector(`[name="${CSS.escape(`${field}[]`)}"]`);

            if(!input)return;

            const message=Array.isArray(messages)?messages[0]:messages;
            this.setFieldError(input,message);
            handled=true;

            if(!firstInvalid)firstInvalid=input;
        });

        if(firstInvalid){
            requestAnimationFrame(()=>{
                firstInvalid.scrollIntoView({
                    behavior:'smooth',
                    block:'center'
                });
                firstInvalid.focus?.({preventScroll:true});
            });
        }

        return handled;
    },

    bindFieldValidation(form){
        form=this.getForm(form);

        if(!form||form.dataset.validationBound==='1')return;

        form.dataset.validationBound='1';
        form.noValidate=true;
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

            pending_approval:
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
| Mobile Number Input Filter
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'input',
    event=>{
        const input=event.target;

        if(
            !input?.matches?.('input')||
            (
                input.type!=='tel'&&
                input.dataset?.mobile!=='true'
            )
        ){
            return;
        }

        const digits=input.value
            .replace(/\D/g,'')
            .slice(0,11);

        if(digits!==input.value){
            input.value=digits;
        }
    }
);

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