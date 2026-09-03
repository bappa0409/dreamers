<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', setting('organization_name', 'Dreamers Association')) | ঐক্যে উন্নতি, স্বপ্নে সমৃদ্ধি</title>

    <meta name="description"
        content="@yield('description', 'Dreamers Association — একটি স্বচ্ছ, ঐক্যবদ্ধ ও দীর্ঘমেয়াদি আর্থিক উন্নয়নের উদ্যোগ।')">

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">


    @if(setting('site_favicon'))
    <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif
    
    @vite(['resources/css/website.css', 'resources/js/website.js'])

    @stack('styles')
</head>

<body>

    @yield('content')

    @stack('scripts')
</body>

</html>