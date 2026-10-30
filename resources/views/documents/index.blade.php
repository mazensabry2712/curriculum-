@extends('layouts.app')

@section('title', 'AI documents')
@section('eyebrow', 'Syllabus ingestion and human review')

@section('content')
<div class="flex flex-wrap items-end justify-between gap-3"><div><h1 class="text-3xl font-semibold tracking-tight">AI document center</h1><p class="mt-2 text-sm text-slate-500">Upload course syllabi and review AI-extracted learning outcomes and skills.</p></div><span class="rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-800">{{ $documents->total() }} documents</span></div>
<div class="mt-7 grid gap-5 lg:grid-cols-[.75fr_1.25fr]">
    <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Upload a syllabus PDF</h2><form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3">@csrf
        <input name="title" required maxlength="180" placeholder="Document title" class="field">
        <select name="course_id" required class="field"><option value="">Select course</option>@foreach ($courses as $course)<option value="{{ $course->id }}">{{ $course->code }} · {{ $course->name }}</option>@endforeach</select>
        <input type="file" name="document" required accept="application/pdf,.pdf" class="field file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-cyan-800">
        <label class="flex items-start gap-2 text-xs leading-5 text-slate-500"><input type="checkbox" name="ai_processing_consent" value="1" required class="mt-1 rounded border-slate-300 text-cyan-700"> I confirm that I am authorized to upload this syllabus and consent to sending its contents to the configured AI provider for analysis.</label>
        <button class="action w-full">Upload & queue AI analysis</button>
    </form><div class="mt-5 rounded-xl bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-700">Processing pipeline</p><ol class="mt-2 list-inside list-decimal space-y-1 text-xs leading-5 text-slate-500"><li>Private file storage and SHA-256 deduplication.</li><li>Queued document analysis with structured AI output.</li><li>Evidence and page references retained.</li><li>Instructor review before official mapping.</li></ol></div></section>
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><h2 class="font-semibold">Uploaded documents</h2><div class="mt-4 divide-y divide-slate-100">@forelse ($documents as $document)<a href="{{ route('documents.show',$document) }}" class="flex items-center justify-between gap-4 py-4 hover:bg-slate-50"><div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $document->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $document->course?->code }} · {{ $document->original_name }} · {{ number_format($document->size_bytes / 1024,0) }} KB</p><p class="mt-1 text-[11px] text-slate-400">Uploaded {{ $document->created_at->diffForHumans() }}</p></div><span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">{{ str_replace('_',' ',ucfirst($document->status)) }} →</span></a>@empty<p class="py-12 text-center text-sm text-slate-400">No PDF documents uploaded yet.</p>@endforelse</div><div class="mt-4">{{ $documents->links() }}</div></section>
</div>
@endsection