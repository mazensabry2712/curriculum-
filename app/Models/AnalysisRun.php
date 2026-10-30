<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisRun extends Model
{
    protected $fillable = ['organization_id', 'analysis_type', 'curriculum_version_id', 'student_profile_id', 'job_profile_id', 'requested_by', 'status', 'alignment_score', 'assessed_coverage', 'input_snapshot', 'error', 'completed_at'];
    protected function casts(): array
    {
        return ['alignment_score' => 'decimal:2', 'assessed_coverage' => 'decimal:2', 'input_snapshot' => 'array', 'completed_at' => 'datetime'];
    }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function curriculumVersion(): BelongsTo { return $this->belongsTo(CurriculumVersion::class); }
    public function studentProfile(): BelongsTo { return $this->belongsTo(StudentProfile::class); }
    public function jobProfile(): BelongsTo { return $this->belongsTo(JobProfile::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function skillResults(): HasMany { return $this->hasMany(AnalysisSkillResult::class); }
    public function recommendations(): HasMany { return $this->hasMany(Recommendation::class); }
}