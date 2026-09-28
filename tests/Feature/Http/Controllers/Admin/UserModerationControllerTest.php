<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Enums\ModerationAction;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UserModerationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_ban_anyone(): void
    {
        $target = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('admin.users.ban', $target), ['reason' => 'Spam', 'duration' => '7'])
            ->assertForbidden();

        $this->assertNull($target->fresh()->banned_until);
    }

    public function test_admin_warns_a_user_and_logs_the_action(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.warn', $target), ['reason' => 'Foto tidak pantas']);

        $this->assertSame(1, $target->fresh()->strikes);
        $this->assertDatabaseHas('moderation_logs', [
            'moderator_id' => $admin->id,
            'target_type' => User::class,
            'target_id' => $target->id,
            'action' => ModerationAction::WarnUser->value,
            'reason' => 'Foto tidak pantas',
        ]);
    }

    #[TestWith(['7', 7])]
    #[TestWith(['30', 30])]
    public function test_admin_bans_a_user_for_a_number_of_days(string $duration, int $days): void
    {
        $this->freezeSecond();
        $target = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.ban', $target), ['reason' => 'Spam berulang', 'duration' => $duration]);

        $target->refresh();
        $this->assertTrue($target->banned_until->equalTo(now()->addDays($days)));
        $this->assertFalse($target->can('create', Report::class));
        $this->assertDatabaseHas('moderation_logs', [
            'target_id' => $target->id,
            'action' => ModerationAction::BanUser->value,
            'reason' => "[{$days} hari] Spam berulang",
        ]);
    }

    public function test_admin_bans_a_user_permanently(): void
    {
        $target = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.ban', $target), ['reason' => 'Akun palsu', 'duration' => 'permanent']);

        $this->assertTrue($target->fresh()->isBannedPermanently());
        $this->assertTrue($target->fresh()->isBanned());
    }

    public function test_ban_with_an_unknown_duration_is_rejected(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.ban', $target), ['reason' => 'Spam', 'duration' => '365']);

        $response->assertSessionHasErrors(['duration' => 'Lama blokir yang dipilih tidak valid.']);
        $this->assertNull($target->fresh()->banned_until);
    }

    public function test_admin_lifts_a_ban(): void
    {
        $target = User::factory()->banned()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.users.unban', $target), ['reason' => 'Banding diterima']);

        $this->assertFalse($target->fresh()->isBanned());
        $this->assertDatabaseHas('moderation_logs', [
            'target_id' => $target->id,
            'action' => ModerationAction::UnbanUser->value,
        ]);
    }

    public function test_admin_cannot_warn_or_ban_another_admin(): void
    {
        $otherAdmin = User::factory()->admin()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.warn', $otherAdmin), ['reason' => 'Tes'])
            ->assertForbidden();
        $this->actingAs($admin)
            ->post(route('admin.users.ban', $otherAdmin), ['reason' => 'Tes', 'duration' => '7'])
            ->assertForbidden();

        $this->assertSame(0, $otherAdmin->fresh()->strikes);
        $this->assertNull($otherAdmin->fresh()->banned_until);
    }
}
