<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillFramework extends Model
{
    protected $fillable = ['code', 'name', 'version', 'source_url', 'last_imported_at'];
    protected function casts(): array { return ['last_imported_at' => 'datetime']; }
    public function mappings(): HasMany { return $this->hasMany(ExternalSkillMapping::class); }
}