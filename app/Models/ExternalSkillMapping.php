<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalSkillMapping extends Model
{
    protected $fillable = ['skill_framework_id', 'skill_id', 'external_uri', 'external_code', 'external_label', 'source_payload'];
    protected function casts(): array { return ['source_payload' => 'array']; }
    public function framework(): BelongsTo { return $this->belongsTo(SkillFramework::class, 'skill_framework_id'); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
}