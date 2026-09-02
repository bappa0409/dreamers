<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', setting('organization_name', 'Dreamers Association')) | ঐক্যে উন্নতি, স্বপ্নে সমৃদ্ধি</title>

    <meta name="description"
        content="@yield('description', 'Dreamers Association — একটি স্বচ্ছ, ঐক্যবদ্ধ ও দীর্ঘমেয়াদি আর্থিক উন্নয়নের উদ্যোগ।')">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Tailwind Config --}}
    <script>
        tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Hind Siliguri', 'sans-serif'],
                },
                colors: {
                    primary: '#0f766e',
                    secondary: '#115e59',
                    accent: '#f59e0b',
                    brand: {
                        50: '#f0fdfa',
                        100: '#ccfbf1',
                        200: '#99f6e4',
                        300: '#5eead4',
                        400: '#2dd4bf',
                        500: '#14b8a6',
                        600: '#0d9488',
                        700: '#0f766e',
                        800: '#115e59',
                        900: '#0f3d3a',
                        950: '#082f2b',
                    },
                },
                boxShadow: {
                    soft: '0 10px 40px rgba(15, 118, 110, 0.08)',
                    card: '0 20px 60px rgba(15, 23, 42, 0.08)',
                },
                animation: {
                    float: 'float 5s ease-in-out infinite',
                    pulseSlow: 'pulseSlow 3s ease-in-out infinite',
                },
                keyframes: {
                    float: {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%': { transform: 'translateY(-10px)' }
                    },
                    pulseSlow: {
                        '0%, 100%': { opacity: '0.5' },
                        '50%': { opacity: '1' }
                    }
                }
            }
        }
    }
    </script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Hind Siliguri', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .form-control:focus,
        .form-select:focus {
            box-shadow: none
        }

        .hero-grid {
            background-image:
                linear-gradient(rgba(15, 118, 110, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 118, 110, 0.05) 1px, transparent 1px);
            background-size: 42px 42px;
        }

        .reveal {
            opacity: 0;
            transform: translateY(25px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .nav-scrolled {
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.94) !important;
        }

        .dashboard-line {
            background: linear-gradient(90deg, rgba(20, 184, 166, 0.12), rgba(20, 184, 166, 0.03));
        }

        /* FAQ ACCORDION */
        .faq-item[open] .faq-plus {
            background: rgb(240 253 250);
            border-color: rgb(153 246 228);
            color: rgb(13 148 136);
        }

        .faq-item[open] .faq-plus svg {
            transform: rotate(45deg);
        }

        .faq-item[open] summary {
            color: rgb(15 118 110);
        }

        .faq-item summary::-webkit-details-marker {
            display: none;
        }

        .faq-item summary {
            outline: none;
        }

        .faq-item summary:focus-visible {
            outline: 2px solid rgb(45 212 191);
            outline-offset: -2px;
        }
    </style>

    @stack('styles')
</head>

<body>

    @yield('content')

    <script>
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>

</html>