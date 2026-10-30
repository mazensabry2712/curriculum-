<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentProfile extends Model
{
    protected $fillable = ['organization_id', 'user_id', 'program_id', 'student_number', 'study_year', 'status', 'bio'];

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function assessments(): HasMany { return $this->hasMany(StudentSkillAssessment::class); }
    public function targets(): HasMany { return $this->hasMany(StudentTarget::class); }
    public function jobProfiles(): BelongsToMany
    {
        return $this->belongsToMany(JobProfile::class, 'student_targets')
            ->withPivot('is_primary')->withTimestamps();
    }
    public function projects(): HasMany { return $this->hasMany(StudentProject::class); }
    public function analyses(): HasMany { return $this->hasMany(AnalysisRun::class); }
}