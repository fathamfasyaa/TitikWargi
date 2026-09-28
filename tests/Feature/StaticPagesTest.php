<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaticPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_is_public(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('Kebijakan Privasi');
    }

    public function test_community_guidelines_page_is_public(): void
    {
        $this->get(route('community-guidelines'))->assertOk()->assertSee('Aturan Komunitas');
    }

    public function test_footer_links_to_both_pages(): void
    {
        $this->get(route('home'))
            ->assertSee(route('privacy'))
            ->assertSee(route('community-guidelines'));
    }
}
