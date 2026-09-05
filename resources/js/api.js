window.api=async(url,options={})=>{
    const method=String(
        options.method??'GET'
    ).toUpperCase();

    const headers={
        Accept:'application/json',
        ...(options.headers||{})
    };

    const csrfToken=document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if(
        csrfToken&&
        !['GET','HEAD','OPTIONS'].includes(method)
    ){
        headers['X-CSRF-TOKEN']=csrfToken;
    }

    const config={
        credentials:'same-origin',
        ...options,
        method,
        headers
    };

    if(
        config.body&&
        !(config.body instanceof FormData)&&
        !config.headers['Content-Type']
    ){
        config.headers['Content-Type']='application/json';
    }

    let response;

    try{
        response=await fetch(
            url,
            config
        );
    }catch{
        throw{
            message:'Network error. Please check your connection.',
            data:null
        };
    }

    let data={};

    const contentType=
        response.headers.get('content-type')??'';

    try{
        if(
            contentType.includes(
                'application/json'
            )
        ){
            data=await response.json();
        }else{
            const text=await response.text();

            data=text
                ?{message:text}
                :{};
        }
    }catch{}

    if(response.status===401){
        window.location.href='/login';

        throw{
            response,
            data,
            message:'Your session has expired.'
        };
    }

    if(response.status===403){
        throw{
            response,
            data,
            message:
                data.message||
                'You do not have permission to perform this action.'
        };
    }

    if(response.status===419){
        throw{
            response,
            data,
            message:
                'CSRF token mismatch. Please refresh the page and try again.'
        };
    }

    if(response.status===422){
        throw{
            response,
            data,
            message:
                data.message||
                'Validation failed.'
        };
    }

    if(response.status===429){
        throw{
            response,
            data,
            message:
                'Too many requests. Please try again shortly.'
        };
    }

    if(!response.ok){
        throw{
            response,
            data,
            message:
                data.message||
                `Request failed (${response.status}).`
        };
    }

    return data;
};


window.downloadPdf=async(url,filename='receipt.pdf')=>{
    let response;

    try{
        response=await fetch(url,{
            method:'GET',
            credentials:'same-origin',
            headers:{
                Accept:'application/pdf'
            }
        });
    }catch{
        throw new Error('Network error. Please check your connection.');
    }

    if(!response.ok){
        let message=`Request failed (${response.status}).`;

        try{
            const contentType=response.headers.get('content-type')??'';

            if(contentType.includes('application/json')){
                const data=await response.json();
                message=data.message??message;
            }
        }catch{}

        if(response.status===401){
            window.location.href='/login';
        }

        throw new Error(message);
    }

    const blob=await response.blob();
    const disposition=response.headers.get('content-disposition')??'';
    const match=disposition.match(/filename="?([^"]+)"?/i);
    const finalName=match?.[1]??filename;

    const objectUrl=URL.createObjectURL(blob);
    const anchor=document.createElement('a');

    anchor.href=objectUrl;
    anchor.download=finalName;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();

    setTimeout(
        ()=>URL.revokeObjectURL(objectUrl),
        1000
    );
};