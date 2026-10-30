<?php

namespace App\Http\Controllers;

use App\Models\JobProfile;
use App\Models\Skill;
use App\Models\StudentProject;
use App\Models\StudentSkillAssessment;
use App\Models\StudentTarget;
use App\Services\Analysis\CurriculumGapAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function show(Request $request): View
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 404, 'Student profile has not been created yet.');

        return view('student-portal.index', [
            'student' => $student->load(['program', 'assessments.skill', 'targets.jobProfile', 'projects.skills']),
            'skills' => Skill::where('is_active', true)->orderBy('name')->get(),
            'jobProfiles' => JobProfile::where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $student->organization_id))
                ->where('is_active', true)->orderBy('title')->get(),
        ]);
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 404);

        $data = $request->validate([
            'skill_id' => ['required', 'integer', 'exists:skills,id'],
            'level' => ['required', 'integer', 'between:1,5'],
            'evidence' => ['nullable', 'string', 'max:4000'],
        ]);

        StudentSkillAssessment::create([
            'student_profile_id' => $student->id,
            'skill_id' => $data['skill_id'],
            'level' => $data['level'],
            'assessment_type' => 'self',
            'evidence' => $data['evidence'] ?? null,
            'confidence' => 0.4,
            'is_verified' => false,
            'assessed_by' => $request->user()->id,
            'assessed_at' => now(),
        ]);

        return back()->with('success', 'Self-assessment saved. It will be used in verified analysis after review.');
    }

    public function storeTarget(Request $request): RedirectResponse
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 404);

        $data = $request->validate([
            'job_profile_id' => ['required', 'integer', 'exists:job_profiles,id'],
        ]);

        JobProfile::whereKey($data['job_profile_id'])
            ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $student->organization_id))
            ->firstOrFail();

        DB::transaction(function () use ($student, $data): void {
            $student->targets()->update(['is_primary' => false]);
            StudentTarget::updateOrCreate(
                ['student_profile_id' => $student->id, 'job_profile_id' => $data['job_profile_id']],
                ['is_primary' => true],
            );
        });

        return back()->with('success', 'Career target updated.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'repository_url' => ['nullable', 'url', 'max:500'],
            'project_url' => ['nullable', 'url', 'max:500'],
            'completed_at' => ['nullable', 'date', 'before_or_equal:today'],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'distinct', 'exists:skills,id'],
        ]);

        $project = StudentProject::create([
            'student_profile_id' => $student->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'repository_url' => $data['repository_url'] ?? null,
            'project_url' => $data['project_url'] ?? null,
            'completed_at' => $data['completed_at'] ?? null,
            'review_status' => 'pending',
        ]);

        $project->skills()->syncWithPivotValues($data['skill_ids'] ?? [], ['evidence' => $data['description'] ?? null]);

        return back()->with('success', 'Project evidence submitted for review.');
    }

    public function analyze(Request $request, CurriculumGapAnalysisService $service): RedirectResponse
    {
        $student = $request->user()->studentProfile;
        abort_unless($student, 404);

        $target = $student->targets()->where('is_primary', true)->firstOrFail();
        $profile = JobProfile::whereKey($target->job_profile_id)
            ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $student->organization_id))
            ->firstOrFail();

        $run = $service->analyzeStudent($student, $profile, $request->user());

        return redirect()->route('analysis.show', $run);
    }
}