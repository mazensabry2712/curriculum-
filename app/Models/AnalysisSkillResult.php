<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisSkillResult extends Model
{
    protected $fillable = ['analysis_run_id', 'skill_id', 'required_level', 'current_level', 'gap_score', 'importance_weight', 'alignment_contribution', 'result_status', 'evidence'];
    protected function casts(): array
    {
        return ['required_level' => 'integer', 'current_level' => 'decimal:1', 'gap_score' => 'decimal:2', 'importance_weight' => 'decimal:2', 'alignment_contribution' => 'decimal:4'];
    }
    public function analysisRun(): BelongsTo { return $this->belongsTo(AnalysisRun::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
}