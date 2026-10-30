@extends('layouts.app')

@section('title', 'Labor market')
@section('eyebrow', 'Occupations, job profiles and demand signals')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">Labor market intelligence</h1><p class="mt-2 text-sm text-slate-500">Convert job descriptions into reviewed, weighted skill requirements.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $profiles->count() }} profiles</span></div>
<div class="mt-7 grid gap-5 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Create occupation</h2><form method="POST" action="{{ route('labor-market.occupations.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
        <input name="name" required placeholder="Occupation name" class="field"><input name="external_code" placeholder="ESCO / O*NET code" class="field">
        <select name="framework" class="field"><option value="LOCAL">Local</option><option value="ESCO">ESCO</option><option value="ONET">O*NET</option></select><input name="framework_uri" type="url" placeholder="External URI" class="field">
        <textarea name="description" rows="2" placeholder="Occupation overview" class="field sm:col-span-2"></textarea><button class="action sm:col-span-2">Save occupation</button>
    </form></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Create job profile</h2><form method="POST" action="{{ route('labor-market.profiles.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
        <input name="title" required placeholder="Job title" class="field"><select name="occupation_id" class="field"><option value="">Link occupation (optional)</option>@foreach ($occupations as $occupation)<option value="{{ $occupation->id }}">{{ $occupation->name }}</option>@endforeach</select>
        <input name="industry" placeholder="Industry" class="field"><select name="seniority" class="field"><option value="intern">Intern</option><option value="entry">Entry</option><option value="junior">Junior</option><option value="mid">Mid-level</option><option value="senior">Senior</option><option value="lead">Lead</option></select>
        <input name="country" value="EG" maxlength="2" class="field"><textarea name="description" rows="2" placeholder="Profile description" class="field"></textarea>
        <button class="action sm:col-span-2">Create profile</button>
    </form></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Add a skill requirement</h2><form method="POST" action="{{ route('labor-market.requirements.store') }}" class="mt-4 space-y-3">@csrf
        <select name="job_profile_id" required class="field"><option value="">Select organization profile</option>@foreach ($profiles->where('organization_id', auth()->user()->organization_id) as $profile)<option value="{{ $profile->id }}">{{ $profile->title }}</option>@endforeach</select>
        <select name="skill_id" required class="field"><option value="">Select skill</option>@foreach ($skills as $skill)<option value="{{ $skill->id }}">{{ $skill->name }}</option>@endforeach</select>
        <div class="grid grid-cols-3 gap-3"><select name="required_level" class="field">@for ($i=1;$i<=5;$i++)<option value="{{ $i }}">Level {{ $i }}</option>@endfor</select><select name="importance_weight" class="field">@for ($i=1;$i<=5;$i++)<option value="{{ $i }}">Weight {{ $i }}</option>@endfor</select><select name="is_required" class="field"><option value="1">Required</option><option value="0">Preferred</option></select></div>
        <button class="action w-full">Save requirement</button>
    </form></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Analyze a job description with AI</h2><form method="POST" action="{{ route('labor-market.postings.store') }}" class="mt-4 space-y-3">@csrf
        <input name="title" required placeholder="Job title" class="field"><textarea name="description" required minlength="80" rows="5" placeholder="Paste the complete job description (at least 80 characters)" class="field"></textarea>
        <div class="grid grid-cols-3 gap-3"><input name="industry" placeholder="Industry" class="field"><input name="location" placeholder="Location" class="field"><input name="country" value="EG" maxlength="2" required class="field"></div>
        <select name="employment_type" class="field"><option value="">Employment type</option><option value="full_time">Full time</option><option value="part_time">Part time</option><option value="internship">Internship</option><option value="contract">Contract</option><option value="remote">Remote</option></select>
        <button class="action w-full">Save job posting</button>
    </form><p class="mt-3 text-xs leading-5 text-slate-500">After saving, run AI analysis below. Extracted skills stay pending until a reviewer approves them.</p></section>
</div>
<section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h2 class="font-semibold">Job posting analysis queue</h2><span class="text-xs text-slate-400">{{ $postings->total() }} postings</span></div>
    <div class="mt-4 divide-y divide-slate-100">@forelse ($postings as $posting)<div class="py-5">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="font-semibold">{{ $posting->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $posting->industry ?? 'Industry not set' }} · {{ $posting->country }} · {{ $posting->created_at->format('d M Y') }}</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ str_replace('_',' ',ucfirst($posting->status)) }}</span></div>
        <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-500">{{ $posting->description }}</p>
        <div class="mt-3 flex flex-wrap items-center gap-2">@if (in_array($posting->status,['draft','failed'],true))<form method="POST" action="{{ route('labor-market.postings.analyze',$posting) }}">@csrf<button class="rounded-lg bg-cyan-50 px-3 py-2 text-xs font-semibold text-cyan-800 hover:bg-cyan-100">Run AI extraction</button></form>@endif
            @if ($posting->skillExtractions->where('review_status','pending')->isNotEmpty())<form method="POST" action="{{ route('labor-market.postings.approve',$posting) }}" class="flex flex-wrap items-center gap-2">@csrf@foreach ($posting->skillExtractions->where('review_status','pending') as $item)<label class="flex items-center gap-1 text-xs text-slate-600"><input type="checkbox" name="extraction_ids[]" value="{{ $item->id }}" checked class="rounded border-slate-300 text-cyan-700">{{ $item->suggested_name }} ({{ $item->required_level }}/5)</label>@endforeach<button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">Approve selected skills</button></form>@endif
        </div>
    </div>@empty<p class="py-10 text-center text-sm text-slate-400">No job postings have been submitted.</p>@endforelse</div><div class="mt-4">{{ $postings->links() }}</div>
</section>
<section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Available job profiles</h2><div class="mt-4 grid gap-3 md:grid-cols-2">@forelse ($profiles as $profile)<div class="rounded-xl border border-slate-100 p-4"><p class="font-semibold">{{ $profile->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $profile->industry ?? 'General' }} · {{ ucfirst($profile->seniority) }} · {{ $profile->country ?? 'Global' }}</p><div class="mt-3 flex flex-wrap gap-1">@foreach ($profile->requirements as $requirement)<span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] text-slate-600">{{ $requirement->skill?->name }} · L{{ $requirement->required_level }} · W{{ $requirement->importance_weight }}</span>@endforeach</div></div>@empty<p class="text-sm text-slate-400">Create a profile to start comparing.</p>@endforelse</div></section>
@endsection