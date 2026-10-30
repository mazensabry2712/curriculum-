@extends('layouts.app')

@section('title', 'Dashboard')
@section('eyebrow', 'Workspace overview')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div><p class="text-sm font-medium text-cyan-700">Good work starts with evidence</p><h1 class="mt-1 text-3xl font-semibold tracking-tight">Workspace overview</h1><p class="mt-2 text-sm text-slate-500">A live snapshot of curriculum, skills, students, and labor-market analysis.</p></div>
    <a href="{{ route('analysis.index') }}" class="rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Run an analysis <span aria-hidden="true">→</span></a>
</div>
<div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ([['Courses',$courseCount,'Academic catalog','▤'],['Skills',$skillCount,'Shared skill taxonomy','✳'],['Students',$studentCount,'Learner profiles','♙'],['Job profiles',$jobCount,'Career requirements','⌁'],['Analysis runs',$analysisCount,'Saved result snapshots','◈']] as [$label,$value,$helper,$icon])
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $label }}</span><span class="grid size-9 place-items-center rounded-xl bg-cyan-50 text-lg text-cyan-800">{{ $icon }}</span></div><p class="mt-5 text-3xl font-semibold tracking-tight">{{ number_format($value) }}</p><p class="mt-1 text-xs text-slate-400">{{ $helper }}</p></div>
    @endforeach
</div>
<div class="mt-8 grid gap-5 xl:grid-cols-[1.4fr_.8fr]">
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between"><div><h2 class="text-lg font-semibold">Recent analysis</h2><p class="mt-1 text-xs text-slate-500">Historical results from this organization</p></div><a href="{{ route('analysis.index') }}" class="text-xs font-semibold text-cyan-700 hover:text-cyan-900">View all →</a></div>
        <div class="mt-5 divide-y divide-slate-100">
            @forelse ($recentAnalyses as $run)
                <a href="{{ route('analysis.show', $run) }}" class="flex flex-wrap items-center justify-between gap-3 py-4 hover:bg-slate-50">
                    <div><p class="text-sm font-semibold">{{ $run->analysis_type === 'curriculum' ? 'Curriculum analysis' : 'Student analysis' }} · {{ $run->jobProfile?->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $run->curriculumVersion?->program?->name ?? $run->studentProfile?->user?->name ?? 'Analysis' }} · {{ $run->created_at->format('d M Y') }}</p></div>
                    <div class="text-right"><p class="text-xl font-semibold">{{ $run->alignment_score === null ? '—' : number_format((float) $run->alignment_score, 1).'%' }}</p><p class="text-[11px] text-slate-400">alignment</p></div>
                </a>
            @empty
                <div class="py-12 text-center"><p class="text-sm font-semibold text-slate-700">No analysis runs yet</p><p class="mt-1 text-xs text-slate-500">Add a curriculum, map skills, then compare it with a job profile.</p></div>
            @endforelse
        </div>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between"><div><h2 class="text-lg font-semibold">AI review queue</h2><p class="mt-1 text-xs text-slate-500">Documents awaiting human verification</p></div><span class="grid size-10 place-items-center rounded-xl bg-amber-50 text-lg text-amber-700">▧</span></div>
        <p class="mt-8 text-5xl font-semibold tracking-tight">{{ $pendingDocuments }}</p><p class="mt-2 text-sm text-slate-500">Documents uploaded, processing, or awaiting review.</p>
        @hasanyrole('super-admin|university-admin|department-admin|instructor')
            <a href="{{ route('documents.index') }}" class="mt-6 inline-flex rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50">Open document center →</a>
        @endhasanyrole
        <div class="mt-8 rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-700">Governance principle</p><p class="mt-1 text-xs leading-5 text-slate-500">AI output stays provisional until an authorized instructor or administrator approves the evidence.</p></div>
    </section>
</div>
@endsection