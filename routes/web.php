<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LaborMarketController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : view('landing'))->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:super-admin|university-admin|department-admin|instructor')->group(function (): void {
        Route::prefix('academic')->name('academic.')->group(function (): void {
            Route::get('/', [AcademicController::class, 'index'])->name('index');
            Route::post('/departments', [AcademicController::class, 'storeDepartment'])->name('departments.store');
            Route::post('/programs', [AcademicController::class, 'storeProgram'])->name('programs.store');
            Route::post('/courses', [AcademicController::class, 'storeCourse'])->name('courses.store');
            Route::post('/curricula', [AcademicController::class, 'storeCurriculum'])->name('curricula.store');
        });

        Route::prefix('skills')->name('skills.')->group(function (): void {
            Route::get('/', [SkillController::class, 'index'])->name('index');
            Route::post('/', [SkillController::class, 'store'])->name('store');
        });

        Route::prefix('documents')->name('documents.')->group(function (): void {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::post('/', [DocumentController::class, 'store'])->name('store');
            Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
            Route::post('/{document}/approve', [DocumentController::class, 'approve'])->name('approve');
            Route::post('/{document}/reject', [DocumentController::class, 'reject'])->name('reject');
        });

        Route::prefix('students')->name('students.')->group(function (): void {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::post('/', [StudentController::class, 'store'])->name('store');
            Route::post('/assessments', [StudentController::class, 'storeAssessment'])->name('assessments.store');
            Route::post('/assessments/{assessment}/verify', [StudentController::class, 'verifyAssessment'])->name('assessments.verify');
            Route::post('/targets', [StudentController::class, 'storeTarget'])->name('targets.store');
        });
    });

    Route::middleware('role:super-admin|university-admin|department-admin|career-advisor|employer')->group(function (): void {
        Route::prefix('labor-market')->name('labor-market.')->group(function (): void {
            Route::get('/', [LaborMarketController::class, 'index'])->name('index');
            Route::post('/occupations', [LaborMarketController::class, 'storeOccupation'])->name('occupations.store');
            Route::post('/profiles', [LaborMarketController::class, 'storeProfile'])->name('profiles.store');
            Route::post('/requirements', [LaborMarketController::class, 'storeRequirement'])->name('requirements.store');
        });
    });

    Route::middleware('role:super-admin|university-admin|department-admin|instructor|career-advisor')->prefix('analysis')->name('analysis.')->group(function (): void {
        Route::get('/', [AnalysisController::class, 'index'])->name('index');
        Route::post('/curriculum', [AnalysisController::class, 'runCurriculum'])->name('curriculum');
        Route::post('/student', [AnalysisController::class, 'runStudent'])->name('student');
        Route::get('/{analysisRun}', [AnalysisController::class, 'show'])->name('show');
    });

    Route::middleware('role:student|graduate')->prefix('my-learning')->name('student-portal.')->group(function (): void {
        Route::get('/', [StudentPortalController::class, 'show'])->name('index');
        Route::post('/assessments', [StudentPortalController::class, 'storeAssessment'])->name('assessments.store');
        Route::post('/career-target', [StudentPortalController::class, 'storeTarget'])->name('target.store');
        Route::post('/projects', [StudentPortalController::class, 'storeProject'])->name('projects.store');
        Route::post('/analyze', [StudentPortalController::class, 'analyze'])->name('analyze');
    });
});
