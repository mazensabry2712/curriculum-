<?php

namespace App\Http\Controllers;

use App\Models\AnalysisRun;
use App\Models\Course;
use App\Models\Document;
use App\Models\JobProfile;
use App\Models\Skill;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        if ($request->user()->hasAnyRole(['student', 'graduate'])) {
            return redirect()->route('student-portal.index');
        }

        $organizationId = $request->user()->organization_id;

        return view('dashboard', [
            'courseCount' => Course::where('organization_id', $organizationId)->count(),
            'skillCount' => Skill::where('is_active', true)->count(),
            'studentCount' => StudentProfile::where('organization_id', $organizationId)->count(),
            'jobCount' => JobProfile::where(fn ($q) => $q->whereNull('organization_id')->orWhere('organization_id', $organizationId))->where('is_active', true)->count(),
            'analysisCount' => AnalysisRun::where('organization_id', $organizationId)->count(),
            'pendingDocuments' => Document::where('organization_id', $organizationId)->whereIn('status', ['uploaded', 'processing', 'needs_review'])->count(),
            'recentAnalyses' => AnalysisRun::where('organization_id', $organizationId)->with(['jobProfile', 'curriculumVersion.program', 'studentProfile.user'])->latest()->limit(6)->get(),
        ]);
    }
}