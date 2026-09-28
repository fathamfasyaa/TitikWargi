<?php

namespace App\Models;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use Database\Factories\ReportFactory;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable(['category', 'severity', 'description', 'location', 'address', 'kelurahan'])]
#[Hidden(['location'])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => ReportCategory::class,
            'severity' => ReportSeverity::class,
            'status' => ReportStatus::class,
            'hidden_at' => 'datetime',
        ];
    }

    /**
     * Build the SQL value for the "location" column.
     *
     * MySQL 8 reads "POINT(a b)" with SRID 4326 as latitude first, then longitude.
     */
    public static function pointFromCoordinates(float $latitude, float $longitude): Expression
    {
        return DB::raw(sprintf("ST_GeomFromText('POINT(%F %F)', 4326)", $latitude, $longitude));
    }

    /**
     * Add "latitude" and "longitude" attributes to the selected reports.
     */
    #[Scope]
    protected function withCoordinates(Builder $query): void
    {
        $query->addSelect([
            'reports.*',
            DB::raw('ST_Latitude(location) AS latitude'),
            DB::raw('ST_Longitude(location) AS longitude'),
        ]);
    }

    /**
     * Only reports inside a map area (the visible part of the map).
     */
    #[Scope]
    protected function withinBounds(Builder $query, float $south, float $west, float $north, float $east): void
    {
        // A rectangle in latitude-first order, because of SRID 4326 in MySQL 8.
        $polygon = sprintf(
            'POLYGON((%1$F %2$F, %1$F %4$F, %3$F %4$F, %3$F %2$F, %1$F %2$F))',
            $south, $west, $north, $east,
        );

        $query->whereRaw('MBRContains(ST_GeomFromText(?, 4326), location)', [$polygon]);
    }

    /**
     * Only reports that are not hidden by a moderator.
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->whereNull('hidden_at');
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /**
     * Whole days since the report was created (0 = today).
     */
    public function daysSinceReported(): int
    {
        return (int) $this->created_at->copy()->startOfDay()->diffInDays(today());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(ReportSupport::class);
    }

    public function supporters(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'report_supports')->withTimestamps();
    }

    public function flags(): HasMany
    {
        return $this->hasMany(ReportFlag::class);
    }
}
