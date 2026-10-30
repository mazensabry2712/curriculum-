<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTarget extends Model
{
    protected $fillable = ['student_profile_id', 'job_profile_id', 'is_primary'];
    protected function casts(): array { return ['is_primary' => 'boolean']; }
    public function studentProfile(): BelongsTo { return $this->belongsTo(StudentProfile::class); }
    public function jobProfile(): BelongsTo { return $this->belongsTo(JobProfile::class); }
}