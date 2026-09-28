<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSupportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $report = Report::factory()->create();

        $this->post(route('reports.support', $report))->assertRedirect(route('login'));

        $this->assertDatabaseCount('report_supports', 0);
    }

    public function test_user_supports_a_report(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();

        $response = $this->actingAs($user)->post(route('reports.support', $report));

        $response->assertRedirect(route('reports.show', $report));
        $this->assertDatabaseHas('report_supports', ['report_id' => $report->id, 'user_id' => $user->id]);
    }

    public function test_supporting_again_removes_the_support(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();
        $report->supporters()->attach($user);

        $this->actingAs($user)->post(route('reports.support', $report));

        $this->assertDatabaseCount('report_supports', 0);
    }

    public function test_reporter_cannot_support_their_own_report(): void
    {
        $report = Report::factory()->create();

        $this->actingAs($report->user)->post(route('reports.support', $report))->assertForbidden();

        $this->assertDatabaseCount('report_supports', 0);
    }

    public function test_hidden_report_cannot_be_supported(): void
    {
        $report = Report::factory()->hidden()->create();

        $this->actingAs(User::factory()->create())->post(route('reports.support', $report))->assertForbidden();

        $this->assertDatabaseCount('report_supports', 0);
    }
}
