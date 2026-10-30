@extends('layouts.app')

@section('title', 'Students')
@section('eyebrow', 'Student records and evidence')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">Student intelligence</h1><p class="mt-2 text-sm text-slate-500">Manage student accounts and verify skill evidence before career analysis.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $students->total() }} students</span></div>
<div class="mt-7 grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Create student account</h2><form method="POST" action="{{ route('students.store') }}" class="mt-4 space-y-3">@csrf
        <input name="name" required placeholder="Student full name" class="field"><input name="email" required type="email" placeholder="Student email" class="field">
        <select name="program_id" class="field"><option value="">Select program (optional)</option>@foreach ($programs as $program)<option value="{{ $program->id }}">{{ $program->name }}</option>@endforeach</select>
        <div class="grid grid-cols-2 gap-3"><input name="student_number" placeholder="Student number" class="field"><input name="study_year" type="number" min="1" max="10" placeholder="Study year" class="field"></div>
        <div class="grid grid-cols-2 gap-3"><input type="password" name="password" required minlength="10" placeholder="Temporary password" class="field"><input type="password" name="password_confirmation" required minlength="10" placeholder="Confirm password" class="field"></div>
        <button class="action w-full">Create student</button>
    </form></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Record an instructor assessment</h2><form method="POST" action="{{ route('students.assessments.store') }}" class="mt-4 space-y-3">@csrf
        <select name="student_profile_id" required class="field"><option value="">Select student</option>@foreach ($students as $student)<option value="{{ $student->id }}">{{ $student->user->name }} · {{ $student->student_number }}</option>@endforeach</select>
        <select name="skill_id" required class="field"><option value="">Select skill</option>@foreach ($skills as $skill)<option value="{{ $skill->id }}">{{ $skill->name }}</option>@endforeach</select>
        <div class="grid grid-cols-2 gap-3"><select name="level" required class="field">@for ($i=1;$i<=5;$i++)<option value="{{ $i }}">Level {{ $i }}</option>@endfor</select><select name="assessment_type" required class="field"><option value="instructor">Instructor</option><option value="exam">Practical exam</option><option value="project">Project</option><option value="certificate">Certificate</option></select></div>
        <textarea name="evidence" rows="2" placeholder="Evidence: rubric, exam result, observed task..." class="field"></textarea><button class="action w-full">Save unverified assessment</button>
    </form><p class="mt-3 text-xs text-slate-500">New assessments are pending until explicitly verified.</p></section>
</div>
<section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="flex items-center justify-between"><h2 class="font-semibold">Students & assessment history</h2><span class="text-xs text-slate-400">Organization-scoped</span></div><div class="mt-4 divide-y divide-slate-100">@forelse ($students as $student)<div class="py-4"><div class="flex flex-wrap items-center justify-between gap-3"><div><p class="text-sm font-semibold">{{ $student->user->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $student->user->email }} · {{ $student->student_number ?? 'No student number' }} · {{ $student->program?->name ?? 'No program' }}</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600">{{ $student->assessments_count ?? $student->assessments()->count() }} assessments</span></div>
        @php($pending = $student->assessments()->where('is_verified',false)->with('skill')->latest('assessed_at')->limit(4)->get())
        @if ($pending->isNotEmpty())<div class="mt-3 flex flex-wrap gap-2">@foreach ($pending as $assessment)<form method="POST" action="{{ route('students.assessments.verify',$assessment) }}" class="flex items-center gap-2 rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs text-amber-900">@csrf<span>{{ $assessment->skill->name }} · L{{ $assessment->level }}</span><button class="font-bold underline">Verify</button></form>@endforeach</div>@endif
    </div>@empty<p class="py-10 text-center text-sm text-slate-400">No student records yet.</p>@endforelse</div><div class="mt-4">{{ $students->links() }}</div></section>
@endsection