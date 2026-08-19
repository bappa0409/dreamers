window.api=async(url,options={})=>{
    const config={
        credentials:'same-origin',
        ...options,
        headers:{
            Accept:'application/json',
            ...(options.headers||{})
        }
    };

    if(config.body&&!(config.body instanceof FormData)){
        config.headers['Content-Type']='application/json';
    }

    let response;

    try{
        response=await fetch(url,config);
    }catch{
        throw {
            message:'Network error. Please check your connection.',
            data:null
        };
    }

    let data={};

    try{
        data=await response.json();
    }catch{}

    if(response.status===401){
        window.location.href='/login';

        throw {
            response,
            data,
            message:'Your session has expired.'
        };
    }

    if(response.status===403){
        throw {
            response,
            data,
            message:data.message||'You do not have permission to perform this action.'
        };
    }

    if(response.status===419){
        window.location.reload();

        throw {
            response,
            data,
            message:'Session token expired.'
        };
    }

    if(response.status===422){
        throw {
            response,
            data,
            message:data.message||'Validation failed.'
        };
    }

    if(response.status===429){
        throw {
            response,
            data,
            message:'Too many requests. Please try again shortly.'
        };
    }

    if(!response.ok){
        throw {
            response,
            data,
            message:data.message||`Request failed (${response.status}).`
        };
    }

    return data;
};