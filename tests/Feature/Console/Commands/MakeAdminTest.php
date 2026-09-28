<?php

namespace Tests\Feature\Console\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_gives_an_existing_user_the_admin_role(): void
    {
        $user = User::factory()->create(['email' => 'warga@example.com']);

        $this->artisan('user:make-admin', ['email' => 'warga@example.com'])->assertSuccessful();

        $this->assertTrue($user->fresh()->isAdmin());
    }

    public function test_fails_when_the_email_is_unknown(): void
    {
        $this->artisan('user:make-admin', ['email' => 'nobody@example.com'])
            ->expectsOutputToContain('User not found')
            ->assertFailed();
    }
}
