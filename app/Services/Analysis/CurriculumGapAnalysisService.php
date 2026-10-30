<?php

namespace App\Services\Analysis;

use App\Models\AnalysisRun;
use App\Models\CurriculumVersion;
use App\Models\JobProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CurriculumGapAnalysisService
{
    public function __construct(private readonly GapScoreCalculator $calculator) {}

    public function analyzeCurriculum(CurriculumVersion $curriculum, JobProfile $jobProfile, User $actor): AnalysisRun
    {
        $curriculum->loadMissing('program.department', 'curriculumCourses.course.skillMappings');
        $jobProfile->loadMissing('requirements.skill');

        $organizationId = $curriculum->program->department->organization_id;

        if ($jobProfile->organization_id !== null && $jobProfile->organization_id !== $organizationId) {
            throw new InvalidArgumentException('The job profile does not belong to this organization.');
        }

        $courseRows = $curriculum->curriculumCourses;
        $totalCourseWeight = $courseRows->sum(fn ($row) => $this->courseWeight($row));

        if ($totalCourseWeight <= 0) {
            throw new InvalidArgumentException('The selected curriculum has no courses to analyze.');
        }

        $coverageBySkill = [];
        foreach ($courseRows as $row) {
            $courseWeight = $this->courseWeight($row);
            foreach ($row->course->skillMappings->where('is_approved', true) as $mapping) {
                $coverageBySkill[$mapping->skill_id] = ($coverageBySkill[$mapping->skill_id] ?? 0)
                    + ((float) $mapping->coverage_level * $courseWeight);
            }
        }

        return DB::transaction(function () use ($curriculum, $jobProfile, $actor, $organizationId, $courseRows, $totalCourseWeight, $coverageBySkill): AnalysisRun {
            $requirements = $jobProfile->requirements;
            $scoreItems = [];
            $details = [];

            foreach ($requirements as $requirement) {
                $coverage = round(($coverageBySkill[$requirement->skill_id] ?? 0) / $totalCourseWeight, 1);
                $required = (int) $requirement->required_level;
                $weight = (float) $requirement->importance_weight;

                $scoreItems[] = [
                    'required_level' => $required,
                    'current_level' => $coverage,
                    'weight' => $weight,
                ];

                $details[] = [
                    'skill_id' => $requirement->skill_id,
                    'required_level' => $required,
                    'current_level' => $coverage,
                    'gap_score' => $this->calculator->skillGap($required, $coverage),
                    'importance_weight' => $weight,
                    'alignment_contribution' => $this->calculator->alignmentContribution($required, $coverage),
                    'result_status' => $this->calculator->resultStatus($required, $coverage),
                    'evidence' => $this->coverageEvidence($courseRows, $requirement->skill_id),
                ];
            }

            if ($requirements->isEmpty()) {
                throw new InvalidArgumentException('The selected job profile has no skill requirements.');
            }

            $alignment = $this->calculator->weightedAlignment($scoreItems);
            $run = AnalysisRun::create([
                'organization_id' => $organizationId,
                'analysis_type' => 'curriculum',
                'curriculum_version_id' => $curriculum->id,
                'job_profile_id' => $jobProfile->id,
                'requested_by' => $actor->id,
                'status' => 'completed',
                'alignment_score' => $alignment,
                'assessed_coverage' => 100,
                'input_snapshot' => [
                    'curriculum_version' => $curriculum->only(['id', 'version', 'academic_year']),
                    'job_profile' => $jobProfile->only(['id', 'title', 'country', 'seniority']),
                    'course_count' => $courseRows->count(),
                    'course_weight' => $totalCourseWeight,
                    'requirements' => $requirements->map(fn ($item) => [
                        'skill_id' => $item->skill_id,
                        'skill_name' => $item->skill?->name,
                        'required_level' => $item->required_level,
                        'weight' => $item->importance_weight,
                    ])->values()->all(),
                ],
                'completed_at' => now(),
            ]);

            $run->skillResults()->createMany($details);
            $this->createRecommendations($run, $details, 'curriculum');

            return $run->load(['skillResults.skill', 'recommendations']);
        });
    }

    public function analyzeStudent(StudentProfile $student, JobProfile $jobProfile, User $actor): AnalysisRun
    {
        $student->loadMissing('organization', 'user');
        $jobProfile->loadMissing('requirements.skill');

        if ($jobProfile->organization_id !== null && $jobProfile->organization_id !== $student->organization_id) {
            throw new InvalidArgumentException('The job profile does not belong to this student organization.');
        }

        $latestVerified = $student->assessments()
            ->where('is_verified', true)
            ->orderByDesc('assessed_at')
            ->get()
            ->unique('skill_id')
            ->keyBy('skill_id');

        return DB::transaction(function () use ($student, $jobProfile, $actor, $latestVerified): AnalysisRun {
            $requirements = $jobProfile->requirements;
            $scoreItems = [];
            $details = [];
            $totalWeight = 0.0;
            $assessedWeight = 0.0;

            foreach ($requirements as $requirement) {
                $required = (int) $requirement->required_level;
                $weight = (float) $requirement->importance_weight;
                $assessment = $latestVerified->get($requirement->skill_id);
                $current = $assessment ? (float) $assessment->level : null;
                $totalWeight += $weight;

                if ($current !== null) {
                    $assessedWeight += $weight;
                }

                $scoreItems[] = ['required_level' => $required, 'current_level' => $current, 'weight' => $weight];
                $details[] = [
                    'skill_id' => $requirement->skill_id,
                    'required_level' => $required,
                    'current_level' => $current,
                    'gap_score' => $this->calculator->skillGap($required, $current),
                    'importance_weight' => $weight,
                    'alignment_contribution' => $this->calculator->alignmentContribution($required, $current),
                    'result_status' => $this->calculator->resultStatus($required, $current),
                    'evidence' => $assessment?->evidence,
                ];
            }

            if ($requirements->isEmpty()) {
                throw new InvalidArgumentException('The selected job profile has no skill requirements.');
            }

            $alignment = $this->calculator->weightedAlignment($scoreItems);
            $coverage = $totalWeight > 0 ? round(($assessedWeight / $totalWeight) * 100, 2) : 0;

            $run = AnalysisRun::create([
                'organization_id' => $student->organization_id,
                'analysis_type' => 'student',
                'student_profile_id' => $student->id,
                'job_profile_id' => $jobProfile->id,
                'requested_by' => $actor->id,
                'status' => 'completed',
                'alignment_score' => $alignment,
                'assessed_coverage' => $coverage,
                'input_snapshot' => [
                    'student_profile_id' => $student->id,
                    'job_profile' => $jobProfile->only(['id', 'title', 'country', 'seniority']),
                    'assessed_skill_count' => count(array_filter($details, fn ($item) => $item['current_level'] !== null)),
                    'requirements' => $requirements->map(fn ($item) => [
                        'skill_id' => $item->skill_id,
                        'skill_name' => $item->skill?->name,
                        'required_level' => $item->required_level,
                        'weight' => $item->importance_weight,
                    ])->values()->all(),
                ],
                'completed_at' => now(),
            ]);

            $run->skillResults()->createMany($details);
            $this->createRecommendations($run, $details, 'student');

            return $run->load(['skillResults.skill', 'recommendations']);
        });
    }

    private function courseWeight(object $curriculumCourse): float
    {
        $creditHours = (float) $curriculumCourse->course->credit_hours;

        return $creditHours * ($curriculumCourse->is_required
            ? 1
            : (float) config('csga.analysis.elective_course_weight', 0.65));
    }

    private function coverageEvidence(Collection $courseRows, int $skillId): ?string
    {
        return $courseRows
            ->flatMap(fn ($row) => $row->course->skillMappings
                ->where('skill_id', $skillId)
                ->where('is_approved', true)
                ->map(fn ($mapping) => $row->course->code.': '.$mapping->evidence))
            ->filter()
            ->take(5)
            ->implode("\n") ?: null;
    }

    private function createRecommendations(AnalysisRun $run, array $details, string $type): void
    {
        foreach ($details as $detail) {
            $gap = $detail['gap_score'];

            if ($gap === null || $gap < 20) {
                continue;
            }

            $skillName = $run->skillResults()
                ->where('skill_id', $detail['skill_id'])
                ->with('skill:id,name')
                ->first()?->skill?->name ?? 'this skill';

            $priority = match (true) {
                $gap >= 60 => 'high',
                $gap >= 35 => 'medium',
                default => 'low',
            };

            $run->recommendations()->create([
                'skill_id' => $detail['skill_id'],
                'recommendation_type' => $type === 'curriculum' ? 'curriculum_improvement' : 'skill_development',
                'title' => $type === 'curriculum'
                    ? "Increase curriculum coverage for {$skillName}"
                    : "Develop {$skillName}",
                'description' => $type === 'curriculum'
                    ? "The approved curriculum mapping indicates a {$gap}% gap. Add measurable learning outcomes, practical activities, and assessment evidence for {$skillName}."
                    : "The verified student assessment indicates a {$gap}% gap. Complete a practical learning activity and submit evidence for {$skillName}.",
                'priority' => $priority,
                'source' => 'rules',
            ]);
        }
    }
}