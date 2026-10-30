@extends('layouts.app')

@section('title', 'My skills & career')
@section('eyebrow', 'Personal skills portfolio and career readiness')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">My skills & career</h1><p class="mt-2 text-sm text-slate-500">Build an evidence-backed skills profile and compare it with your target career.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $student->program?->name ?? 'Program not set' }}</span></div>
<div class="mt-7 grid gap-5 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Set career target</h2><form method="POST" action="{{ route('student-portal.target.store') }}" class="mt-4 space-y-3">@csrf
        <select name="job_profile_id" required class="field"><option value="">Choose target job</option>@foreach ($jobProfiles as $profile)<option value="{{ $profile->id }}" {{ $student->targets->contains(fn ($target) => $target->job_profile_id === $profile->id && $target->is_primary) ? 'selected' : '' }}>{{ $profile->title }} · {{ $profile->seniority }}</option>@endforeach</select>
        <button class="action">Save primary career target</button>
    </form>
    <form method="POST" action="{{ route('student-portal.analyze') }}" class="mt-4">@csrf<button class="w-full rounded-xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-semibold text-cyan-900 hover:bg-cyan-100">Analyze my verified skills →</button></form>
    <p class="mt-3 text-xs leading-5 text-slate-500">Only verified skill assessments are used in official alignment. Self-assessment entries remain provisional until reviewed.</p></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Self-assess a skill</h2><form method="POST" action="{{ route('student-portal.assessments.store') }}" class="mt-4 space-y-3">@csrf
        <select name="skill_id" required class="field"><option value="">Choose a skill</option>@foreach ($skills as $skill)<option value="{{ $skill->id }}">{{ $skill->name }}</option>@endforeach</select>
        <select name="level" required class="field">@for ($i=1;$i<=5;$i++)<option value="{{ $i }}">Level {{ $i }} — {{ [1=>'Awareness',2=>'Understanding',3=>'Guided application',4=>'Independent application',5=>'Advanced practice'][$i] }}</option>@endfor</select>
        <textarea name="evidence" rows="2" placeholder="Describe how you used this skill or what evidence you can provide." class="field"></textarea>
        <button class="action w-full">Submit self-assessment</button>
    </form></section>
</div>
<div class="mt-6 grid gap-5 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex justify-between"><h2 class="font-semibold">My skill assessments</h2><span class="text-xs text-slate-400">{{ $student->assessments->count() }} records</span></div><div class="mt-4 divide-y divide-slate-100">@forelse ($student->assessments->sortByDesc('assessed_at') as $assessment)<div class="flex items-center justify-between gap-3 py-3"><div><p class="text-sm font-semibold">{{ $assessment->skill->name }}</p><p class="mt-1 text-xs text-slate-500">{{ ucfirst($assessment->assessment_type) }} · {{ $assessment->assessed_at?->format('d M Y') }}</p></div><div class="text-right"><p class="font-semibold">Level {{ $assessment->level }}/5</p><span class="text-[10px] font-semibold {{ $assessment->is_verified ? 'text-emerald-700' : 'text-amber-700' }}">{{ $assessment->is_verified ? 'Verified' : 'Pending review' }}</span></div></div>@empty<p class="py-8 text-center text-sm text-slate-400">No assessments yet.</p>@endforelse</div></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Add a project to your portfolio</h2><form method="POST" action="{{ route('student-portal.projects.store') }}" class="mt-4 space-y-3">@csrf
        <input name="title" required placeholder="Project title" class="field"><textarea name="description" rows="3" placeholder="What did you build? Which skills did you use?" class="field"></textarea>
        <div class="grid grid-cols-2 gap-3"><input name="repository_url" type="url" placeholder="GitHub URL" class="field"><input name="project_url" type="url" placeholder="Live demo URL" class="field"></div>
        <label class="block text-xs font-semibold text-slate-500">Skills used</label><div class="max-h-32 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3">@foreach ($skills as $skill)<label class="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" class="rounded border-slate-300 text-cyan-700">{{ $skill->name }}</label>@endforeach</div>
        <button class="action w-full">Submit project evidence</button>
    </form></section>
</div>
<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Project portfolio</h2><div class="mt-4 grid gap-3 md:grid-cols-2">@forelse ($student->projects as $project)<div class="rounded-xl border border-slate-100 p-4"><p class="font-semibold">{{ $project->title }}</p><p class="mt-2 text-xs leading-5 text-slate-500">{{ $project->description }}</p><div class="mt-3 flex flex-wrap gap-2">@foreach ($project->skills as $skill)<span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] text-slate-600">{{ $skill->name }}</span>@endforeach</div><p class="mt-3 text-[10px] uppercase text-slate-400">Review: {{ $project->review_status }}</p></div>@empty<p class="text-sm text-slate-400">No projects added.</p>@endforelse</div></section>
@endsection