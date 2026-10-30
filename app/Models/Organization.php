<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'country', 'website', 'timezone', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function departments(): HasMany { return $this->hasMany(Department::class); }
    public function courses(): HasMany { return $this->hasMany(Course::class); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function studentProfiles(): HasMany { return $this->hasMany(StudentProfile::class); }
}