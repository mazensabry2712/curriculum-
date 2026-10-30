@extends('layouts.app')

@section('title', 'Gap analysis')
@section('eyebrow', 'Explainable analysis and historical snapshots')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">Skills gap analysis</h1><p class="mt-2 text-sm text-slate-500">Compare approved curriculum coverage or verified student evidence against weighted job requirements.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $runs->total() }} saved analyses</span></div>
<div class="mt-7 grid gap-5 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-cyan-50 text-lg text-cyan-800">▤</span><div><h2 class="font-semibold">Curriculum vs job profile</h2><p class="mt-1 text-xs text-slate-500">Measures approved course skill coverage.</p></div></div>
        <form method="POST" action="{{ route('analysis.curriculum') }}" class="mt-5 space-y-3">@csrf
            <select name="curriculum_version_id" required class="field"><option value="">Select curriculum version</option>@foreach ($curricula as $curriculum)<option value="{{ $curriculum->id }}">{{ $curriculum->program->name }} · {{ $curriculum->version }} · {{ ucfirst($curriculum->status) }}</option>@endforeach</select>
            <select name="job_profile_id" required class="field"><option value="">Select target job profile</option>@foreach ($jobProfiles as $profile)<option value="{{ $profile->id }}">{{ $profile->title }} · {{ ucfirst($profile->seniority) }}</option>@endforeach</select>
            <button class="action w-full">Calculate curriculum alignment</button>
        </form>
        <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">Coverage uses approved course mappings, credit-hour weighting, and the optional-course discount. Missing skills are represented as zero coverage, not omitted.</p>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-violet-50 text-lg text-violet-800">◎</span><div><h2 class="font-semibold">Student vs job profile</h2><p class="mt-1 text-xs text-slate-500">Uses the latest verified assessment for each skill.</p></div></div>
        <form method="POST" action="{{ route('analysis.student') }}" class="mt-5 space-y-3">@csrf
            <select name="student_profile_id" required class="field"><option value="">Select student</option>@foreach ($students as $student)<option value="{{ $student->id }}">{{ $student->user->name }} · {{ $student->student_number }}</option>@endforeach</select>
            <select name="job_profile_id" required class="field"><option value="">Select target job profile</option>@foreach ($jobProfiles as $profile)<option value="{{ $profile->id }}">{{ $profile->title }} · {{ ucfirst($profile->seniority) }}</option>@endforeach</select>
            <button class="action w-full">Calculate student readiness</button>
        </form>
        <p class="mt-4 rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">Unverified and unassessed skills never silently become zero. The report shows assessment coverage separately from alignment.</p>
    </section>
</div>
<section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h2 class="font-semibold">Analysis history</h2><span class="text-xs text-slate-400">Saved input snapshots</span></div>
    <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead><tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400"><th class="py-3 pr-4">Type / target</th><th class="py-3 pr-4">Subject</th><th class="py-3 pr-4">Alignment</th><th class="py-3 pr-4">Assessed coverage</th><th class="py-3 pr-4">Date</th><th></th></tr></thead><tbody class="divide-y divide-slate-100">@forelse ($runs as $run)<tr><td class="py-4 pr-4"><p class="font-semibold">{{ ucfirst($run->analysis_type) }} analysis</p><p class="mt-1 text-xs text-slate-500">{{ $run->jobProfile->title }}</p></td><td class="py-4 pr-4 text-slate-600">{{ $run->curriculumVersion?->program?->name ?? $run->studentProfile?->user?->name ?? '—' }}</td><td class="py-4 pr-4 font-semibold">{{ $run->alignment_score === null ? '—' : number_format((float)$run->alignment_score,1).'%' }}</td><td class="py-4 pr-4 text-slate-600">{{ $run->assessed_coverage === null ? '—' : number_format((float)$run->assessed_coverage,1).'%' }}</td><td class="py-4 pr-4 text-xs text-slate-500">{{ $run->created_at->format('d M Y, H:i') }}</td><td class="py-4 text-right"><a href="{{ route('analysis.show',$run) }}" class="font-semibold text-cyan-700 hover:text-cyan-900">Details →</a></td></tr>@empty<tr><td colspan="6" class="py-12 text-center text-sm text-slate-400">No analysis snapshots yet.</td></tr>@endforelse</tbody></table></div>
    <div class="mt-4">{{ $runs->links() }}</div>
</section>
@endsection