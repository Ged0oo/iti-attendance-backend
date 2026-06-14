<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-spatie-role', function () {
            return response()->json(['message' => 'success']);
        })->middleware(\Spatie\Permission\Middleware\RoleMiddleware::using('instructor'));
        
        Route::get('/test-spatie-multiple', function () {
            return response()->json(['message' => 'success']);
        })->middleware(\Spatie\Permission\Middleware\RoleMiddleware::using('track_admin|instructor'));
    }

    public function test_user_role_fallback()
    {
        $user = User::factory()->create([
            'role' => 'instructor'
        ]);

        $this->assertTrue($user->hasRole('instructor'), 'Failed hasRole string');
        $this->assertTrue($user->hasAnyRole(['instructor']), 'Failed hasAnyRole array');
        $this->assertFalse($user->hasAnyRole(['student']), 'Failed hasAnyRole array negative');
        $this->assertTrue($user->hasAllRoles(['instructor']), 'Failed hasAllRoles');

        $response = $this->actingAs($user)->getJson('/test-spatie-role');
        $response->assertStatus(200);
        
        $response2 = $this->actingAs($user)->getJson('/test-spatie-multiple');
        $response2->assertStatus(200);
    }
}
