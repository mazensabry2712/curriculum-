<?php

namespace App\Http\Controllers;

use App\Models\AnalysisRun;
use App\Models\CurriculumVersion;
use App\Models\JobProfile;
use App\Models\StudentProfile;
use App\Services\Analysis\CurriculumGapAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalysisController extends Controller
{
    public function index(Request $request): View
    {
        $runs = AnalysisRun::query()
            ->where('organization_id', $request->user()->organization_id)
            ->with(['jobProfile', 'curriculumVersion.program', 'studentProfile.user'])
            ->latest()
            ->paginate(15);

        return view('analysis.index', [
            'runs' => $runs,
            'curricula' => CurriculumVersion::query()->whereHas('program.department', fn ($q) => $q->where('organization_id', $request->user()->organization_id))->orderByDesc('id')->get(),
            'students' => StudentProfile::query()->where('organization_id', $request->user()->organization_id)->with('user')->get(),
            'jobProfiles' => JobProfile::query()->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $request->user()->organization_id))->where('is_active', true)->orderBy('title')->get(),
        ]);
    }

    public function runCurriculum(Request $request, CurriculumGapAnalysisService $service): RedirectResponse
    {
        $data = $request->validate([
            'curriculum_version_id' => ['required', 'integer', 'exists:curriculum_versions,id'],
            'job_profile_id' => ['required', 'integer', 'exists:job_profiles,id'],
        ]);

        $curriculum = CurriculumVersion::query()
            ->whereKey($data['curriculum_version_id'])
            ->whereHas('program.department', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->firstOrFail();

        $profile = JobProfile::query()
            ->whereKey($data['job_profile_id'])
            ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $request->user()->organization_id))
            ->firstOrFail();

        $run = $service->analyzeCurriculum($curriculum, $profile, $request->user());

        return redirect()->route('analysis.show', $run)->with('success', 'Curriculum analysis completed.');
    }

    public function runStudent(Request $request, CurriculumGapAnalysisService $service): RedirectResponse
    {
        $data = $request->validate([
            'student_profile_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'job_profile_id' => ['required', 'integer', 'exists:job_profiles,id'],
        ]);

        $student = StudentProfile::query()
            ->whereKey($data['student_profile_id'])
            ->where('organization_id', $request->user()->organization_id)
            ->firstOrFail();

        $profile = JobProfile::query()
            ->whereKey($data['job_profile_id'])
            ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $request->user()->organization_id))
            ->firstOrFail();

        $run = $service->analyzeStudent($student, $profile, $request->user());

        return redirect()->route('analysis.show', $run)->with('success', 'Student analysis completed.');
    }

    public function show(Request $request, AnalysisRun $analysisRun): View
    {
        if ($request->user()->hasAnyRole(['student', 'graduate'])) {
            abort_unless($analysisRun->student_profile_id === $request->user()->studentProfile?->id, 404);
        } else {
            abort_unless($analysisRun->organization_id === $request->user()->organization_id, 404);
        }

        return view('analysis.show', [
            'run' => $analysisRun->load(['jobProfile', 'curriculumVersion.program', 'studentProfile.user', 'skillResults.skill', 'recommendations.skill']),
        ]);
    }
}