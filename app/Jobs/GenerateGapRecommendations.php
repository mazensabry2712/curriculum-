<?php

namespace App\Jobs;

use App\Ai\Agents\GapRecommendationAgent;
use App\Models\AnalysisRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;
use Throwable;

class GenerateGapRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    public function __construct(public readonly int $analysisRunId) {}

    public function handle(): void
    {
        $run = AnalysisRun::query()
            ->with(['skillResults.skill', 'jobProfile', 'curriculumVersion.program', 'studentProfile'])
            ->findOrFail($this->analysisRunId);

        $gaps = $run->skillResults
            ->filter(fn ($item) => $item->gap_score !== null && (float) $item->gap_score >= 20)
            ->map(fn ($item) => [
                'skill_id' => $item->skill_id,
                'skill_name' => $item->skill?->name,
                'required_level' => $item->required_level,
                'current_level' => $item->current_level,
                'gap_score' => $item->gap_score,
                'status' => $item->result_status,
                'evidence' => $item->evidence,
            ])->values()->all();

        if ($gaps === []) {
            return;
        }

        $scope = $run->analysis_type === 'curriculum' ? 'curriculum improvement' : 'individual student skill development';
        $payload = [
            'analysis_type' => $run->analysis_type,
            'job_title' => $run->jobProfile?->title,
            'scope' => $scope,
            'verified_skill_gaps' => $gaps,
        ];

        try {
            $response = (new GapRecommendationAgent)->prompt(
                "Create evidence-based recommendations from this verified analysis data. Return no more than one recommendation per skill.\n\n".json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                provider: config('csga.ai.provider', Lab::OpenAI),
                model: config('csga.ai.model'),
                timeout: (int) config('csga.ai.timeout', 120),
            );

            $validSkillIds = collect($gaps)->pluck('skill_id')->all();

            foreach ($response['recommendations'] as $suggestion) {
                $skillId = (int) $suggestion['skill_id'];

                if (! in_array($skillId, $validSkillIds, true)) {
                    continue;
                }

                $run->recommendations()->firstOrCreate([
                    'skill_id' => $skillId,
                    'source' => 'ai',
                    'title' => mb_substr(trim($suggestion['title']), 0, 255),
                ], [
                    'recommendation_type' => $run->analysis_type === 'curriculum' ? 'curriculum_improvement' : 'skill_development',
                    'description' => trim($suggestion['description']),
                    'priority' => in_array($suggestion['priority'], ['low', 'medium', 'high'], true) ? $suggestion['priority'] : 'medium',
                    'status' => 'pending',
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('CSGA AI recommendation generation failed.', [
                'analysis_run_id' => $run->id,
                'exception' => $exception::class,
                'message' => mb_substr($exception->getMessage(), 0, 500),
            ]);
        }
    }
}