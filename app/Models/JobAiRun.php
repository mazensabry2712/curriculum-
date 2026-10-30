<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobAiRun extends Model
{
    protected $fillable = ['job_posting_id', 'requested_by', 'provider', 'model', 'status', 'output', 'error', 'started_at', 'finished_at'];
    protected function casts(): array { return ['output' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime']; }
    public function jobPosting(): BelongsTo { return $this->belongsTo(JobPosting::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function extractions(): HasMany { return $this->hasMany(JobSkillExtraction::class); }
}