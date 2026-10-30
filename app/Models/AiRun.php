<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRun extends Model
{
    protected $fillable = ['document_id', 'requested_by', 'provider', 'model', 'task_type', 'status', 'input_tokens', 'output_tokens', 'provider_request_id', 'output', 'error', 'started_at', 'finished_at'];
    protected function casts(): array { return ['output' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime']; }
    public function document(): BelongsTo { return $this->belongsTo(Document::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function skillSuggestions(): HasMany { return $this->hasMany(DocumentSkillSuggestion::class); }
}