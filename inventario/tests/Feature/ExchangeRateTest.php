<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_exchange_rate_is_stored(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/exchange-rates', [
            'currency' => 'USD',
            'rate_to_cup' => 120,
            'effective_date' => '2024-01-01',
        ]);

        $response->assertRedirect(route('exchange-rates.index'));
        $this->assertDatabaseHas('exchange_rates', [
            'currency' => 'USD',
            'rate_to_cup' => 120,
            'effective_date' => '2024-01-01 00:00:00',
        ]);
    }
}
