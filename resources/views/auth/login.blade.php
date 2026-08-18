<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Dreamers Association</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

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
           BRAND SHIMMER
        ========================================= */

        .brand-shimmer {
            background: linear-gradient(
                90deg,
                #022c22,
                #047857,
                #10b981,
                #064e3b,
                #14b8a6,
                #047857,
                #022c22
            );

            background-size: 300% auto;

            -webkit-background-clip: text;
            background-clip: text;

            color: transparent;

            animation: brandShimmer 4s linear infinite;
        }

        @keyframes brandShimmer {
            0% {
                background-position: 0% center;
            }

            100% {
                background-position: 300% center;
            }
        }


        /* =========================================
           LEFT BRAND PANEL
        ========================================= */

        .brand-panel {
            background:
                radial-gradient(
                    circle at 15% 20%,
                    rgba(16, 185, 129, 0.32),
                    transparent 35%
                ),

                radial-gradient(
                    circle at 85% 80%,
                    rgba(20, 184, 166, 0.22),
                    transparent 35%
                ),

                linear-gradient(
                    120deg,
                    #022c22 0%,
                    #064e3b 30%,
                    #047857 60%,
                    #059669 82%,
                    #064e3b 100%
                );

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


        /* =========================================
           TYPEWRITER CURSOR
        ========================================= */

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


        /* =========================================
           FLOATING GLOW
        ========================================= */

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


        /* =========================================
           LOGIN CARD ANIMATION
        ========================================= */

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


        /* =========================================
           INPUT EFFECT
        ========================================= */

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


        /* =========================================
           LOGIN BUTTON
        ========================================= */

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

            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.25),
                transparent
            );

            transform: skewX(-20deg);

            transition: left 0.6s ease;
        }

        .login-button:hover::before {
            left: 140%;
        }


        /* =========================================
           DOT PATTERN
        ========================================= */

        .dot-pattern {
            background-image: radial-gradient(
                rgba(255, 255, 255, 0.13) 1px,
                transparent 1px
            );

            background-size: 22px 22px;
        }


        /* =========================================
           MOBILE LOGO
        ========================================= */

        .mobile-logo {
            box-shadow:
                0 15px 35px rgba(5, 150, 105, 0.20);
        }


        /* =========================================
           ERROR / SUCCESS ANIMATION
        ========================================= */

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
    </style>
</head>


<body class="min-h-screen bg-slate-50">


    <div class="min-h-screen flex">


        <!-- =====================================================
             LEFT BRAND SECTION
        ====================================================== -->

        <section
            class="brand-panel relative hidden lg:flex lg:w-1/2 overflow-hidden text-white"
        >

            <!-- Dot Pattern -->
            <div class="dot-pattern absolute inset-0 opacity-40"></div>


            <!-- Glow 1 -->
            <div
                class="floating-glow absolute -top-24 -left-24 w-80 h-80
                       bg-emerald-400/20 rounded-full blur-3xl"
            ></div>


            <!-- Glow 2 -->
            <div
                class="floating-glow absolute bottom-[-100px] right-[-80px]
                       w-96 h-96 bg-teal-400/10 rounded-full blur-3xl"
            ></div>


            <!-- Content -->
            <div
                class="relative z-10 flex flex-col justify-between
                       w-full p-12 xl:p-16"
            >


                <!-- =============================================
                     LOGO + BRAND
                ============================================== -->

                <div>

                    <div class="flex items-center gap-4">

                        <!-- Logo -->
                        <div
                            class="w-14 h-14 rounded-md
                                   bg-white/95
                                   flex items-center justify-center
                                   shadow-xl shadow-black/10"
                        >

                            <div
                                class="w-9 h-9 rounded-md
                                       bg-gradient-to-br
                                       from-emerald-600 to-teal-500
                                       flex items-center justify-center
                                       text-white font-extrabold text-xl"
                            >
                                D
                            </div>

                        </div>


                        <!-- Brand Name -->
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


                <!-- =============================================
                     MAIN MESSAGE
                ============================================== -->

                <div class="max-w-xl">

                    <p
                        class="text-emerald-200 uppercase
                               tracking-[0.3em] text-xs
                               font-semibold mb-5"
                    >
                        DREAMERS ASSOCIATION
                    </p>


                    <h2
                        class="text-4xl xl:text-5xl
                               font-extrabold leading-tight
                               tracking-tight"
                    >
                        Build dreams.
                        <br>

                        <span class="text-emerald-200">
                            Build the future.
                        </span>
                    </h2>


                    <!-- Typewriter Text -->
                    <div class="mt-7 text-lg text-emerald-50/90">

                        <span id="typewriterText"></span>

                        <span class="typewriter-cursor">|</span>

                    </div>


                    <!-- Features -->
                    <div class="mt-10 grid grid-cols-2 gap-4">

                        <div
                            class="rounded-md
                                   border border-white/10
                                   bg-white/5
                                   backdrop-blur-sm
                                   p-4"
                        >

                            <div
                                class="w-9 h-9 rounded-md
                                       bg-emerald-400/20
                                       flex items-center justify-center
                                       mb-3"
                            >
                                ✓
                            </div>

                            <p class="text-sm font-semibold">
                                Member Management
                            </p>

                            <p class="text-xs text-emerald-100/70 mt-1">
                                Manage members and accounts easily.
                            </p>

                        </div>


                        <div
                            class="rounded-md
                                   border border-white/10
                                   bg-white/5
                                   backdrop-blur-sm
                                   p-4"
                        >

                            <div
                                class="w-9 h-9 rounded-md
                                       bg-teal-400/20
                                       flex items-center justify-center
                                       mb-3"
                            >
                                ৳
                            </div>

                            <p class="text-sm font-semibold">
                                Financial Tracking
                            </p>

                            <p class="text-xs text-emerald-100/70 mt-1">
                                Track contributions and investments.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- =============================================
                     FOOTER
                ============================================== -->

                <div class="text-xs text-emerald-100/60">

                    © {{ date('Y') }} Dreamers Association

                    <span class="mx-2">•</span>

                    Secure Member Portal

                </div>

            </div>

        </section>



        <!-- =====================================================
             RIGHT LOGIN SECTION
        ====================================================== -->

        <section
            class="w-full lg:w-1/2
                   bg-white
                   flex items-center justify-center
                   px-6 py-10 sm:px-10 lg:px-14"
        >

            <div class="login-card w-full max-w-md">


                <!-- =============================================
                     MOBILE BRAND
                ============================================== -->

                <div class="lg:hidden text-center mb-10">

                    <div
                        class="mobile-logo
                               mx-auto
                               w-16 h-16
                               rounded-md
                               bg-gradient-to-br
                               from-emerald-600 to-teal-500
                               flex items-center justify-center
                               text-white
                               text-2xl
                               font-extrabold"
                    >
                        D
                    </div>


                    <h1
                        class="mt-4
                               text-xl
                               font-bold
                               text-slate-900"
                    >
                        Dreamers Association
                    </h1>


                    <p
                        class="mt-1
                               text-sm
                               text-slate-500"
                    >
                        Together We Dream. Together We Grow.
                    </p>

                </div>



                <!-- =============================================
                     BACK LINK
                ============================================== -->

                <a
                    href="/"
                    class="inline-flex
                           items-center
                           gap-2
                           text-sm
                           font-medium
                           text-slate-500
                           hover:text-emerald-600
                           transition-colors"
                >

                    <span>←</span>

                    <span>Back to home</span>

                </a>



                <!-- =============================================
                     HEADER
                ============================================== -->

                <div class="mt-8">

                    <h2
                        class="text-3xl
                               sm:text-4xl
                               font-extrabold
                               tracking-tight
                               text-slate-900"
                    >
                        Welcome back
                    </h2>


                    <p
                        class="mt-3
                               text-sm
                               sm:text-base
                               text-slate-500
                               leading-relaxed"
                    >
                        Sign in to manage your Dreamers Association account.
                    </p>

                </div>



                <!-- =============================================
                     LOGIN FORM
                ============================================== -->

                
                <form
                    id="loginForm"
                    method="POST"
                    action="{{ route('login.submit') }}"
                    class="mt-8 space-y-5">

                    @csrf


                    <!-- Email or Mobile Number -->
                    <div>

                        <label
                            for="email"
                            class="block
                                   text-sm
                                   font-semibold
                                   text-slate-700
                                   mb-2"
                        >
                            Email or Mobile Number
                        </label>


                        <input
                            type="text"
                            id="email"
                            name="login"
                            autocomplete="username"
                            placeholder="Enter your email"
                            required

                            class="login-input
                                   w-full
                                   h-12
                                   rounded-md
                                   border
                                   border-slate-200
                                   bg-slate-50
                                   px-4
                                   text-sm
                                   text-slate-900
                                   placeholder:text-slate-400
                                   focus:border-emerald-600
                                   focus:ring-2
                                   focus:ring-emerald-600/10"
                        >

                    </div>



                    <!-- PASSWORD -->
                    <div>

                        <div
                            class="flex
                                   items-center
                                   justify-between
                                   mb-2"
                        >

                            <label
                                for="password"
                                class="text-sm
                                       font-semibold
                                       text-slate-700"
                            >
                                Password
                            </label>


                            <a
                                href="#"
                                class="text-xs
                                       font-semibold
                                       text-emerald-600
                                       hover:text-emerald-700
                                       transition-colors"
                            >
                                Forgot password?
                            </a>

                        </div>


                        <div class="relative">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                required

                                class="login-input
                                       w-full
                                       h-12
                                       rounded-md
                                       border
                                       border-slate-200
                                       bg-slate-50
                                       px-4
                                       pr-12
                                       text-sm
                                       text-slate-900
                                       placeholder:text-slate-400
                                       focus:border-emerald-600
                                       focus:ring-2
                                       focus:ring-emerald-600/10"
                            >


                            <!-- Password Toggle -->
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute
                                       right-3
                                       top-1/2
                                       -translate-y-1/2
                                       w-8
                                       h-8
                                       rounded-md
                                       flex
                                       items-center
                                       justify-center
                                       cursor-pointer
                                       text-slate-400
                                       hover:text-emerald-600
                                       hover:bg-emerald-50
                                       transition-colors"
                                aria-label="Show password"
                            >

                                <svg
                                    id="eyeIcon"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.8"
                                    stroke="currentColor"
                                    class="w-5 h-5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178a1.012 1.012 0 0 1 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178Z"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                    />
                                </svg>

                            </button>

                        </div>

                    </div>



                    <!-- REMEMBER -->
                    <div
                        class="flex
                               items-center
                               justify-between"
                    >

                        <label
                            class="inline-flex
                                   items-center
                                   gap-2
                                   cursor-pointer"
                        >

                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="w-4 h-4
                                       rounded
                                       border-slate-300
                                       text-emerald-600
                                       focus:ring-emerald-600/20"
                            >

                            <span
                                class="text-sm
                                       text-slate-600"
                            >
                                Remember me
                            </span>

                        </label>

                    </div>



                    <!-- ERROR -->
                    <div
                        id="loginError"
                        class="message-box
                               hidden
                               rounded-md
                               border
                               border-red-200
                               bg-red-50
                               px-4
                               py-3
                               text-sm
                               text-red-600"
                    >
                    </div>



                    <!-- SUCCESS -->
                    <div
                        id="loginSuccess"
                        class="message-box
                               hidden
                               rounded-md
                               border
                               border-emerald-200
                               bg-emerald-50
                               px-4
                               py-3
                               text-sm
                               text-emerald-700"
                    >
                    </div>



                    <!-- SUBMIT -->
                    <button
                        type="submit"
                        id="loginButton"

                        class="login-button
                               w-full
                               h-12
                               rounded-md
                               bg-gradient-to-r
                               from-emerald-600
                               to-green-800
                               hover:from-emerald-700
                               hover:to-green-900
                               text-white
                               font-semibold
                               text-sm
                               shadow-lg
                               shadow-emerald-600/20
                               transition-all
                               duration-200
                               flex
                               items-center
                               cursor-pointer
                               justify-center
                               gap-2"
                    >

                        <span id="buttonText">
                            Sign In →
                        </span>

                        <svg
                            id="loadingIcon"
                            class="hidden animate-spin w-5 h-5"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            ></path>
                        </svg>

                    </button>

                </form>



                <!-- =============================================
                     BOTTOM INFO
                ============================================== -->

                <div class="mt-8">

                    <div
                        class="flex items-center gap-3"
                    >

                        <div class="h-px bg-slate-200 flex-1"></div>

                        <span
                            class="text-xs
                                   text-slate-400
                                   uppercase
                                   tracking-wider"
                        >
                            Secure Access
                        </span>

                        <div class="h-px bg-slate-200 flex-1"></div>

                    </div>


                    <p
                        class="mt-5
                               text-center
                               text-xs
                               text-slate-400
                               leading-relaxed"
                    >
                        Your account information is protected and securely
                        handled by the Dreamers Association member portal.
                    </p>

                </div>

            </div>

        </section>

    </div>



    <!-- =========================================================
         JAVASCRIPT
    ========================================================== -->

    <script>

        document.addEventListener('DOMContentLoaded', function () {


            /* =====================================================
               TYPEWRITER
            ====================================================== */

            const phrases = [
                'Manage your membership and contributions with ease.',
                'Track investments, projects and financial activities.',
                'Stay connected with the Dreamers community.',
                'Together we build a better tomorrow.'
            ];

            const typewriterElement =
                document.getElementById('typewriterText');

            let phraseIndex = 0;
            let characterIndex = 0;
            let deleting = false;


            function typeWriter() {

                const currentPhrase =
                    phrases[phraseIndex];


                if (!deleting) {

                    typewriterElement.textContent =
                        currentPhrase.substring(
                            0,
                            characterIndex + 1
                        );

                    characterIndex++;


                    if (characterIndex === currentPhrase.length) {

                        deleting = true;

                        setTimeout(typeWriter, 2200);

                        return;
                    }

                    setTimeout(typeWriter, 45);

                } else {

                    typewriterElement.textContent =
                        currentPhrase.substring(
                            0,
                            characterIndex - 1
                        );

                    characterIndex--;


                    if (characterIndex === 0) {

                        deleting = false;

                        phraseIndex =
                            (phraseIndex + 1) % phrases.length;

                        setTimeout(typeWriter, 400);

                        return;
                    }

                    setTimeout(typeWriter, 25);
                }
            }


            if (typewriterElement) {
                typeWriter();
            }



            /* =====================================================
               PASSWORD TOGGLE
            ====================================================== */

            const passwordInput =
                document.getElementById('password');

            const togglePassword =
                document.getElementById('togglePassword');

            if (togglePassword) {

                togglePassword.addEventListener('click', function () {

                    const isPassword =
                        passwordInput.type === 'password';


                    passwordInput.type =
                        isPassword ? 'text' : 'password';


                    togglePassword.setAttribute(
                        'aria-label',
                        isPassword
                            ? 'Hide password'
                            : 'Show password'
                    );

                });

            }
        });

    </script>

</body>

</html>