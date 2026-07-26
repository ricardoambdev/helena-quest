<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$tests = [
    '/' => 'GET',
    '/login' => 'GET',
];

foreach ($tests as $path => $method) {
    $req = Illuminate\Http\Request::create($path, $method);
    try {
        $resp = $kernel->handle($req);
        echo "{$method} {$path} → " . $resp->getStatusCode() . PHP_EOL;
    } catch (Throwable $e) {
        echo "{$method} {$path} → ERR: " . $e->getMessage() . PHP_EOL;
    }
}
