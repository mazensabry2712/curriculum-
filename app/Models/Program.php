<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = ['department_id', 'name', 'code', 'degree_type', 'duration_years', 'description'];

    protected function casts(): array { return ['duration_years' => 'integer']; }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function curriculumVersions(): HasMany { return $this->hasMany(CurriculumVersion::class); }
    public function studentProfiles(): HasMany { return $this->hasMany(StudentProfile::class); }
}