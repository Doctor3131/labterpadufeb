<?php

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/tests/Support/DuskDatabaseGuard.php';

use Tests\Support\DuskDatabaseGuard;

$environmentFile = dirname(__DIR__).'/.env.dusk.local';

if (! is_file($environmentFile)) {
    fwrite(STDERR, "Missing .env.dusk.local; no database was accessed.\n");
    exit(2);
}

try {
    $environment = Dotenv\Dotenv::parse(file_get_contents($environmentFile));
    DuskDatabaseGuard::assertEnvironment($environment);

    $host = $environment['DB_HOST'];
    $port = $environment['DB_PORT'] ?? '3306';
    $database = $environment['DB_DATABASE'];
    $username = $environment['DB_USERNAME'] ?? '';
    $password = $environment['DB_PASSWORD'] ?? '';
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $objectCount = (int) $pdo->query(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()'
    )->fetchColumn();

    DuskDatabaseGuard::assertDatabaseIsEmpty($objectCount);
    fwrite(STDOUT, "Dedicated Dusk database is empty.\n");
} catch (RuntimeException $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
} catch (Throwable) {
    fwrite(STDERR, "Dusk could not verify that the dedicated database is empty; no changes were made.\n");
    exit(1);
}
