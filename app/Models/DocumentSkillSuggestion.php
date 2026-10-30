<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSkillSuggestion extends Model
{
    protected $fillable = ['ai_run_id', 'skill_id', 'suggested_name', 'suggested_category', 'suggested_level', 'confidence', 'evidence', 'page_number', 'review_status', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['suggested_level' => 'integer', 'confidence' => 'decimal:4', 'reviewed_at' => 'datetime']; }
    public function aiRun(): BelongsTo { return $this->belongsTo(AiRun::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
}