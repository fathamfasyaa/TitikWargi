<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\FlagReason;
use App\Enums\ModerationAction;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportModerationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_hide_a_report(): void
    {
        $report = Report::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.reports.hide', $report), ['reason' => 'Spam'])
            ->assertForbidden();

        $this->assertNull($report->fresh()->hidden_at);
        $this->assertDatabaseCount('moderation_logs', 0);
    }

    public function test_admin_hides_a_report_closes_its_flags_and_logs_the_action(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create();
        $this->flag($report);

        $response = $this->actingAs($admin)->post(route('admin.reports.hide', $report), ['reason' => 'Foto berisi wajah']);

        $response->assertRedirect()->assertSessionHas('status');
        $this->assertNotNull($report->fresh()->hidden_at);
        $this->assertDatabaseMissing('report_flags', ['report_id' => $report->id, 'resolved_at' => null]);
        $this->assertDatabaseHas('moderation_logs', [
            'moderator_id' => $admin->id,
            'target_type' => Report::class,
            'target_id' => $report->id,
            'action' => ModerationAction::HideReport->value,
            'reason' => 'Foto berisi wajah',
        ]);

        // The hidden report is no longer public.
        $this->post(route('logout'));
        $this->get(route('reports.show', $report))->assertNotFound();
    }

    public function test_admin_shows_a_hidden_report_again(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->hidden()->create();

        $this->actingAs($admin)->post(route('admin.reports.unhide', $report), ['reason' => 'Salah sembunyikan']);

        $this->assertNull($report->fresh()->hidden_at);
        $this->assertDatabaseHas('moderation_logs', [
            'target_id' => $report->id,
            'action' => ModerationAction::UnhideReport->value,
        ]);
    }

    public function test_admin_resolves_flags_without_hiding_the_report(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create();
        $this->flag($report);

        $this->actingAs($admin)->post(route('admin.reports.resolve-flags', $report), ['reason' => 'Flag tidak benar']);

        $this->assertNull($report->fresh()->hidden_at);
        $this->assertDatabaseMissing('report_flags', ['report_id' => $report->id, 'resolved_at' => null]);
        $this->assertDatabaseHas('moderation_logs', [
            'target_id' => $report->id,
            'action' => ModerationAction::ResolveFlags->value,
        ]);
    }

    public function test_action_without_a_reason_is_rejected_and_changes_nothing(): void
    {
        $report = Report::factory()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.reports.hide', $report), ['reason' => '']);

        $response->assertSessionHasErrors(['reason' => 'Alasan wajib diisi.']);
        $this->assertNull($report->fresh()->hidden_at);
        $this->assertDatabaseCount('moderation_logs', 0);
    }

    private function flag(Report $report): void
    {
        $flag = $report->flags()->make(['reason' => FlagReason::Spam]);
        $flag->user()->associate(User::factory()->create());
        $flag->save();
    }
}
