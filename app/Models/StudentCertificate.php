<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCertificate extends Model
{
    protected $fillable = ['student_profile_id', 'title', 'issuer', 'credential_id', 'credential_url', 'disk', 'path', 'issued_at', 'expires_at', 'review_status'];
    protected function casts(): array { return ['issued_at' => 'date', 'expires_at' => 'date']; }
    public function studentProfile(): BelongsTo { return $this->belongsTo(StudentProfile::class); }
}