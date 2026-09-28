<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\FlagReason;
use App\Models\Report;
use App\Models\ReportFlag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.moderation'))->assertRedirect(route('login'));
    }

    public function test_regular_user_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.moderation'))->assertForbidden();
    }

    public function test_admin_sees_reports_with_open_flags(): void
    {
        $flagged = Report::factory()->create();
        $this->flag($flagged, FlagReason::InappropriatePhoto, 'Wajah terlihat');

        $resolved = Report::factory()->create();
        $this->flag($resolved, FlagReason::Spam, 'Sudah ditangani')->forceFill(['resolved_at' => now()])->save();

        $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.moderation'));

        $response->assertOk()
            ->assertSee("#{$flagged->id}")
            ->assertSee(FlagReason::InappropriatePhoto->label())
            ->assertSee('Wajah terlihat')
            ->assertDontSee('Sudah ditangani');
    }

    public function test_admin_sees_the_moderation_link_in_the_layout(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('home'))
            ->assertSee(route('admin.moderation'));

        $this->actingAs(User::factory()->create())->get(route('home'))
            ->assertDontSee(route('admin.moderation'));
    }

    private function flag(Report $report, FlagReason $reason, string $note): ReportFlag
    {
        $flag = $report->flags()->make(['reason' => $reason, 'note' => $note]);
        $flag->user()->associate(User::factory()->create());
        $flag->save();

        return $flag;
    }
}
