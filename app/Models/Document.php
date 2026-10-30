<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $fillable = ['organization_id', 'course_id', 'uploaded_by', 'title', 'original_name', 'disk', 'path', 'mime_type', 'size_bytes', 'sha256', 'status', 'processing_error'];

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function aiRuns(): HasMany { return $this->hasMany(AiRun::class); }
}