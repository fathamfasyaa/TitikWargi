<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['path', 'width', 'height'])]
class ReportPhoto extends Model
{
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
