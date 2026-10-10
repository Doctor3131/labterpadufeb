<?php

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/tests/Support/DuskDatabaseGuard.php';

use Tests\Support\DuskDatabaseGuard;

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
$connection = (string) $app['config']->get('database.default');
$config = $app['config']->get("database.connections.{$connection}", []);

DuskDatabaseGuard::assertConnection(
    $connection,
    (string) ($config['database'] ?? ''),
    (string) ($config['host'] ?? ''),
    (string) $app['config']->get('app.url'),
    (string) $app['config']->get('app.env')
);

echo "Runtime Dusk database target is valid.\n";
