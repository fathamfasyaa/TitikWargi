<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportMapRequest;
use App\Models\Report;
use Illuminate\Http\JsonResponse;

/**
 * Returns the reports inside the visible map area as GeoJSON.
 */
class ReportMapController extends Controller
{
    /**
     * The most points we send at once, to keep the response small.
     */
    private const MAX_REPORTS = 500;

    public function __invoke(ReportMapRequest $request): JsonResponse
    {
        $reports = Report::query()
            ->visible()
            ->withinBounds(
                $request->float('south'),
                $request->float('west'),
                $request->float('north'),
                $request->float('east'),
            )
            ->withCoordinates()
            ->withCount('supporters')
            ->latest()
            ->limit(self::MAX_REPORTS)
            ->get();

        $features = $reports->map(fn (Report $report) => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                // GeoJSON uses [longitude, latitude], the opposite of MySQL and Leaflet.
                'coordinates' => [round($report->longitude, 6), round($report->latitude, 6)],
            ],
            'properties' => [
                'id' => $report->id,
                'category' => $report->category->label(),
                'severity' => $report->severity->label(),
                'status' => $report->status->value,
                'status_label' => $report->status->label(),
                'address' => $report->address,
                'kelurahan' => $report->kelurahan,
                'supports_count' => $report->supporters_count,
                'reported_ago' => $report->created_at->diffForHumans(),
            ],
        ]);

        return response()->json(
            ['type' => 'FeatureCollection', 'features' => $features],
            headers: ['Content-Type' => 'application/geo+json'],
        );
    }
}
