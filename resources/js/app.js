import './bootstrap';
import './api';
import './admin-ui';
import './admin-toast';

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';

window.logout=async()=>{
    try{
        await window.api('/api/auth/logout',{
            method:'POST',
            body:JSON.stringify({})
        });
    }catch(error){
        console.error('Logout error:',error);
    }

    localStorage.removeItem('auth_token');
    localStorage.removeItem('auth_user');
    window.location.href='/login';
};

function initDatePickers(){
    document.querySelectorAll('.js-date-picker').forEach(element=>{
        if(element._flatpickr)return;

        flatpickr(element,{
            dateFormat:'Y-m-d',
            allowInput:true,
            disableMobile:true,
            monthSelectorType:'dropdown'
        });
    });

    document.querySelectorAll('.js-date-range').forEach(element=>{
        if(element._flatpickr)return;

        flatpickr(element,{
            mode:'range',
            dateFormat:'Y-m-d',
            allowInput:true,
            disableMobile:true,
            monthSelectorType:'dropdown'
        });
    });

    document.querySelectorAll('.js-datetime-picker').forEach(element=>{
        if(element._flatpickr)return;

        flatpickr(element,{
            enableTime:true,
            time_24hr:true,
            dateFormat:'Y-m-d H:i',
            allowInput:true,
            disableMobile:true,
            monthSelectorType:'dropdown'
        });
    });
}

document.addEventListener('DOMContentLoaded',()=>{
    initDatePickers();

    if(!window.AdminUI)return;

    document.querySelectorAll('form[data-js-validation="1"]').forEach(form=>{
        AdminUI.bindFieldValidation(form);
    });

    const clearInvalidField=event=>{
        const field=event.target;

        if(
            field?.matches?.('input,select,textarea')&&
            field.classList.contains('is-invalid')
        ){
            AdminUI.clearFieldError(field);
        }
    };

    document.addEventListener('input',clearInvalidField,true);
    document.addEventListener('change',clearInvalidField,true);

    document.addEventListener('submit',event=>{
        const form=event.target?.closest?.('form');

        if(
            !form||
            form.dataset.jsValidation!=='1'||
            form.dataset.skipValidation==='1'
        ){
            return;
        }

        if(!AdminUI.validateForm(form)){
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    },true);
});

window.initDatePickers=initDatePickers;