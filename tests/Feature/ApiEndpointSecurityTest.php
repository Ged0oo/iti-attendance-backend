<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;

class ApiEndpointSecurityTest extends TestCase
{
    public static function apiRoutesProvider()
    {
        // This provider cannot easily resolve the app instance dynamically in data provider,
        // so we'll fetch the routes directly in the test method.
        return [[true]];
    }

    #[Test]
    public function all_api_routes_require_authentication()
    {
        $routes = Route::getRoutes()->getRoutes();
        $apiRoutes = [];
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            // public auth routes (you are not signed in when you hit them) are exempt
            $publicAuth = ['login', 'logout', 'forgot-password', 'reset-password'];
            $isPublic = array_filter($publicAuth, fn ($p) => str_contains($uri, $p));
            if (str_starts_with($uri, 'api/') && !in_array('login', $route->middleware()) && empty($isPublic)) {
                $apiRoutes[] = $route;
            }
        }

        $this->assertNotEmpty($apiRoutes, "No API routes found to test.");

        $failedRoutes = [];

        foreach ($apiRoutes as $route) {
            $middlewares = $route->gatherMiddleware();
            
            // If it's explicitly an API route, we expect auth:sanctum and check.expiry unless it's a login route.
            if (!in_array('auth:sanctum', $middlewares) || !in_array('check.expiry', $middlewares)) {
                $failedRoutes[] = $route->uri() . ' (' . implode('|', $route->methods()) . ') is missing auth:sanctum or check.expiry middleware.';
                continue;
            }

            // Test unauthenticated access
            $uri = $route->uri();
            
            // Replace parameters with dummy values
            $uri = preg_replace('/\{.*?\}/', '1', $uri);
            
            $method = $route->methods()[0];
            if ($method === 'HEAD') {
                continue;
            }
            
            $response = $this->json($method, '/' . $uri);
            
            if ($response->status() !== 401) {
                $failedRoutes[] = $route->uri() . ' [' . $method . '] did not return 401 Unauthenticated. Returned: ' . $response->status();
            }
        }

        $this->assertEmpty($failedRoutes, "Failed Security Checks:\n" . implode("\n", $failedRoutes));
    }
    
    #[Test]
    public function it_generates_api_documentation_report()
    {
        $routes = Route::getRoutes()->getRoutes();
        $markdown = "# API Discovery & Documentation\n\n";
        $markdown .= "| Method | URI | Middleware | Action |\n";
        $markdown .= "|---|---|---|---|\n";
        
        foreach ($routes as $route) {
            if (str_starts_with($route->uri(), 'api/')) {
                $method = implode('|', array_filter($route->methods(), fn($m) => $m !== 'HEAD'));
                $uri = $route->uri();
                $middleware = implode(', ', $route->gatherMiddleware());
                $action = $route->getActionName();
                $markdown .= "| {$method} | `{$uri}` | {$middleware} | {$action} |\n";
            }
        }
        
        file_put_contents(base_path('API_DOCUMENTATION.md'), $markdown);
        $this->assertFileExists(base_path('API_DOCUMENTATION.md'));
    }
}
