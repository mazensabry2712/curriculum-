<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSkillExtraction extends Model
{
    protected $fillable = ['job_posting_id', 'skill_id', 'suggested_name', 'required_level', 'importance_weight', 'is_required', 'confidence', 'evidence', 'review_status', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array
    {
        return ['required_level' => 'integer', 'importance_weight' => 'decimal:2', 'is_required' => 'boolean', 'confidence' => 'decimal:4', 'reviewed_at' => 'datetime'];
    }
    public function jobPosting(): BelongsTo { return $this->belongsTo(JobPosting::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}