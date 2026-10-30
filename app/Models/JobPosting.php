<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    protected $fillable = ['employer_id', 'job_profile_id', 'job_market_source_id', 'external_reference', 'title', 'description', 'industry', 'location', 'country', 'employment_type', 'status', 'published_at'];
    protected function casts(): array { return ['published_at' => 'datetime']; }
    public function employer(): BelongsTo { return $this->belongsTo(Employer::class); }
    public function jobProfile(): BelongsTo { return $this->belongsTo(JobProfile::class); }
    public function source(): BelongsTo { return $this->belongsTo(JobMarketSource::class, 'job_market_source_id'); }
    public function skillExtractions(): HasMany { return $this->hasMany(JobSkillExtraction::class); }
}