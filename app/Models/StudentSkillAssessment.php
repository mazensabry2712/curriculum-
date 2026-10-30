<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSkillAssessment extends Model
{
    protected $fillable = ['student_profile_id', 'skill_id', 'level', 'assessment_type', 'evidence', 'confidence', 'is_verified', 'assessed_by', 'assessed_at'];

    protected function casts(): array
    {
        return ['level' => 'integer', 'confidence' => 'decimal:4', 'is_verified' => 'boolean', 'assessed_at' => 'datetime'];
    }

    public function studentProfile(): BelongsTo { return $this->belongsTo(StudentProfile::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
    public function assessor(): BelongsTo { return $this->belongsTo(User::class, 'assessed_by'); }
}