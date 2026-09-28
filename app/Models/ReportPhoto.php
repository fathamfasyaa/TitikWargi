<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['path', 'width', 'height'])]
class ReportPhoto extends Model
{
    /**
     * Public URL of the photo file.
     */
    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
