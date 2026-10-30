<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicTerm extends Model
{
    protected $fillable = ['organization_id', 'name', 'starts_at', 'ends_at', 'status'];
    protected function casts(): array { return ['starts_at' => 'date', 'ends_at' => 'date']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}