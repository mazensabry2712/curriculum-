<?php

namespace App\Http\Controllers;

use App\Models\JobProfile;
use App\Models\JobSkillRequirement;
use App\Models\Occupation;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $slug = \Illuminate\Support\Str::slug($data['name']);
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
}