<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobMarketSource extends Model
{
    protected $fillable = ['organization_id', 'name', 'source_type', 'source_url', 'metadata', 'collected_at'];
    protected function casts(): array { return ['metadata' => 'array', 'collected_at' => 'datetime']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function postings(): HasMany { return $this->hasMany(JobPosting::class); }
}