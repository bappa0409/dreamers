<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', setting('organization_name', 'Dreamers Association'))
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

     @if(setting('site_favicon'))
        <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', system-ui, sans-serif;
        }

        *:focus,
        *:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }

        /* =========================================
           BRAND PANEL
        ========================================= */

        .brand-panel {
            background:
                radial-gradient(circle at 15% 20%,
                    rgba(16, 185, 129, 0.32),
                    transparent 35%),

                radial-gradient(circle at 85% 80%,
                    rgba(20, 184, 166, 0.22),
                    transparent 35%),

                linear-gradient(120deg,
                    #022c22 0%,
                    #064e3b 30%,
                    #047857 60%,
                    #059669 82%,
                    #064e3b 100%);

            background-size: 200% 200%;
            animation: panelShift 12s ease-in-out infinite;
        }

        @keyframes panelShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .typewriter-cursor {
            display: inline-block;
            color: rgba(255, 255, 255, 0.75);
            animation: blink 0.9s step-end infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .floating-glow {
            animation: floatingGlow 6s ease-in-out infinite;
        }

        @keyframes floatingGlow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-18px);
            }
        }

        .login-card {
            animation: loginAppear 0.7s ease-out both;
        }

        @keyframes loginAppear {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-input {
            transition:
                border-color 0.2s ease,
                background-color 0.2s ease,
                transform 0.2s ease;
        }

        .login-input:focus {
            border-color: #059669 !important;
            background-color: #ffffff;
            transform: translateY(-1px);
        }

        .login-button {
            position: relative;
            overflow: hidden;
        }

        .login-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 80%;
            height: 100%;

            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.25),
                    transparent);

            transform: skewX(-20deg);
            transition: left 0.6s ease;
        }

        .login-button:hover::before {
            left: 140%;
        }

        .dot-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.13) 1px, transparent 1px);
            background-size: 22px 22px;
        }

        .mobile-logo {
            box-shadow: 0 15px 35px rgba(5, 150, 105, 0.20);
        }

        .message-box {
            animation: messageAppear 0.3s ease-out;
        }

        @keyframes messageAppear {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-pulse {
            animation: iconPulse 2.4s ease-in-out infinite;
        }

        @keyframes iconPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.08);
            }
        }

        .strength-bar {
            transition: width 0.25s ease, background-color 0.25s ease;
        }

        /* =========================================
        AUTOFILL STYLE FIX
        ========================================= */

        .login-input:-webkit-autofill,
        .login-input:-webkit-autofill:hover,
        .login-input:-webkit-autofill:focus,
        .login-input:-webkit-autofill:active {
            /* Force our own font instead of the browser default */
            font-family: 'Poppins', system-ui, sans-serif !important;
            font-size: 0.875rem !important;
            -webkit-text-fill-color: #0f172a !important;

            /* Kill the yellow/blue autofill background using a huge inset shadow trick */
            box-shadow: 0 0 0 1000px #f8fafc inset !important;
            -webkit-box-shadow: 0 0 0 1000px #f8fafc inset !important;

            transition: background-color 9999s ease-in-out 0s;
        }

        .login-input:-webkit-autofill:focus {
            box-shadow: 0 0 0 1000px #ffffff inset, 0 0 0 2px rgba(5, 150, 105, 0.10) !important;
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset, 0 0 0 2px rgba(5, 150, 105, 0.10) !important;
        }

        /* Firefox */
        .login-input:autofill {
            font-family: 'Poppins', system-ui, sans-serif !important;
            font-size: 0.875rem !important;
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen bg-slate-50">
    <div class="min-h-screen flex">

        <!-- =====================================================
             LEFT BRAND SECTION (shared)
        ====================================================== -->

        <section class="brand-panel relative hidden lg:flex lg:w-1/2 overflow-hidden text-white">

            <div class="dot-pattern absolute inset-0 opacity-40"></div>

            <div class="floating-glow absolute -top-24 -left-24 w-80 h-80 bg-emerald-400/20 rounded-full blur-3xl">
            </div>

            <div
                class="floating-glow absolute bottom-[-100px] right-[-80px] w-96 h-96 bg-teal-400/10 rounded-full blur-3xl">
            </div>

            <div class="relative z-10 flex flex-col justify-between w-full p-12 xl:p-16">

                <div>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-md bg-white/95 flex items-center justify-center shadow-xl shadow-black/10">
                            <div
                                class="w-9 h-9 rounded-md bg-gradient-to-br from-emerald-600 to-teal-500 flex items-center justify-center text-white font-extrabold text-xl">
                                D
                            </div>
                        </div>

                        <div>
                            <h1 class="text-xl font-bold tracking-wide">
                                Dreamers Association
                            </h1>

                            <p class="text-emerald-100 text-xs mt-1">
                                Together We Dream. Together We Grow.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="max-w-xl">
                    <p class="text-emerald-200 uppercase tracking-[0.3em] text-xs font-semibold mb-5">
                        @yield('brand-eyebrow', 'DREAMERS ASSOCIATION')
                    </p>

                    <h2 class="text-4xl xl:text-5xl font-extrabold leading-tight tracking-tight">
                        @yield('brand-heading')
                    </h2>

                    <div class="mt-5 text-lg text-emerald-50/90">
                        <span id="typewriterText"></span>
                        <span class="typewriter-cursor">|</span>
                    </div>

                    <div class="mt-5 grid @yield('brand-features-cols', 'grid-cols-2') gap-4">
                        @yield('brand-features')
                    </div>
                </div>

                <div class="text-xs text-emerald-100/60">© {{ date('Y') }} Dreamers Association
                    <span class="mx-2">•</span> Secure Member Portal
                </div>
            </div>
        </section>

        <!-- =====================================================
             RIGHT FORM SECTION (per-page content)
        ====================================================== -->

        <section class="w-full lg:w-1/2 bg-white flex items-center justify-center px-6 py-10 sm:px-10 lg:px-14">
            <div class="login-card w-full max-w-md">

                <div class="lg:hidden text-center mb-10">
                    <div
                        class="mobile-logo mx-auto w-16 h-16 rounded-md bg-gradient-to-br from-emerald-600 to-teal-500 flex items-center justify-center text-white text-2xl font-extrabold">
                        D
                    </div>

                    <h1 class="mt-4 text-xl font-bold text-slate-900">
                        Dreamers Association
                    </h1>

                    <p class="text-sm text-slate-500">
                        Together We Dream. Together We Grow.
                    </p>
                </div>

                @yield('content')

            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const phrases = [
                @yield('phrases')
            ];

            const typewriterElement = document.getElementById('typewriterText');
            let phraseIndex = 0;
            let characterIndex = 0;
            let deleting = false;

            function typeWriter() {
                if (!phrases.length) return;

                const currentPhrase = phrases[phraseIndex];

                if (!deleting) {
                    typewriterElement.textContent = currentPhrase.substring(0, characterIndex + 1);
                    characterIndex++;

                    if (characterIndex === currentPhrase.length) {
                        deleting = true;
                        setTimeout(typeWriter, 2200);
                        return;
                    }

                    setTimeout(typeWriter, 45);
                } else {
                    typewriterElement.textContent = currentPhrase.substring(0, characterIndex - 1);
                    characterIndex--;

                    if (characterIndex === 0) {
                        deleting = false;
                        phraseIndex = (phraseIndex + 1) % phrases.length;
                        setTimeout(typeWriter, 400);
                        return;
                    }

                    setTimeout(typeWriter, 25);
                }
            }

            if (typewriterElement) {
                typeWriter();
            }
        });

        /* Shared password show/hide toggle, used by any @section('content') */
        function togglePassword(id, button) {
            const input = document.getElementById(id);
            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';

            const icon = button.querySelector('.eye-icon');

            button.setAttribute(
                'aria-label',
                isPassword ? 'Hide password' : 'Show password'
            );
        }
    </script>

    @stack('scripts')

</body>

</html>