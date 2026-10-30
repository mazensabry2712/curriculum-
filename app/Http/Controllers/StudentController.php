<?php

namespace App\Http\Controllers;

use App\Models\JobProfile;
use App\Models\Program;
use App\Models\Skill;
use App\Models\StudentProfile;
use App\Models\StudentSkillAssessment;
use App\Models\StudentTarget;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        return view('students.index', [
            'students' => StudentProfile::where('organization_id', $organizationId)->with(['user', 'program'])->latest()->paginate(20),
            'programs' => Program::whereHas('department', fn ($q) => $q->where('organization_id', $organizationId))->orderBy('name')->get(),
            'skills' => Skill::where('is_active', true)->orderBy('name')->get(),
            'jobProfiles' => JobProfile::where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $organizationId))->where('is_active', true)->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'student_number' => ['nullable', 'string', 'max:80'],
            'study_year' => ['nullable', 'integer', 'between:1,10'],
        ]);

        if (! empty($data['program_id'])) {
            Program::whereKey($data['program_id'])
                ->whereHas('department', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
                ->firstOrFail();
        }

        DB::transaction(function () use ($data, $request): void {
            $user = User::create([
                'organization_id' => $request->user()->organization_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $role = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
            $user->assignRole($role);

            StudentProfile::create([
                'organization_id' => $request->user()->organization_id,
                'user_id' => $user->id,
                'program_id' => $data['program_id'] ?? null,
                'student_number' => $data['student_number'] ?? null,
                'study_year' => $data['study_year'] ?? null,
                'status' => 'active',
            ]);
        });

        return back()->with('success', 'Student account created.');
    }

    public function storeAssessment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_profile_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'skill_id' => ['required', 'integer', 'exists:skills,id'],
            'level' => ['required', 'integer', 'between:1,5'],
            'assessment_type' => ['required', Rule::in(['self', 'instructor', 'exam', 'project', 'certificate'])],
            'evidence' => ['nullable', 'string', 'max:4000'],
        ]);

        $student = StudentProfile::whereKey($data['student_profile_id'])
            ->where('organization_id', $request->user()->organization_id)->firstOrFail();

        StudentSkillAssessment::create([
            'student_profile_id' => $student->id,
            'skill_id' => $data['skill_id'],
            'level' => $data['level'],
            'assessment_type' => $data['assessment_type'],
            'evidence' => $data['evidence'] ?? null,
            'confidence' => $data['assessment_type'] === 'self' ? 0.4 : 0.8,
            'is_verified' => false,
            'assessed_by' => $request->user()->id,
            'assessed_at' => now(),
        ]);

        return back()->with('success', 'Skill assessment saved and awaiting verification.');
    }

    public function storeTarget(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_profile_id' => ['required', 'integer', 'exists:student_profiles,id'],
            'job_profile_id' => ['required', 'integer', 'exists:job_profiles,id'],
            'is_primary' => ['required', 'boolean'],
        ]);

        $student = StudentProfile::whereKey($data['student_profile_id'])
            ->where('organization_id', $request->user()->organization_id)->firstOrFail();

        $profile = JobProfile::whereKey($data['job_profile_id'])
            ->where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $request->user()->organization_id))
            ->firstOrFail();

        if ($data['is_primary']) {
            $student->targets()->update(['is_primary' => false]);
        }

        StudentTarget::updateOrCreate(
            ['student_profile_id' => $student->id, 'job_profile_id' => $profile->id],
            ['is_primary' => $data['is_primary']],
        );

        return back()->with('success', 'Career target saved.');
    }

    public function verifyAssessment(Request $request, StudentSkillAssessment $assessment): RedirectResponse
    {
        $assessment->load('studentProfile');

        abort_unless($assessment->studentProfile->organization_id === $request->user()->organization_id, 404);

        $assessment->update([
            'is_verified' => true,
            'assessed_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Student skill evidence verified.');
    }
}