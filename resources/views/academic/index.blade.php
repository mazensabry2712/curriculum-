@extends('layouts.app')

@section('title', 'Academic structure')
@section('eyebrow', 'Programs, courses and curriculum versions')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">Academic structure</h1><p class="mt-2 text-sm text-slate-500">Build versioned programs and course catalogs before measuring skill coverage.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $departments->count() }} departments</span></div>
<div class="mt-7 grid gap-5 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Add department</h2><form method="POST" action="{{ route('academic.departments.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
            <input name="name" required maxlength="160" placeholder="Department name" class="field">
            <input name="code" required maxlength="40" placeholder="Code (e.g. CS)" class="field">
            <textarea name="description" rows="2" placeholder="Description (optional)" class="field sm:col-span-2"></textarea>
            <button class="action sm:col-span-2">Create department</button>
        </form>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Add academic program</h2><form method="POST" action="{{ route('academic.programs.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
            <select name="department_id" required class="field sm:col-span-2"><option value="">Select department</option>@foreach ($departments as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select>
            <input name="name" required placeholder="Program name" class="field">
            <input name="code" required placeholder="Program code" class="field">
            <select name="degree_type" class="field"><option value="bachelor">Bachelor</option><option value="diploma">Diploma</option><option value="master">Master</option><option value="phd">PhD</option><option value="certificate">Certificate</option></select>
            <input name="duration_years" type="number" min="1" max="10" value="4" required class="field">
            <button class="action sm:col-span-2">Create program</button>
        </form>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Add course</h2><form method="POST" action="{{ route('academic.courses.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf
            <input name="code" required placeholder="Course code" class="field">
            <input name="name" required placeholder="Course name" class="field">
            <input name="credit_hours" type="number" min=".5" max="30" step=".5" value="3" required class="field">
            <input name="course_level" placeholder="Level (e.g. undergraduate)" class="field">
            <select name="language" class="field"><option value="en">English</option><option value="ar">Arabic</option></select>
            <button class="action">Add course</button>
            <textarea name="description" rows="2" placeholder="Course description" class="field sm:col-span-2"></textarea>
        </form>
    </section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-semibold">Create curriculum version</h2><form method="POST" action="{{ route('academic.curricula.store') }}" class="mt-4 space-y-3">@csrf
            <select name="program_id" required class="field"><option value="">Select program</option>@foreach ($programs as $program)<option value="{{ $program->id }}">{{ $program->name }} · {{ $program->department->name }}</option>@endforeach</select>
            <div class="grid grid-cols-2 gap-3"><input name="version" required placeholder="Version (2026)" class="field"><input name="academic_year" placeholder="Academic year" class="field"></div>
            <label class="block text-xs font-semibold text-slate-500">Select courses in this version</label>
            <div class="max-h-40 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3">@forelse ($courses as $course)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="course_ids[]" value="{{ $course->id }}" class="rounded border-slate-300 text-cyan-700"><span>{{ $course->code }} · {{ $course->name }}</span></label>@empty<p class="text-xs text-slate-400">Create courses first.</p>@endforelse</div>
            <button class="action w-full">Create draft version</button>
        </form>
    </section>
</div>
<div class="mt-7 grid gap-5 xl:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Departments & programs</h2><div class="mt-4 divide-y divide-slate-100">@forelse ($departments as $department)<div class="py-3"><div class="flex justify-between gap-3"><b class="text-sm">{{ $department->name }}</b><span class="text-xs text-slate-400">{{ $department->code }}</span></div>@foreach ($department->programs as $program)<p class="mt-2 pl-3 text-xs text-slate-500">↳ {{ $program->name }} ({{ $program->code }})</p>@endforeach</div>@empty<p class="py-8 text-center text-sm text-slate-400">No departments yet.</p>@endforelse</div></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Curriculum versions</h2><div class="mt-4 divide-y divide-slate-100">@forelse ($curricula as $version)<div class="flex items-center justify-between gap-3 py-3"><div><p class="text-sm font-semibold">{{ $version->program->name }} · {{ $version->version }}</p><p class="mt-1 text-xs text-slate-500">{{ $version->academic_year ?? 'Academic year not set' }}</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($version->status) }}</span></div>@empty<p class="py-8 text-center text-sm text-slate-400">No curriculum versions yet.</p>@endforelse</div></section>
</div>
@endsection