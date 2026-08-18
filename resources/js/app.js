import './bootstrap';


/*
|--------------------------------------------------------------------------
| Get Laravel CSRF Token
|--------------------------------------------------------------------------
*/

function getCsrfToken() {

    // Laravel meta tag
    const meta = document.querySelector(
        'meta[name="csrf-token"]'
    );

    if (meta && meta.getAttribute('content')) {
        return meta.getAttribute('content');
    }

    // Laravel cookie fallback
    const match = document.cookie.match(
        /(?:^|;\s*)XSRF-TOKEN=([^;]+)/
    );

    if (match) {
        return decodeURIComponent(match[1]);
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| API Helper
|--------------------------------------------------------------------------
*/

window.api = async function (url, options = {}) {

    const token = localStorage.getItem('auth_token');

    const headers = {
        'Accept': 'application/json',

        ...options.headers,
    };


    /*
    |--------------------------------------------------------------------------
    | Content-Type
    |--------------------------------------------------------------------------
    */

    if (
        options.body &&
        !(options.body instanceof FormData) &&
        !headers['Content-Type']
    ) {

        headers['Content-Type'] = 'application/json';

    }


    /*
    |--------------------------------------------------------------------------
    | Laravel CSRF
    |--------------------------------------------------------------------------
    |
    | Important for web/session POST, PUT, PATCH and DELETE requests.
    |
    */

    const csrfToken = getCsrfToken();

    if (csrfToken) {

        headers['X-CSRF-TOKEN'] = csrfToken;

    }


    /*
    |--------------------------------------------------------------------------
    | Bearer Token
    |--------------------------------------------------------------------------
    |
    | Flutter / API clients can still use auth_token.
    | Admin web can use Laravel session cookie.
    |
    */

    if (token) {

        headers['Authorization'] =
            `Bearer ${token}`;

    }


    /*
    |--------------------------------------------------------------------------
    | API Request
    |--------------------------------------------------------------------------
    */

    const response = await fetch(url, {

        ...options,

        headers,

        /*
        | Laravel session / Sanctum cookie
        */
        credentials: 'same-origin',

    });


    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

    let data = null;

    try {

        data = await response.json();

    } catch (error) {

        data = null;

    }


    /*
    |--------------------------------------------------------------------------
    | Error
    |--------------------------------------------------------------------------
    */

    if (!response.ok) {

        const error = new Error(
            data?.message ||
            data?.error ||
            'Request failed.'
        );

        error.status = response.status;

        error.data = data;

        throw error;

    }


    return data;

};


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

window.logout = async function () {

    try {

        await window.api('/api/auth/logout', {

            method: 'POST',

            body: JSON.stringify({}),

        });

    } catch (error) {

        console.error(
            'Logout error:',
            error
        );

    }


    localStorage.removeItem('auth_token');

    localStorage.removeItem('auth_user');


    window.location.href = '/login';

};