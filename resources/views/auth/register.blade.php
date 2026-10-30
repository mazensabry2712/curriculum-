@extends('layouts.guest')

@section('title', 'Create workspace')

@section('content')
<div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-5 py-12">
    <div class="w-full max-w-xl rounded-[2rem] border border-white/10 bg-slate-900/90 p-7 shadow-2xl sm:p-10">
        <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-cyan-400 text-lg font-black text-slate-950">C</span><span class="font-bold">CSGA <span class="font-normal text-slate-500">/ Workspace setup</span></span></a>
        <h1 class="mt-9 text-3xl font-semibold">Create your workspace</h1><p class="mt-2 text-sm leading-6 text-slate-400">Set up your university or department workspace and administrator account.</p>
        @if ($errors->any())<div class="mt-6 rounded-xl border border-rose-400/30 bg-rose-400/10 p-3 text-sm text-rose-200"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-4">
            @csrf
            <div><label class="mb-2 block text-xs font-semibold text-slate-300">University / organization name</label><input name="organization_name" value="{{ old('organization_name') }}" required maxlength="160" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-300" placeholder="Faculty of Computer Science"></div>
            <div><label class="mb-2 block text-xs font-semibold text-slate-300">Administrator name</label><input name="name" value="{{ old('name') }}" required maxlength="120" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-300" placeholder="Your full name"></div>
            <div><label class="mb-2 block text-xs font-semibold text-slate-300">Email address</label><input name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-300" placeholder="admin@university.edu"></div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-2 block text-xs font-semibold text-slate-300">Password</label><input name="password" type="password" required minlength="10" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-300"></div><div><label class="mb-2 block text-xs font-semibold text-slate-300">Confirm password</label><input name="password_confirmation" type="password" required minlength="10" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-300"></div></div>
            <p class="text-xs leading-5 text-slate-500">Use at least 10 characters with letters and numbers. Your new account starts with organization administrator access.</p>
            <button class="mt-2 w-full rounded-xl bg-cyan-400 px-5 py-3.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">Create workspace</button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-400">Already registered? <a href="{{ route('login') }}" class="font-semibold text-cyan-300">Sign in</a></p>
    </div>
</div>
@endsection