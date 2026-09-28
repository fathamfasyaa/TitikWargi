<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_the_map_and_the_latest_visible_reports(): void
    {
        Report::factory()->create(['address' => 'Jl. Siliwangi']);
        Report::factory()->hidden()->create(['address' => 'Jl. Tersembunyi']);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('id="report-map"', escape: false)
            ->assertSee('Jl. Siliwangi')
            ->assertDontSee('Jl. Tersembunyi');
    }
}
