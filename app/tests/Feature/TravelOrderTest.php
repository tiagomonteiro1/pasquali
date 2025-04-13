<?php
namespace Tests\Feature;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_travel_order()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/orders', [
            'destination' => 'New York',
            'start_date' => '2023-12-01',
            'end_date' => '2023-12-10',
            'reason' => 'Conference attendance',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'destination' => 'New York',
                'status' => 'requested',
            ]);
    }

    public function test_user_cannot_update_status_of_own_order()
    {
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/orders/{$order->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_status_of_order()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/orders/{$order->id}/status", [
            'status' => 'approved',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'approved',
            ]);
    }

    public function test_user_can_cancel_approved_order_if_within_timeframe()
    {
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'start_date' => now()->addDays(5),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/orders/{$order->id}/cancel", [
            'reason' => 'Change of plans',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'canceled',
            ]);
    }

    public function test_user_cannot_cancel_approved_order_if_too_close()
    {
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'start_date' => now()->addDays(1),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/orders/{$order->id}/cancel", [
            'reason' => 'Change of plans',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_filter_orders_by_status()
    {
        $user = User::factory()->create();
        TravelOrder::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'approved',
        ]);
        TravelOrder::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'requested',
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/orders?status=approved');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}