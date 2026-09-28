<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\FlagReason;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFlagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $report = Report::factory()->create();

        $this->post(route('reports.flag', $report), ['reason' => FlagReason::Spam->value])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('report_flags', 0);
    }

    public function test_user_flags_a_report_with_a_reason_and_note(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();

        $response = $this->actingAs($user)->post(route('reports.flag', $report), [
            'reason' => FlagReason::InappropriatePhoto->value,
            'note' => 'Plat nomor terlihat jelas.',
        ]);

        $response->assertRedirect(route('reports.show', $report))->assertSessionHas('status');
        $this->assertDatabaseHas('report_flags', [
            'report_id' => $report->id,
            'user_id' => $user->id,
            'reason' => 'inappropriate_photo',
            'note' => 'Plat nomor terlihat jelas.',
            'resolved_at' => null,
        ]);
    }

    public function test_user_cannot_flag_the_same_report_twice(): void
    {
        $user = User::factory()->create();
        $report = Report::factory()->create();
        $this->actingAs($user)->post(route('reports.flag', $report), ['reason' => FlagReason::Spam->value]);

        $response = $this->actingAs($user)->post(route('reports.flag', $report), ['reason' => FlagReason::Fake->value]);

        $response->assertForbidden();
        $this->assertDatabaseCount('report_flags', 1);
    }

    public function test_reporter_cannot_flag_their_own_report(): void
    {
        $report = Report::factory()->create();

        $this->actingAs($report->user)
            ->post(route('reports.flag', $report), ['reason' => FlagReason::Spam->value])
            ->assertForbidden();
    }

    public function test_flag_without_a_reason_is_rejected_with_a_message(): void
    {
        $report = Report::factory()->create();

        $response = $this->actingAs(User::factory()->create())->post(route('reports.flag', $report), []);

        $response->assertSessionHasErrors(['reason' => 'Alasan wajib diisi.']);
        $this->assertDatabaseCount('report_flags', 0);
    }
}
