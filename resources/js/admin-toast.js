window.Toast={
    getContainer(){
        let container=document.getElementById('globalToastContainer');

        if(container)return container;

        container=document.createElement('div');
        container.id='globalToastContainer';
        container.className='fixed right-4 top-4 z-[9999] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2';

        document.body.appendChild(container);

        return container;
    },

    show(message,type='success',duration=3500){
        const styles={
            success:'border-emerald-200 bg-emerald-50 text-emerald-700',
            error:'border-red-200 bg-red-50 text-red-700',
            warning:'border-amber-200 bg-amber-50 text-amber-700',
            info:'border-sky-200 bg-sky-50 text-sky-700'
        };

        const icons={
            success:'bi-check-circle',
            error:'bi-x-circle',
            warning:'bi-exclamation-triangle',
            info:'bi-info-circle'
        };

        const toast=document.createElement('div');

        toast.className=`
            flex items-start gap-2 rounded-md border px-4 py-3
            text-sm shadow-lg transition
            ${styles[type]??styles.info}
        `;

        toast.innerHTML=`
            <i class="bi ${icons[type]??icons.info} mt-0.5 shrink-0"></i>
            <div class="min-w-0 flex-1">${AdminUI.escapeHtml(message)}</div>
            <button type="button" class="shrink-0 opacity-60 hover:opacity-100">
                <i class="bi bi-x-lg text-xs 2xl:text-sm"></i>
            </button>
        `;

        toast.querySelector('button').addEventListener('click',()=>{
            toast.remove();
        });

        this.getContainer().appendChild(toast);

        setTimeout(()=>{
            toast.remove();
        },duration);
    },

    success(message){
        this.show(message,'success');
    },

    error(message){
        this.show(message,'error');
    },

    warning(message){
        this.show(message,'warning');
    },

    info(message){
        this.show(message,'info');
    }
};