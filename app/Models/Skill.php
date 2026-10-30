<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = ['skill_category_id', 'code', 'name', 'slug', 'description', 'skill_type', 'framework', 'framework_uri', 'is_active'];

    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function category(): BelongsTo { return $this->belongsTo(SkillCategory::class, 'skill_category_id'); }
    public function aliases(): HasMany { return $this->hasMany(SkillAlias::class); }
    public function courseMappings(): HasMany { return $this->hasMany(CourseSkillMapping::class); }
    public function jobRequirements(): HasMany { return $this->hasMany(JobSkillRequirement::class); }
    public function studentAssessments(): HasMany { return $this->hasMany(StudentSkillAssessment::class); }
    public function externalMappings(): HasMany { return $this->hasMany(ExternalSkillMapping::class); }
    public function outgoingRelationships(): HasMany { return $this->hasMany(SkillRelationship::class); }
}