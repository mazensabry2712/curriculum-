<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudentProject extends Model
{
    protected $fillable = ['student_profile_id', 'title', 'description', 'repository_url', 'project_url', 'completed_at', 'review_status'];
    protected function casts(): array { return ['completed_at' => 'date']; }
    public function studentProfile(): BelongsTo { return $this->belongsTo(StudentProfile::class); }
    public function skills(): BelongsToMany { return $this->belongsToMany(Skill::class, 'student_project_skills')->withPivot('evidence')->withTimestamps(); }
}