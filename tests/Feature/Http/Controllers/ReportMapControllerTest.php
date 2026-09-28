<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportMapControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A map area around central Cianjur.
     *
     * @var array{south: float, west: float, north: float, east: float}
     */
    private const CIANJUR_AREA = [
        'south' => -6.83,
        'west' => 107.13,
        'north' => -6.81,
        'east' => 107.15,
    ];

    public function test_returns_reports_inside_the_map_area_as_geojson(): void
    {
        $inside = Report::factory()->at(-6.817, 107.142)->create();
        Report::factory()->at(-6.9, 107.2)->create();

        $response = $this->getJson(route('map.reports', self::CIANJUR_AREA));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/geo+json')
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.properties.id', $inside->id)
            // GeoJSON order is [longitude, latitude].
            ->assertJsonPath('features.0.geometry.coordinates', [107.142, -6.817]);
    }

    public function test_does_not_return_hidden_reports(): void
    {
        Report::factory()->at(-6.817, 107.142)->hidden()->create();

        $response = $this->getJson(route('map.reports', self::CIANJUR_AREA));

        $response->assertOk()->assertJsonCount(0, 'features');
    }

    public function test_does_not_return_deleted_reports(): void
    {
        Report::factory()->at(-6.817, 107.142)->create()->delete();

        $response = $this->getJson(route('map.reports', self::CIANJUR_AREA));

        $response->assertOk()->assertJsonCount(0, 'features');
    }

    public function test_returns_the_status_so_the_map_can_color_the_point(): void
    {
        Report::factory()->at(-6.817, 107.142)->repaired()->create();

        $response = $this->getJson(route('map.reports', self::CIANJUR_AREA));

        $response->assertJsonPath('features.0.properties.status', 'repaired');
    }

    public function test_rejects_a_map_area_that_is_missing_or_out_of_range_with_422(): void
    {
        $response = $this->getJson(route('map.reports', [
            'west' => 107.13,
            'north' => 95,
            'east' => 107.15,
        ]));

        $response->assertUnprocessable()->assertJsonValidationErrors(['south', 'north']);
    }
}
