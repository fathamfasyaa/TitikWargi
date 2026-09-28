<?php

namespace Tests\Feature\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(today()->setTime(12, 0));
    }

    public function test_user_can_create_a_report(): void
    {
        $this->assertTrue(User::factory()->create()->can('create', Report::class));
    }

    public function test_banned_user_cannot_create_a_report(): void
    {
        $this->assertFalse(User::factory()->banned()->create()->can('create', Report::class));
    }

    public function test_user_whose_ban_has_ended_can_create_a_report_again(): void
    {
        $user = User::factory()->create(['banned_until' => now()->subMinute()]);

        $this->assertTrue($user->can('create', Report::class));
    }

    public function test_user_cannot_create_more_than_five_reports_per_day(): void
    {
        $user = User::factory()->create();
        Report::factory()->for($user)->count(4)->create();

        $this->assertTrue($user->can('create', Report::class));

        Report::factory()->for($user)->create();

        $this->assertFalse($user->can('create', Report::class));
    }

    public function test_deleted_reports_still_count_toward_the_daily_limit(): void
    {
        $user = User::factory()->create();
        Report::factory()->for($user)->count(5)->create()->each->delete();

        $this->assertFalse($user->can('create', Report::class));
    }

    public function test_reports_from_yesterday_do_not_count_toward_the_daily_limit(): void
    {
        $user = User::factory()->create();
        Report::factory()->for($user)->count(5)->create(['created_at' => now()->subDay()]);

        $this->assertTrue($user->can('create', Report::class));
    }
}
