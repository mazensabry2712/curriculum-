<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseOutcome extends Model
{
    protected $fillable = ['course_id', 'code', 'description', 'assessment_method', 'weight', 'position'];

    protected function casts(): array { return ['weight' => 'decimal:2']; }

    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'outcome_skill_mappings')
            ->withPivot(['suggested_level', 'confidence', 'evidence', 'is_approved'])
            ->withTimestamps();
    }
}