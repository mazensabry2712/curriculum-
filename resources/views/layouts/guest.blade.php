<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <div class="pointer-events-none fixed inset-0 overflow-hidden"><div class="absolute -left-40 -top-40 size-[36rem] rounded-full bg-cyan-500/10 blur-3xl"></div><div class="absolute -bottom-60 right-0 size-[42rem] rounded-full bg-blue-600/10 blur-3xl"></div></div>
    <div class="relative min-h-screen">@yield('content')</div>
</body>
</html>