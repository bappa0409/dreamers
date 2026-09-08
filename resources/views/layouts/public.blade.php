<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seoOrgName = setting('organization_name', 'Dreamers Association');

        $seoDefaultDescription = $seoOrgName . ' — সদস্যদের সম্মিলিত সঞ্চয়, বিনিয়োগ ও সম্পদ গঠনের মাধ্যমে স্বচ্ছ ও দীর্ঘমেয়াদি আর্থিক উন্নয়ন নিশ্চিত করার একটি ঐক্যবদ্ধ উদ্যোগ।';
        $seoDefaultTitle = $seoOrgName . ' | স্বচ্ছতা, ঐক্য ও সম্মিলিত আর্থিক উন্নয়ন';

        $seoTitle = trim($__env->yieldContent('title', $seoDefaultTitle));
        $seoDescription = trim(preg_replace('/\s+/', ' ', $__env->yieldContent('description', $seoDefaultDescription)));
        $seoKeywords = trim(preg_replace('/\s+/', ' ', $__env->yieldContent('keywords', '')));
        $seoRobots = trim($__env->yieldContent('robots', 'index, follow'));
        $seoCanonical = trim($__env->yieldContent('canonical', url()->current()));

        $seoLogoPath = setting('site_logo');
        $seoImage = $seoLogoPath ? asset('storage/' . $seoLogoPath) : null;

        $seoOrgDescription = setting('organization_description') ?: $seoDefaultDescription;
        $seoOrgEmail = setting('organization_email');
        $seoOrgPhone = setting('organization_phone');
        $seoOrgAddress = setting('organization_address');
    @endphp

    {{-- Primary Meta Tags --}}
    <title>{{ $seoTitle }}</title>
    <meta name="title" content="{{ $seoTitle }}">
    <meta name="description" content="{{ $seoDescription }}">
    @if($seoKeywords)
    <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <meta name="robots" content="{{ $seoRobots }}">
    <meta name="author" content="{{ $seoOrgName }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    {{-- Open Graph / Facebook, Messenger, WhatsApp --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:site_name" content="{{ $seoOrgName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:locale" content="bn_BD">
    @if($seoImage)
    <meta property="og:image" content="{{ $seoImage }}">
    @endif

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $seoCanonical }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    @if($seoImage)
    <meta name="twitter:image" content="{{ $seoImage }}">
    @endif

    {{-- Google Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if(setting('site_favicon'))
    <link rel="icon" href="{{ asset('storage/'.setting('site_favicon')) }}">
    @endif

    @vite(['resources/css/website.css', 'resources/js/website.js'])

    {{-- Organization / WebSite structured data (site-wide, real settings only) --}}
    <script type="application/ld+json">
    {!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $seoOrgName,
        'url' => url('/'),
        'description' => $seoOrgDescription,
        'logo' => $seoImage,
        'image' => $seoImage,
        'email' => $seoOrgEmail ?: null,
        'telephone' => $seoOrgPhone ?: null,
        'address' => $seoOrgAddress ?: null,
    ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $seoOrgName,
        'url' => url('/'),
        'inLanguage' => 'bn-BD',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>

    @stack('schema')

    @stack('styles')
</head>

<body>

    @yield('content')

    @stack('scripts')
</body>

</html>
