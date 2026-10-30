<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSkillMapping extends Model
{
    protected $fillable = ['course_id', 'skill_id', 'coverage_level', 'instructional_hours', 'evidence', 'confidence', 'mapping_source', 'is_approved', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['coverage_level' => 'integer', 'instructional_hours' => 'decimal:2', 'confidence' => 'decimal:4', 'is_approved' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
}