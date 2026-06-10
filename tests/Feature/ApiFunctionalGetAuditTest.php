<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Track;
use App\Models\Cohort;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class ApiFunctionalGetAuditTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_successfully_access_all_get_endpoints_without_crashing()
    {
        // Setup initial state to prevent 404s on route bindings where possible
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        
        // We bypass RoleMiddleware by using a mock or simply ignoring authorization for this specific audit,
        // but since Laravel evaluates middleware, we'll assign the highest role if Spatie is used, 
        // or we just catch 403s as acceptable responses for restricted routes.
        
        // Let's create dummy records for common route parameters to avoid 404s
        // We will do this safely in a transaction or just using DB
        try {
            DB::table('tracks')->insert(['id' => 1, 'name' => 'Audit Track', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('cohorts')->insert(['id' => 1, 'track_id' => 1, 'name' => 'Audit Cohort', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('students')->insert(['id' => 1, 'user_id' => $user->id, 'cohort_id' => 1, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('courses')->insert(['id' => 1, 'name' => 'Audit Course', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('lab_groups')->insert(['id' => 1, 'cohort_id' => 1, 'name' => 'Audit Group', 'created_at' => now(), 'updated_at' => now()]);
        } catch (\Exception $e) {
            // Ignore if tables don't exist yet or constraints fail
        }

        $routes = Route::getRoutes()->getRoutes();
        $getRoutes = [];
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            
            if (str_starts_with($uri, 'api/') && in_array('GET', $methods) && !str_contains($uri, 'login')) {
                $getRoutes[] = $route;
            }
        }

        $failedRoutes = [];

        foreach ($getRoutes as $route) {
            $uri = $route->uri();
            
            // Replace parameters with ID 1
            $uri = preg_replace('/\{.*?\}/', '1', $uri);
            
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json'
            ])->get('/' . $uri);
            
            $status = $response->status();
            
            // Acceptable statuses for a generic audit:
            // 200 OK
            // 403 Forbidden (if the user lacks the specific role)
            // 404 Not Found (if the dummy ID 1 doesn't exist for a specific table)
            // 422 Unprocessable (if query params are strictly validated)
            if ($status === 500) {
                $failedRoutes[] = "GET /$uri returned 500 Internal Server Error. Response: " . $response->getContent();
            }
        }

        $this->assertEmpty($failedRoutes, "Functional GET Robustness Failures:\n" . implode("\n", $failedRoutes));
    }
}
