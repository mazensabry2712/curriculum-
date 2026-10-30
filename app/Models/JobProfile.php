<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobProfile extends Model
{
    protected $fillable = ['organization_id', 'occupation_id', 'title', 'industry', 'seniority', 'country', 'description', 'source_type', 'source_reference', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function occupation(): BelongsTo { return $this->belongsTo(Occupation::class); }
    public function requirements(): HasMany { return $this->hasMany(JobSkillRequirement::class); }
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'job_skill_requirements')
            ->withPivot(['required_level', 'importance_weight', 'is_required', 'evidence_source'])
            ->withTimestamps();
    }
}