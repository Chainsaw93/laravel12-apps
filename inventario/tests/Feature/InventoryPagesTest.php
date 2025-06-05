<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_render(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/categories')->assertStatus(200);
        $this->get('/warehouses')->assertStatus(200);
        $this->get('/transfers/create')->assertStatus(200);
        $this->get('/sales')->assertStatus(200);
        $this->get('/reports')->assertStatus(200);
    }
}
