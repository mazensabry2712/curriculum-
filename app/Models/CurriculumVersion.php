<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumVersion extends Model
{
    protected $fillable = ['program_id', 'version', 'academic_year', 'status', 'effective_from', 'effective_to', 'created_by'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_to' => 'date'];
    }

    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'curriculum_courses')
            ->withPivot(['id', 'semester', 'is_elective', 'is_required', 'position'])
            ->withTimestamps();
    }
    public function curriculumCourses(): HasMany { return $this->hasMany(CurriculumCourse::class); }
    public function analyses(): HasMany { return $this->hasMany(AnalysisRun::class); }
}