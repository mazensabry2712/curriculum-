<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country', 2)->default('EG');
            $table->string('website')->nullable();
            $table->string('timezone')->default('Africa/Cairo');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('password');
        });

        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 40);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 40);
            $table->string('degree_type', 40)->default('bachelor');
            $table->unsignedTinyInteger('duration_years')->default(4);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['department_id', 'code']);
        });

        Schema::create('curriculum_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('version', 40);
            $table->string('academic_year', 20)->nullable();
            $table->string('status', 20)->default('draft');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['program_id', 'version']);
            $table->index(['program_id', 'status']);
        });

        Schema::create('courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('credit_hours', 4, 1)->default(3);
            $table->string('course_level', 30)->nullable();
            $table->string('language', 10)->default('en');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'code']);
        });

        Schema::create('curriculum_courses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('curriculum_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('semester')->default(1);
            $table->boolean('is_elective')->default(false);
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['curriculum_version_id', 'course_id']);
            $table->index(['curriculum_version_id', 'semester']);
        });

        Schema::create('course_outcomes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->text('description');
            $table->string('assessment_method', 60)->nullable();
            $table->decimal('weight', 6, 2)->default(1);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['course_id', 'code']);
        });

        Schema::create('skill_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 80)->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('skill_type', 30)->default('technical');
            $table->string('framework', 30)->nullable();
            $table->string('framework_uri')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['skill_category_id', 'is_active']);
        });

        Schema::create('skill_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->string('normalized_alias')->unique();
            $table->timestamps();
        });

        Schema::create('course_skill_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('coverage_level');
            $table->decimal('instructional_hours', 6, 2)->default(0);
            $table->text('evidence')->nullable();
            $table->decimal('confidence', 5, 4)->default(1);
            $table->string('mapping_source', 30)->default('manual');
            $table->boolean('is_approved')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['course_id', 'skill_id']);
        });

        Schema::create('outcome_skill_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_outcome_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('suggested_level')->default(1);
            $table->decimal('confidence', 5, 4)->default(1);
            $table->text('evidence')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['course_outcome_id', 'skill_id']);
        });

        Schema::create('occupations', function (Blueprint $table): void {
            $table->id();
            $table->string('external_code', 100)->nullable()->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('framework', 30)->nullable();
            $table->string('framework_uri')->nullable();
            $table->timestamps();
        });

        Schema::create('job_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('occupation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('industry')->nullable();
            $table->string('seniority', 30)->default('entry');
            $table->string('country', 2)->nullable();
            $table->text('description')->nullable();
            $table->string('source_type', 30)->default('curated');
            $table->string('source_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('job_skill_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('required_level');
            $table->decimal('importance_weight', 6, 2)->default(1);
            $table->boolean('is_required')->default(true);
            $table->string('evidence_source', 40)->default('expert_defined');
            $table->timestamps();
            $table->unique(['job_profile_id', 'skill_id']);
        });

        Schema::create('student_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('student_number', 80)->nullable();
            $table->unsignedTinyInteger('study_year')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('bio')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'student_number']);
        });

        Schema::create('student_targets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_profile_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['student_profile_id', 'job_profile_id']);
        });

        Schema::create('student_skill_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('assessment_type', 30);
            $table->text('evidence')->nullable();
            $table->decimal('confidence', 5, 4)->default(1);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->useCurrent();
            $table->timestamps();
            $table->index(['student_profile_id', 'skill_id', 'assessed_at']);
        });

        Schema::create('student_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('project_url')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('review_status', 20)->default('pending');
            $table->timestamps();
        });

        Schema::create('student_project_skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->unique(['student_project_id', 'skill_id']);
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('original_name');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64)->index();
            $table->string('status', 30)->default('uploaded');
            $table->text('processing_error')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'status']);
        });

        Schema::create('ai_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('model', 100);
            $table->string('task_type', 40);
            $table->string('status', 30)->default('queued');
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->string('provider_request_id')->nullable();
            $table->json('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('document_skill_suggestions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ai_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->string('suggested_name');
            $table->string('suggested_category', 50)->nullable();
            $table->unsignedTinyInteger('suggested_level')->default(1);
            $table->decimal('confidence', 5, 4)->default(0);
            $table->text('evidence')->nullable();
            $table->unsignedInteger('page_number')->nullable();
            $table->string('review_status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['ai_run_id', 'review_status']);
        });

        Schema::create('analysis_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('analysis_type', 30);
            $table->foreignId('curriculum_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_profile_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('queued');
            $table->decimal('alignment_score', 6, 2)->nullable();
            $table->decimal('assessed_coverage', 6, 2)->nullable();
            $table->json('input_snapshot')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'analysis_type', 'created_at']);
        });

        Schema::create('analysis_skill_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('analysis_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('required_level');
            $table->decimal('current_level', 3, 1)->nullable();
            $table->decimal('gap_score', 6, 2)->nullable();
            $table->decimal('importance_weight', 6, 2)->default(1);
            $table->decimal('alignment_contribution', 8, 4)->nullable();
            $table->string('result_status', 20);
            $table->text('evidence')->nullable();
            $table->timestamps();
            $table->unique(['analysis_run_id', 'skill_id']);
        });

        Schema::create('recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('analysis_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recommendation_type', 30)->default('skill_development');
            $table->string('title');
            $table->text('description');
            $table->string('priority', 20)->default('medium');
            $table->string('source', 20)->default('rules');
            $table->string('status', 20)->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('analysis_skill_results');
        Schema::dropIfExists('analysis_runs');
        Schema::dropIfExists('document_skill_suggestions');
        Schema::dropIfExists('ai_runs');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('student_project_skills');
        Schema::dropIfExists('student_projects');
        Schema::dropIfExists('student_skill_assessments');
        Schema::dropIfExists('student_targets');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('job_skill_requirements');
        Schema::dropIfExists('job_profiles');
        Schema::dropIfExists('occupations');
        Schema::dropIfExists('outcome_skill_mappings');
        Schema::dropIfExists('course_skill_mappings');
        Schema::dropIfExists('skill_aliases');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('skill_categories');
        Schema::dropIfExists('course_outcomes');
        Schema::dropIfExists('curriculum_courses');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('curriculum_versions');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('departments');
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn('is_active');
        });
        Schema::dropIfExists('organizations');
    }
};
