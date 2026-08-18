<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Dreamers Association')
    </title>

    <meta
        name="description"
        content="@yield('description', 'Dreamers Association')"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-white text-slate-900">

    @yield('content')

</body>

</html>