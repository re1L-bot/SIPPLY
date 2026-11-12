<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesMenuTables;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use CreatesMenuTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createMenuTables();
        $this->withoutVite();
    }

    public function test_guest_can_browse_the_public_menu(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Find your kind of sip')
            ->assertSee('Sign in');
    }
}
