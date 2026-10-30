<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillAlias;
use App\Models\SkillCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(): View
    {
        return view('skills.index', [
            'skills' => Skill::with('category', 'aliases')->orderBy('name')->paginate(25),
            'categories' => SkillCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'skill_category_id' => ['nullable', 'integer', 'exists:skill_categories,id'],
            'skill_type' => ['required', Rule::in(['technical', 'programming', 'soft', 'analytical', 'business', 'digital', 'management'])],
            'framework' => ['nullable', 'string', 'max:30'],
            'alias' => ['nullable', 'string', 'max:160'],
        ]);

        $slug = Str::slug($data['name']);
        abort_if(Skill::where('slug', $slug)->exists(), 422, 'A skill with this name already exists.');

        $skill = Skill::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'skill_category_id' => $data['skill_category_id'] ?? null,
            'skill_type' => $data['skill_type'],
            'framework' => $data['framework'] ?? null,
        ]);

        if (! empty($data['alias'])) {
            $normalized = Str::lower(trim($data['alias']));
            SkillAlias::firstOrCreate([
                'normalized_alias' => $normalized,
            ], [
                'skill_id' => $skill->id,
                'alias' => trim($data['alias']),
            ]);
        }

        return back()->with('success', 'Skill added to the shared skill catalog.');
    }
}