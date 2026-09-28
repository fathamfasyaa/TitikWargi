<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class GoogleAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_sends_the_visitor_to_google(): void
    {
        Socialite::fake('google');

        $this->get(route('login'))->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_login_remembers_the_page_to_return_to(): void
    {
        Socialite::fake('google');

        $this->get(route('login', ['back' => '/reports/5']));

        $this->assertSame(url('/reports/5'), session('url.intended'));
    }

    #[TestWith(['//evil.example.com'])]
    #[TestWith(['https://evil.example.com'])]
    #[TestWith(['/\\evil.example.com'])]
    public function test_login_ignores_a_return_address_on_another_site(string $back): void
    {
        Socialite::fake('google');

        $this->get(route('login', ['back' => $back]));

        $this->assertNull(session('url.intended'));
    }

    public function test_first_login_creates_a_regular_user(): void
    {
        Socialite::fake('google', $this->googleUser());

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect('/');
        $user = User::sole();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('123456789', $user->google_id);
        $this->assertSame('warga@example.com', $user->email);
        $this->assertSame('https://example.com/avatar.jpg', $user->avatar);
        $this->assertSame(UserRole::User, $user->role);
    }

    public function test_next_login_updates_the_same_user_without_creating_another(): void
    {
        $existing = User::factory()->create(['google_id' => '123456789', 'name' => 'Nama Lama']);
        Socialite::fake('google', $this->googleUser());

        $this->get(route('auth.google.callback'));

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('Warga Cianjur', $existing->fresh()->name);
        $this->assertAuthenticatedAs($existing);
    }

    public function test_login_links_google_to_an_existing_account_with_the_same_email(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'warga@example.com', 'google_id' => null]);
        Socialite::fake('google', $this->googleUser());

        $this->get(route('auth.google.callback'));

        $admin->refresh();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('123456789', $admin->google_id);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_login_with_an_unverified_google_email_is_rejected(): void
    {
        Socialite::fake('google', $this->googleUser(emailVerified: false));

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect('/')->assertSessionHas('error');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_failed_google_login_shows_an_error(): void
    {
        Socialite::fake('google', fn () => throw new InvalidStateException);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect('/')->assertSessionHas('error', 'Login dengan Google gagal. Silakan coba lagi.');
        $this->assertGuest();
    }

    public function test_user_logs_out(): void
    {
        $this->actingAs(User::factory()->create())->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
    }

    private function googleUser(bool $emailVerified = true): GoogleUser
    {
        return (new GoogleUser)
            ->map([
                'id' => '123456789',
                'name' => 'Warga Cianjur',
                'email' => 'warga@example.com',
                'avatar' => 'https://example.com/avatar.jpg',
            ])
            ->setRaw(['email_verified' => $emailVerified]);
    }
}
