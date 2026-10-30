<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = ['organization_id', 'code', 'name', 'description', 'credit_hours', 'course_level', 'language', 'is_active'];

    protected function casts(): array
    {
        return ['credit_hours' => 'decimal:1', 'is_active' => 'boolean'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function outcomes(): HasMany { return $this->hasMany(CourseOutcome::class); }
    public function assessments(): HasMany { return $this->hasMany(CurriculumAssessment::class); }
    public function skillMappings(): HasMany { return $this->hasMany(CourseSkillMapping::class); }
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_skill_mappings')
            ->withPivot(['coverage_level', 'instructional_hours', 'evidence', 'confidence', 'is_approved'])
            ->withTimestamps();
    }
    public function documents(): HasMany { return $this->hasMany(Document::class); }
}