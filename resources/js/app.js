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

    document.querySelectorAll('form').forEach(form=>{
        if(
            form.querySelector('[data-field-error]')&&
            window.AdminUI
        ){
            AdminUI.bindFieldValidation(form);
        }
    });
});

window.initDatePickers=initDatePickers;