@extends('layouts.guest')

@section('title', 'Curriculum intelligence')

@section('content')
<div class="mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-7">
    <header class="flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-3"><span class="grid size-11 place-items-center rounded-2xl bg-cyan-400 text-lg font-black text-slate-950">C</span><span><b class="block tracking-wide">CSGA</b><span class="text-xs text-slate-400">Academic intelligence platform</span></span></a>
        <div class="flex gap-2"><a href="{{ route('login') }}" class="rounded-xl border border-white/15 px-4 py-2.5 text-sm font-semibold text-slate-200 hover:bg-white/5">Sign in</a><a href="{{ route('register') }}" class="rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">Create workspace</a></div>
    </header>
    <main class="grid flex-1 items-center gap-14 py-20 lg:grid-cols-[1.05fr_.95fr]">
        <section>
            <p class="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-xs font-semibold tracking-wide text-cyan-200"><span class="size-1.5 rounded-full bg-cyan-300"></span> AI-assisted · Evidence-led · Explainable</p>
            <h1 class="mt-7 max-w-3xl text-5xl font-semibold leading-[1.08] tracking-tight sm:text-6xl">Connect what students learn to <span class="text-cyan-300">what work needs.</span></h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">Analyze curriculum coverage, verify student skills, understand labor-market requirements, and turn measurable gaps into practical improvement plans.</p>
            <div class="mt-9 flex flex-wrap gap-3"><a href="{{ route('register') }}" class="rounded-xl bg-cyan-400 px-6 py-3.5 text-sm font-bold text-slate-950 hover:bg-cyan-300">Start a workspace <span aria-hidden="true">→</span></a><a href="#how-it-works" class="rounded-xl border border-white/15 px-6 py-3.5 text-sm font-semibold text-white hover:bg-white/5">Explore the platform</a></div>
            <div class="mt-12 grid max-w-lg grid-cols-3 gap-5 border-t border-white/10 pt-6"><div><p class="text-2xl font-semibold text-white">01</p><p class="mt-1 text-xs text-slate-400">Curriculum fit</p></div><div><p class="text-2xl font-semibold text-white">02</p><p class="mt-1 text-xs text-slate-400">Student readiness</p></div><div><p class="text-2xl font-semibold text-white">03</p><p class="mt-1 text-xs text-slate-400">Market demand</p></div></div>
        </section>
        <section class="rounded-[2rem] border border-white/10 bg-white/[.04] p-5 shadow-2xl shadow-cyan-950/30 sm:p-7">
            <div class="flex items-center justify-between"><div><p class="text-xs font-semibold uppercase tracking-[.18em] text-cyan-200">Skills alignment preview</p><h2 class="mt-2 text-xl font-semibold">Backend Developer</h2></div><span class="rounded-full bg-amber-300/10 px-3 py-1 text-xs font-semibold text-amber-200">Illustrative data</span></div>
            <div class="mt-8 flex items-end justify-between"><div><p class="text-5xl font-semibold tracking-tight">68<span class="text-2xl text-cyan-300">%</span></p><p class="mt-2 text-sm text-slate-400">Curriculum alignment score</p></div><div class="rounded-2xl bg-cyan-300/10 p-3 text-right"><p class="text-xs text-cyan-200">Gap detected</p><p class="mt-1 text-2xl font-semibold text-white">4 skills</p></div></div>
            <div class="mt-6 h-2 overflow-hidden rounded-full bg-white/10"><div class="h-full w-[68%] rounded-full bg-cyan-300"></div></div>
            <div class="mt-8 space-y-4">
                @foreach ([['REST API Development',4,3],['SQL',4,4],['Docker',3,1],['Software Testing',4,2],['Laravel',4,2]] as [$name,$needed,$covered])
                    <div><div class="mb-2 flex items-center justify-between text-sm"><span class="text-slate-200">{{ $name }}</span><span class="text-xs text-slate-400">{{ $covered }}/{{ $needed }}</span></div><div class="h-1.5 overflow-hidden rounded-full bg-white/10"><div class="h-full rounded-full bg-cyan-300" style="width: {{ $covered / $needed * 100 }}%"></div></div></div>
                @endforeach
            </div>
            <p class="mt-7 border-t border-white/10 pt-5 text-xs leading-6 text-slate-400">Every score is calculated from approved curriculum evidence and versioned job requirements. AI suggestions remain pending until reviewed by an authorized person.</p>
        </section>
    </main>
    <section id="how-it-works" class="grid gap-4 border-t border-white/10 py-8 md:grid-cols-3">
        @foreach ([['01','Analyze curriculum','Extract course outcomes and skill evidence from syllabus documents.'],['02','Compare the market','Build job profiles from curated requirements and reviewed job-description extraction.'],['03','Improve readiness','Measure student skill evidence and recommend concrete next steps.']] as [$number,$title,$description])
            <div class="rounded-2xl border border-white/10 bg-white/[.03] p-5"><p class="text-xs font-bold tracking-widest text-cyan-300">{{ $number }}</p><h3 class="mt-3 font-semibold">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-slate-400">{{ $description }}</p></div>
        @endforeach
    </section>
    <footer class="flex flex-wrap justify-between gap-2 border-t border-white/10 pt-5 text-xs text-slate-500"><span>CSGA · Curriculum Skills Gap Analyzer</span><span>Designed for evidence-based education decisions</span></footer>
</div>
@endsection