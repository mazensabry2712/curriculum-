<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeSyllabusDocument;
use App\Models\Course;
use App\Models\Document;
use App\Models\DocumentSkillSuggestion;
use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\SkillCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        return view('documents.index', [
            'documents' => Document::where('organization_id', $organizationId)->with(['course', 'uploader'])->latest()->paginate(20),
            'courses' => Course::where('organization_id', $organizationId)->orderBy('code')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $maxKb = (int) config('csga.documents.max_kb', 15360);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:180'],
            'document' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', "max:{$maxKb}"],
        ]);

        $course = Course::whereKey($data['course_id'])
            ->where('organization_id', $request->user()->organization_id)->firstOrFail();

        $file = $data['document'];
        $sha = hash_file('sha256', $file->getRealPath());

        $existing = Document::where('organization_id', $request->user()->organization_id)
            ->where('sha256', $sha)->first();

        if ($existing) {
            return redirect()->route('documents.show', $existing)->with('info', 'This exact PDF has already been uploaded.');
        }

        $disk = config('csga.documents.private_disk', 'local');
        $path = $file->store("organizations/{$request->user()->organization_id}/syllabi", $disk);

        $document = Document::create([
            'organization_id' => $request->user()->organization_id,
            'course_id' => $course->id,
            'uploaded_by' => $request->user()->id,
            'title' => $data['title'],
            'original_name' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size_bytes' => $file->getSize(),
            'sha256' => $sha,
            'status' => 'uploaded',
        ]);

        AnalyzeSyllabusDocument::dispatch($document->id);

        return redirect()->route('documents.show', $document)->with('success', 'PDF uploaded. AI analysis has been queued.');
    }

    public function show(Request $request, Document $document): View
    {
        $this->authorizeDocument($request, $document);

        return view('documents.show', [
            'document' => $document->load(['course', 'aiRuns.skillSuggestions.skill']),
            'latestRun' => $document->aiRuns()->with('skillSuggestions.skill')->latest()->first(),
        ]);
    }

    public function approve(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $data = $request->validate([
            'suggestion_ids' => ['required', 'array', 'min:1'],
            'suggestion_ids.*' => ['required', 'integer', 'distinct', 'exists:document_skill_suggestions,id'],
        ]);

        abort_unless($document->course, 422, 'This document is not linked to a course.');

        DB::transaction(function () use ($document, $data, $request): void {
            $suggestions = DocumentSkillSuggestion::whereIn('id', $data['suggestion_ids'])
                ->whereHas('aiRun', fn ($q) => $q->where('document_id', $document->id))
                ->where('review_status', 'pending')->get();

            abort_unless($suggestions->count() === count($data['suggestion_ids']), 422, 'One or more suggestions cannot be reviewed.');

            foreach ($suggestions as $suggestion) {
                $name = trim($suggestion->suggested_name);
                $slug = Str::slug($name);
                $skill = Skill::firstOrCreate(['slug' => $slug], [
                    'name' => $name,
                    'skill_type' => 'technical',
                    'is_active' => true,
                ]);

                SkillAlias::firstOrCreate([
                    'normalized_alias' => Str::lower($name),
                ], [
                    'skill_id' => $skill->id,
                    'alias' => $name,
                ]);

                $document->course->skillMappings()->updateOrCreate(
                    ['skill_id' => $skill->id],
                    [
                        'coverage_level' => $suggestion->suggested_level,
                        'instructional_hours' => 0,
                        'evidence' => $suggestion->evidence,
                        'confidence' => $suggestion->confidence,
                        'mapping_source' => 'ai_reviewed',
                        'is_approved' => true,
                        'reviewed_by' => $request->user()->id,
                        'reviewed_at' => now(),
                    ],
                );

                $suggestion->update([
                    'skill_id' => $skill->id,
                    'review_status' => 'approved',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            }

            $latestRun = $document->aiRuns()->latest()->first();
            foreach (($latestRun?->output['learning_outcomes'] ?? []) as $index => $outcome) {
                if (empty($outcome['code']) || empty($outcome['description'])) {
                    continue;
                }

                $document->course->outcomes()->updateOrCreate(
                    ['code' => Str::limit($outcome['code'], 30, '')],
                    [
                        'description' => $outcome['description'],
                        'assessment_method' => 'syllabus',
                        'weight' => 1,
                        'position' => $index + 1,
                    ],
                );
            }

            if (! $document->aiRuns()->whereHas('skillSuggestions', fn ($q) => $q->where('review_status', 'pending'))->exists()) {
                $document->update(['status' => 'approved']);
            }
        });

        return back()->with('success', 'Selected AI suggestions were reviewed and added to the course curriculum mapping.');
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        $data = $request->validate([
            'suggestion_ids' => ['required', 'array', 'min:1'],
            'suggestion_ids.*' => ['required', 'integer', 'distinct', 'exists:document_skill_suggestions,id'],
        ]);

        $suggestions = DocumentSkillSuggestion::whereIn('id', $data['suggestion_ids'])
            ->whereHas('aiRun', fn ($q) => $q->where('document_id', $document->id))
            ->where('review_status', 'pending')->get();

        foreach ($suggestions as $suggestion) {
            $suggestion->update([
                'review_status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);
        }

        if (! $document->aiRuns()->whereHas('skillSuggestions', fn ($q) => $q->where('review_status', 'pending'))->exists()) {
            $document->update(['status' => 'reviewed']);
        }

        return back()->with('success', 'Selected suggestions were rejected.');
    }

    private function authorizeDocument(Request $request, Document $document): void
    {
        abort_unless($document->organization_id === $request->user()->organization_id, 404);
    }
}