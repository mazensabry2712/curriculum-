@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
<div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-5 py-12">
    <div class="grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white/10 bg-slate-900/80 shadow-2xl md:grid-cols-2">
        <div class="hidden flex-col justify-between bg-gradient-to-br from-cyan-400 to-blue-500 p-10 text-slate-950 md:flex">
            <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-slate-950 text-lg font-black text-cyan-300">C</span><b>CSGA</b></a>
            <div><p class="text-xs font-bold uppercase tracking-[.2em]">Welcome back</p><h1 class="mt-5 text-4xl font-semibold leading-tight">Make academic decisions with clearer evidence.</h1><p class="mt-5 max-w-sm text-sm leading-7 text-slate-900/70">Review curriculum coverage, understand skill gaps, and improve career readiness.</p></div>
            <p class="text-xs font-semibold">Curriculum Skills Gap Analyzer</p>
        </div>
        <div class="p-7 sm:p-10">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-cyan-300">← CSGA home</a>
            <h2 class="mt-10 text-3xl font-semibold">Sign in</h2><p class="mt-2 text-sm text-slate-400">Access your organization workspace.</p>
            @if ($errors->any())<div class="mt-6 rounded-xl border border-rose-400/30 bg-rose-400/10 p-3 text-sm text-rose-200">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <div><label for="email" class="mb-2 block text-xs font-semibold text-slate-300">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-300" placeholder="you@university.edu"></div>
                <div><label for="password" class="mb-2 block text-xs font-semibold text-slate-300">Password</label><input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none transition focus:border-cyan-300" placeholder="Your password"></div>
                <label class="flex items-center gap-2 text-xs text-slate-400"><input type="checkbox" name="remember" value="1" class="rounded border-white/20 bg-slate-950 text-cyan-400"> Remember me</label>
                <button class="w-full rounded-xl bg-cyan-400 px-5 py-3.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">Sign in to workspace</button>
            </form>
            <p class="mt-7 text-center text-sm text-slate-400">New to CSGA? <a href="{{ route('register') }}" class="font-semibold text-cyan-300 hover:text-cyan-200">Create an organization</a></p>
        </div>
    </div>
</div>
@endsection