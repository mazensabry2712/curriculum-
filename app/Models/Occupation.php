<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Occupation extends Model
{
    protected $fillable = ['external_code', 'name', 'slug', 'description', 'framework', 'framework_uri'];

    public function jobProfiles(): HasMany { return $this->hasMany(JobProfile::class); }
}