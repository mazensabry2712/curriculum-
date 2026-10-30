<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillRelationship extends Model
{
    protected $fillable = ['skill_id', 'related_skill_id', 'relationship_type', 'strength'];
    protected function casts(): array { return ['strength' => 'decimal:4']; }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
    public function relatedSkill(): BelongsTo { return $this->belongsTo(Skill::class, 'related_skill_id'); }
}