<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_ai_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('model', 100);
            $table->string('status', 30)->default('queued');
            $table->json('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->index(['job_posting_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_ai_runs');
    }
};
