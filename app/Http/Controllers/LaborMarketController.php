<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeJobPosting;
use App\Models\JobPosting;
use App\Models\JobProfile;
use App\Models\JobSkillExtraction;
use App\Models\JobSkillRequirement;
use App\Models\Occupation;
use App\Models\Skill;
use App\Models\SkillAlias;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaborMarketController extends Controller
{
    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        return view('labor-market.index', [
            'occupations' => Occupation::orderBy('name')->get(),
            'profiles' => JobProfile::where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $organizationId))
                ->with('occupation', 'requirements.skill')->orderBy('title')->get(),
            'skills' => Skill::where('is_active', true)->orderBy('name')->get(),
            'postings' => JobPosting::where('organization_id', $organizationId)
                ->with('skillExtractions', 'aiRuns')->latest()->paginate(12),
        ]);
    }

    public function storeOccupation(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'external_code' => ['nullable', 'string', 'max:100'],
            'framework' => ['nullable', Rule::in(['ESCO', 'ONET', 'LOCAL'])],
            'framework_uri' => ['nullable', 'url', 'max:500'],
        ]);

        $slug = Str::slug($data['name']);
        Occupation::firstOrCreate(['slug' => $slug], $data + ['slug' => $slug]);

        return back()->with('success', 'Occupation saved.');
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'occupation_id' => ['nullable', 'integer', 'exists:occupations,id'],
            'title' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:100'],
            'seniority' => ['required', Rule::in(['intern', 'entry', 'junior', 'mid', 'senior', 'lead'])],
            'country' => ['nullable', 'string', 'size:2'],
            'description' => ['nullable', 'string', 'max:10000'],
        ]);

        JobProfile::create($data + [
            'organization_id' => $request->user()->organization_id,
            'country' => $data['country'] ?? 'EG',
            'source_type' => 'curated',
        ]);

        return back()->with('success', 'Job profile created.');
    }

    public function storeRequirement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_profile_id' => ['required', 'integer', 'exists:job_profiles,id'],
            'skill_id' => ['required', 'integer', 'exists:skills,id'],
            'required_level' => ['required', 'integer', 'between:1,5'],
            'importance_weight' => ['required', 'numeric', 'between:1,5'],
            'is_required' => ['required', 'boolean'],
        ]);

        $profile = JobProfile::whereKey($data['job_profile_id'])
            ->where('organization_id', $request->user()->organization_id)->firstOrFail();

        JobSkillRequirement::updateOrCreate(
            ['job_profile_id' => $profile->id, 'skill_id' => $data['skill_id']],
            [
                'required_level' => $data['required_level'],
                'importance_weight' => $data['importance_weight'],
                'is_required' => $data['is_required'],
                'evidence_source' => 'expert_defined',
            ],
        );

        return back()->with('success', 'Job skill requirement saved.');
    }

    public function storePosting(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:80', 'max:30000'],
            'industry' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'size:2'],
            'employment_type' => ['nullable', Rule::in(['full_time', 'part_time', 'internship', 'contract', 'remote'])],
        ]);

        $posting = JobPosting::create($data + [
            'organization_id' => $request->user()->organization_id,
            'status' => 'draft',
        ]);

        return redirect()->route('labor-market.index')->with('success', "Job posting #{$posting->id} saved. Run AI analysis when ready.");
    }

    public function analyzePosting(Request $request, JobPosting $posting): RedirectResponse
    {
        $this->authorizePosting($request, $posting);

        abort_if(! config('ai.providers.'.config('csga.ai.provider').'.key'), 503, 'Configure the AI provider API key before analyzing job descriptions.');

        AnalyzeJobPosting::dispatch($posting->id, $request->user()->id);

        return back()->with('success', 'Job description analysis has been queued.');
    }

    public function approvePosting(Request $request, JobPosting $posting): RedirectResponse
    {
        $this->authorizePosting($request, $posting);

        $data = $request->validate([
            'extraction_ids' => ['required', 'array', 'min:1'],
            'extraction_ids.*' => ['required', 'integer', 'distinct', 'exists:job_skill_extractions,id'],
        ]);

        DB::transaction(function () use ($request, $posting, $data): void {
            $extractions = JobSkillExtraction::where('job_posting_id', $posting->id)
                ->whereIn('id', $data['extraction_ids'])
                ->where('review_status', 'pending')->get();

            abort_unless($extractions->count() === count($data['extraction_ids']), 422, 'One or more skill suggestions cannot be reviewed.');

            $occupation = Occupation::firstOrCreate(
                ['slug' => Str::slug($posting->title)],
                ['name' => $posting->title, 'framework' => 'LOCAL'],
            );

            $profile = $posting->jobProfile;

            if (! $profile || $profile->organization_id !== $request->user()->organization_id) {
                $profile = JobProfile::create([
                    'organization_id' => $request->user()->organization_id,
                    'occupation_id' => $occupation->id,
                    'title' => $posting->title,
                    'industry' => $posting->industry,
                    'seniority' => 'entry',
                    'country' => $posting->country,
                    'description' => $posting->description,
                    'source_type' => 'job_posting',
                    'source_reference' => (string) $posting->id,
                ]);
            }

            foreach ($extractions as $extraction) {
                $name = trim($extraction->suggested_name);
                $skill = Skill::firstOrCreate(['slug' => Str::slug($name)], [
                    'name' => $name,
                    'skill_type' => 'technical',
                    'is_active' => true,
                ]);

                SkillAlias::firstOrCreate(
                    ['normalized_alias' => Str::lower($name)],
                    ['skill_id' => $skill->id, 'alias' => $name],
                );

                JobSkillRequirement::updateOrCreate(
                    ['job_profile_id' => $profile->id, 'skill_id' => $skill->id],
                    [
                        'required_level' => $extraction->required_level,
                        'importance_weight' => $extraction->importance_weight,
                        'is_required' => $extraction->is_required,
                        'evidence_source' => 'ai_reviewed',
                    ],
                );

                $extraction->update([
                    'skill_id' => $skill->id,
                    'review_status' => 'approved',
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            }

            $posting->update(['job_profile_id' => $profile->id, 'status' => 'approved']);
        });

        return back()->with('success', 'Selected job skills were approved and added to a job profile.');
    }

    private function authorizePosting(Request $request, JobPosting $posting): void
    {
        abort_unless($posting->organization_id === $request->user()->organization_id, 404);
    }
}