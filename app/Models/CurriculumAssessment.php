<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumAssessment extends Model
{
    protected $fillable = ['course_id', 'title', 'assessment_type', 'description', 'weight'];
    protected function casts(): array { return ['weight' => 'decimal:2']; }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}