<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Under Maintenance | Dreamers Association</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
    <div class="max-w-md text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 text-2xl">
            🛠️
        </div>

        <h1 class="mt-6 text-2xl font-bold text-slate-800">
            We'll be right back
        </h1>

        <p class="mt-3 text-sm text-slate-500 leading-relaxed">
            {{ $message }}
        </p>
    </div>
</body>
</html>