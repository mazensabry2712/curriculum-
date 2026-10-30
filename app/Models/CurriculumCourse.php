<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumCourse extends Model
{
    protected $fillable = ['curriculum_version_id', 'course_id', 'semester', 'is_elective', 'is_required', 'position'];

    protected function casts(): array
    {
        return ['is_elective' => 'boolean', 'is_required' => 'boolean', 'semester' => 'integer'];
    }

    public function curriculumVersion(): BelongsTo { return $this->belongsTo(CurriculumVersion::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}