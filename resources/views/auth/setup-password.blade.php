<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Set Password | Dreamers Association</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body class="min-h-screen bg-slate-100">

<div class="flex min-h-screen items-center justify-center p-4">

    <div class="w-full max-w-md overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl">

        <div class="bg-gradient-to-r from-[#0d3b66] to-[#145da0] px-6 py-6 text-center text-white">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-white/10">
                <i class="bi bi-shield-lock text-xl"></i>
            </div>

            <h1 class="mt-3 text-xl font-bold">
                Dreamers Association
            </h1>

            <p class="mt-1 text-xs text-sky-100">
                Secure account activation
            </p>
        </div>

        <div class="p-6">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-800">
                    Create Your Password
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Welcome {{ $user->name }}. Set a secure password for your account.
                </p>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.setup.submit') }}" class="space-y-4">

                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Password
                    </label>

                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            minlength="8"
                            required
                            autocomplete="new-password"
                            class="app-input pr-10"
                        >

                        <button
                            type="button"
                            onclick="togglePassword('password',this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <p class="mt-1 text-xs text-slate-400">
                        Minimum 8 characters.
                    </p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Confirm Password
                    </label>

                    <div class="relative">
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            minlength="8"
                            required
                            autocomplete="new-password"
                            class="app-input pr-10"
                        >

                        <button
                            type="button"
                            onclick="togglePassword('password_confirmation',this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white  hover:bg-indigo-700"
                >
                    <i class="bi bi-check2-circle mr-1"></i>
                    Create Password
                </button>

            </form>

        </div>

    </div>

</div>

<script>
function togglePassword(id,button){
    const input=document.getElementById(id);
    const icon=button.querySelector('i');

    if(input.type==='password'){
        input.type='text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
        return;
    }

    input.type='password';
    icon.classList.remove('bi-eye-slash');
    icon.classList.add('bi-eye');
}
</script>

</body>
</html>