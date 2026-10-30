@extends('layouts.app')

@section('title', 'Skills catalog')
@section('eyebrow', 'Normalized skill taxonomy')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">Skills catalog</h1><p class="mt-2 text-sm text-slate-500">A shared taxonomy keeps curriculum and job requirements comparable.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $skills->total() }} skills</span></div>
<div class="mt-7 grid gap-5 xl:grid-cols-[.75fr_1.25fr]">
    <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Add a skill</h2><form method="POST" action="{{ route('skills.store') }}" class="mt-4 space-y-3">@csrf
        <input name="name" required maxlength="160" placeholder="Canonical skill name" class="field">
        <select name="skill_category_id" class="field"><option value="">Choose category</option>@foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select>
        <select name="skill_type" required class="field"><option value="technical">Technical</option><option value="programming">Programming</option><option value="soft">Soft skill</option><option value="analytical">Analytical</option><option value="business">Business</option><option value="digital">Digital</option><option value="management">Management</option></select>
        <input name="framework" maxlength="30" placeholder="Framework (ESCO / O*NET / local)" class="field">
        <input name="alias" maxlength="160" placeholder="Alternative name (optional)" class="field">
        <textarea name="description" rows="3" placeholder="Definition / scope" class="field"></textarea>
        <button class="action w-full">Add to catalog</button>
    </form><div class="mt-5 rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-700">Catalog guidance</p><p class="mt-1 text-xs leading-5 text-slate-500">Use one canonical record per competency. Add spelling variants and synonyms as aliases, not duplicate skills.</p></div></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h2 class="font-semibold">Skill library</h2><span class="text-xs text-slate-400">Shared across workspaces</span></div><div class="mt-4 divide-y divide-slate-100">@forelse ($skills as $skill)<div class="flex flex-wrap items-center justify-between gap-3 py-3"><div class="min-w-0"><p class="text-sm font-semibold">{{ $skill->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $skill->category?->name ?? 'Uncategorized' }} · {{ $skill->skill_type }}</p>@if ($skill->aliases->isNotEmpty())<p class="mt-1 text-[11px] text-slate-400">Aliases: {{ $skill->aliases->pluck('alias')->join(', ') }}</p>@endif</div><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase text-slate-500">{{ $skill->framework ?? 'Local' }}</span></div>@empty<p class="py-10 text-center text-sm text-slate-400">No skills in the catalog.</p>@endforelse</div><div class="mt-4">{{ $skills->links() }}</div></section>
</div>
@endsection