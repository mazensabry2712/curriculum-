<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CurriculumCourse;
use App\Models\CurriculumVersion;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicController extends Controller
{
    public function index(Request $request): View
    {
        $organizationId = $request->user()->organization_id;

        return view('academic.index', [
            'departments' => Department::where('organization_id', $organizationId)->with('programs')->orderBy('name')->get(),
            'programs' => Program::whereHas('department', fn ($q) => $q->where('organization_id', $organizationId))->with('department')->orderBy('name')->get(),
            'courses' => Course::where('organization_id', $organizationId)->orderBy('code')->get(),
            'curricula' => CurriculumVersion::whereHas('program.department', fn ($q) => $q->where('organization_id', $organizationId))->with('program')->latest()->get(),
        ]);
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:40', Rule::unique('departments')->where('organization_id', $request->user()->organization_id)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Department::create($data + ['organization_id' => $request->user()->organization_id]);

        return back()->with('success', 'Department created.');
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:40'],
            'degree_type' => ['required', Rule::in(['bachelor', 'diploma', 'master', 'phd', 'certificate'])],
            'duration_years' => ['required', 'integer', 'min:1', 'max:10'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $department = Department::whereKey($data['department_id'])
            ->where('organization_id', $request->user()->organization_id)->firstOrFail();

        validator($data, [
            'code' => [Rule::unique('programs')->where('department_id', $department->id)],
        ])->validate();

        Program::create($data);

        return back()->with('success', 'Academic program created.');
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('courses')->where('organization_id', $request->user()->organization_id)],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'credit_hours' => ['required', 'numeric', 'min:0.5', 'max:30'],
            'course_level' => ['nullable', 'string', 'max:30'],
            'language' => ['required', 'string', 'size:2'],
        ]);

        Course::create($data + ['organization_id' => $request->user()->organization_id]);

        return back()->with('success', 'Course created.');
    }

    public function storeCurriculum(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'version' => ['required', 'string', 'max:40'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'course_ids' => ['required', 'array', 'min:1'],
            'course_ids.*' => ['required', 'integer', 'distinct', 'exists:courses,id'],
        ]);

        $program = Program::whereKey($data['program_id'])
            ->whereHas('department', fn ($q) => $q->where('organization_id', $request->user()->organization_id))
            ->firstOrFail();

        $courses = Course::where('organization_id', $request->user()->organization_id)
            ->whereIn('id', $data['course_ids'])->get();

        abort_unless($courses->count() === count($data['course_ids']), 422, 'One or more courses are outside your organization.');

        $version = DB::transaction(function () use ($program, $data, $courses, $request): CurriculumVersion {
            $version = CurriculumVersion::create([
                'program_id' => $program->id,
                'version' => $data['version'],
                'academic_year' => $data['academic_year'] ?? null,
                'status' => 'draft',
                'created_by' => $request->user()->id,
            ]);

            foreach ($courses as $index => $course) {
                CurriculumCourse::create([
                    'curriculum_version_id' => $version->id,
                    'course_id' => $course->id,
                    'semester' => 1,
                    'is_required' => true,
                    'is_elective' => false,
                    'position' => $index + 1,
                ]);
            }

            return $version;
        });

        return back()->with('success', "Curriculum version {$version->version} created.");
    }
}