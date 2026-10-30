<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = ['analysis_run_id', 'skill_id', 'recommendation_type', 'title', 'description', 'priority', 'source', 'status', 'assigned_to', 'due_at'];
    protected function casts(): array { return ['due_at' => 'datetime']; }
    public function analysisRun(): BelongsTo { return $this->belongsTo(AnalysisRun::class); }
    public function skill(): BelongsTo { return $this->belongsTo(Skill::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
}