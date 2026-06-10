<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/login', 'POST', [
    'email' => 'manager@iti.gov.eg',
    'password' => 'password',
]);
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
$content = json_decode($response->getContent(), true);
$token = $content['token'] ?? null;

if (!$token) {
    echo "Login Failed!\n";
    exit(1);
}

echo "Login Successful! Token retrieved.\n";
echo "Testing all GET endpoints...\n";

$routes = \Illuminate\Support\Facades\Route::getRoutes()->getRoutes();
$failed = 0;
$success = 0;

foreach ($routes as $route) {
    $uri = $route->uri();
    if (str_starts_with($uri, 'api/') && in_array('GET', $route->methods()) && !str_contains($uri, 'login')) {
        // Replace {param} with 1
        $testUri = '/' . preg_replace('/\{.*?\}/', '1', $uri);
        
        $req = Illuminate\Http\Request::create($testUri, 'GET');
        $req->headers->set('Accept', 'application/json');
        $req->headers->set('Authorization', 'Bearer ' . $token);
        
        $res = $kernel->handle($req);
        $status = $res->getStatusCode();
        
        // 500 is what we want to avoid. 403, 404, 200 are generally acceptable in this blunt audit.
        if ($status >= 500) {
            echo "[FAIL] GET $testUri returned $status\n";
            $failed++;
        } else {
            // echo "[OK] GET $testUri returned $status\n";
            $success++;
        }
    }
}

echo "\nSummary: $success endpoints passed (no 500s), $failed endpoints failed.\n";

