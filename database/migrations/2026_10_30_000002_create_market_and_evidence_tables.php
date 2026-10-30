<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->string('status', 20)->default('planned');
            $table->timestamps();
            $table->unique(['organization_id', 'name']);
        });

        Schema::create('curriculum_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('assessment_type', 40);
            $table->text('description')->nullable();
            $table->decimal('weight', 6, 2)->default(1);
            $table->timestamps();
        });

        Schema::create('skill_frameworks', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('version', 40)->nullable();
            $table->string('source_url')->nullable();
            $table->timestamp('last_imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('external_skill_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_framework_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('external_uri', 500);
            $table->string('external_code', 120)->nullable();
            $table->string('external_label')->nullable();
            $table->json('source_payload')->nullable();
            $table->timestamps();
            $table->unique(['skill_framework_id', 'external_uri']);
            $table->index(['skill_id', 'skill_framework_id']);
        });

        Schema::create('skill_relationships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('relationship_type', 30);
            $table->decimal('strength', 5, 4)->default(1);
            $table->timestamps();
            $table->unique(['skill_id', 'related_skill_id', 'relationship_type']);
        });

        Schema::create('employers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company_name');
            $table->string('industry')->nullable();
            $table->string('website')->nullable();
            $table->string('country', 2)->default('EG');
            $table->timestamps();
        });

        Schema::create('job_market_sources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('source_type', 30);
            $table->string('source_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'source_type']);
        });

        Schema::create('job_postings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_market_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_reference')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('industry')->nullable();
            $table->string('location')->nullable();
            $table->string('country', 2)->default('EG');
            $table->string('employment_type', 30)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['country', 'status', 'published_at']);
        });

        Schema::create('job_skill_extractions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->nullable()->constrained()->nullOnDelete();
            $table->string('suggested_name');
            $table->unsignedTinyInteger('required_level')->default(1);
            $table->decimal('importance_weight', 6, 2)->default(1);
            $table->boolean('is_required')->default(true);
            $table->decimal('confidence', 5, 4)->default(0);
            $table->text('evidence')->nullable();
            $table->string('review_status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['job_posting_id', 'review_status']);
        });

        Schema::create('student_certificates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('issuer');
            $table->string('credential_id')->nullable();
            $table->string('credential_url')->nullable();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('review_status', 20)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_certificates');
        Schema::dropIfExists('job_skill_extractions');
        Schema::dropIfExists('job_postings');
        Schema::dropIfExists('job_market_sources');
        Schema::dropIfExists('employers');
        Schema::dropIfExists('skill_relationships');
        Schema::dropIfExists('external_skill_mappings');
        Schema::dropIfExists('skill_frameworks');
        Schema::dropIfExists('curriculum_assessments');
        Schema::dropIfExists('academic_terms');
    }
};
