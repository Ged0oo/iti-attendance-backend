<?php
$routesJson = shell_exec('php artisan route:list --json');
$routes = json_decode($routesJson, true);

$collection = [
    'info' => [
        'name' => 'ITI Attendance & Grading Platform API',
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    ],
    'item' => []
];

// Group by prefix (first segment of URI)
$folders = [];

foreach ($routes as $route) {
    if (!str_starts_with($route['uri'], 'api/')) {
        continue;
    }
    
    $uriSegments = explode('/', $route['uri']);
    $folderName = isset($uriSegments[1]) ? ucfirst($uriSegments[1]) : 'Other';
    
    // Some basic formatting
    $method = explode('|', $route['method'])[0];
    if ($method === 'HEAD') continue;

    $urlParts = explode('/', $route['uri']);
    $postmanUrlParts = [];
    $variables = [];
    foreach ($urlParts as $part) {
        if (preg_match('/\{(.+?)\}/', $part, $matches)) {
            $varName = str_replace('?', '', $matches[1]);
            $postmanUrlParts[] = ':' . $varName;
            $variables[] = [
                'key' => $varName,
                'value' => ''
            ];
        } else {
            $postmanUrlParts[] = $part;
        }
    }

    $item = [
        'name' => $method . ' /' . $route['uri'],
        'request' => [
            'method' => $method,
            'header' => [
                [
                    'key' => 'Accept',
                    'value' => 'application/json'
                ],
                [
                    'key' => 'Authorization',
                    'value' => 'Bearer {{token}}'
                ]
            ],
            'url' => [
                'raw' => '{{base_url}}/' . implode('/', $postmanUrlParts),
                'host' => [
                    '{{base_url}}'
                ],
                'path' => $postmanUrlParts,
                'variable' => $variables
            ]
        ]
    ];
    
    // Determine if it needs body
    if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $item['request']['body'] = [
            'mode' => 'raw',
            'raw' => "{\n\n}",
            'options' => [
                'raw' => [
                    'language' => 'json'
                ]
            ]
        ];
    }

    $folders[$folderName][] = $item;
}

foreach ($folders as $folderName => $items) {
    $collection['item'][] = [
        'name' => $folderName,
        'item' => $items
    ];
}

file_put_contents('postman_collection.json', json_encode($collection, JSON_PRETTY_PRINT));
echo "postman_collection.json generated successfully.\n";
