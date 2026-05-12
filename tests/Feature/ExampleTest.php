<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_boots_and_registers_the_portal_route(): void
    {
        $this->assertTrue(app()->bound('router'));
        $this->assertTrue(Route::has('portal.index'));
    }
}
