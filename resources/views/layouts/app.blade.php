<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Workspace') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]">
    <aside class="bg-slate-950 px-5 py-6 text-slate-200">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="grid size-11 place-items-center rounded-2xl bg-cyan-400 text-lg font-black text-slate-950">C</span>
            <span><span class="block text-sm font-bold tracking-wide text-white">CSGA</span><span class="text-xs text-slate-400">Academic intelligence</span></span>
        </a>
        <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-3">
            <p class="text-[11px] font-semibold uppercase tracking-[.18em] text-slate-500">Workspace</p>
            <p class="mt-1 truncate text-sm font-semibold text-white">{{ auth()->user()->organization?->name ?? 'Personal workspace' }}</p>
        </div>
        <nav class="mt-7 space-y-1 text-sm">
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">Overview</p>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>◫</span> Dashboard</a>
            @hasanyrole('super-admin|university-admin|department-admin|instructor')
                <p class="px-3 pb-2 pt-6 text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">Academic</p>
                <a href="{{ route('academic.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('academic.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>▤</span> Curriculum</a>
                <a href="{{ route('skills.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('skills.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>✳</span> Skills catalog</a>
                <a href="{{ route('documents.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('documents.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>▧</span> AI documents</a>
                <a href="{{ route('students.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('students.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>♙</span> Students</a>
            @endhasanyrole
            @hasanyrole('super-admin|university-admin|department-admin|career-advisor|employer')
                <p class="px-3 pb-2 pt-6 text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">Market intelligence</p>
                <a href="{{ route('labor-market.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('labor-market.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>⌁</span> Labor market</a>
            @endhasanyrole
            @hasanyrole('super-admin|university-admin|department-admin|instructor|career-advisor')
                <p class="px-3 pb-2 pt-6 text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">Intelligence</p>
                <a href="{{ route('analysis.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('analysis.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>◈</span> Gap analysis</a>
            @endhasanyrole
            @hasanyrole('student|graduate')
                <p class="px-3 pb-2 pt-6 text-[10px] font-bold uppercase tracking-[.2em] text-slate-500">My career</p>
                <a href="{{ route('student-portal.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-white/10 {{ request()->routeIs('student-portal.*') ? 'bg-white/10 text-white' : 'text-slate-400' }}"><span>◎</span> My skills & career</a>
            @endhasanyrole
        </nav>
        <div class="mt-10 border-t border-white/10 pt-5">
            <div class="flex items-center gap-3">
                <div class="grid size-9 place-items-center rounded-full bg-cyan-400/15 text-xs font-bold text-cyan-200">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->getRoleNames()->first() ?? 'Member' }}</p></div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<button class="w-full rounded-xl border border-white/10 px-3 py-2 text-left text-xs font-semibold text-slate-400 hover:bg-white/10 hover:text-white">Sign out ↗</button></form>
        </div>
    </aside>
    <main class="min-w-0">
        <header class="flex min-h-20 items-center justify-between border-b border-slate-200 bg-white px-5 sm:px-8">
            <div><p class="text-xs font-semibold uppercase tracking-[.17em] text-cyan-700">Curriculum Skills Gap Analyzer</p><p class="mt-1 text-sm text-slate-500">@yield('eyebrow', 'Evidence-led academic intelligence')</p></div>
            <div class="hidden rounded-full border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500 sm:block">{{ now()->format('D, d M Y') }}</div>
        </header>
        <div class="mx-auto max-w-[1500px] p-5 sm:p-8">
            @if (session('success'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if (session('info'))<div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('info') }}</div>@endif
            @if ($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"><p class="font-semibold">Please review the highlighted information.</p><ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
        <footer class="border-t border-slate-200 px-5 py-5 text-center text-xs text-slate-400 sm:px-8">CSGA · Curriculum and career intelligence · {{ date('Y') }}</footer>
    </main>
</div>
</body>
</html>