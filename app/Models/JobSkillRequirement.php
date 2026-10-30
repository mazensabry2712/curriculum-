<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSkillRequirement extends Model
{
    protected $fillable = ['job_profile_id', 'skill_id', 'required_level', 'importance_weight', 'is_required', 'evidence_source'];

    protected function casts(): array
    {
        return ['required_level' => 'integer', 'importance_weight' => 'decimal:2', 'is_required' => 'boolean'];
    }

    public function jobProfile(): BelongsTo { return $this->belongsTo(JobProfile::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
}