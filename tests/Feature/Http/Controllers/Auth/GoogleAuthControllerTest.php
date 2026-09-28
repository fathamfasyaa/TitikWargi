<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Laravel\Socialite\Socialite;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class GoogleAuthControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Socialite::fake('google');
    }

    public function test_login_remembers_the_page_to_return_to(): void
    {
        $this->get(route('login', ['back' => '/reports/5']));

        $this->assertSame(url('/reports/5'), session('url.intended'));
    }

    #[TestWith(['//evil.example.com'])]
    #[TestWith(['https://evil.example.com'])]
    #[TestWith(['/\\evil.example.com'])]
    public function test_login_ignores_a_return_address_on_another_site(string $back): void
    {
        $this->get(route('login', ['back' => $back]));

        $this->assertNull(session('url.intended'));
    }
}
