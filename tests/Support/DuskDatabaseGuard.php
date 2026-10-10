<?php

namespace Tests\Support;

use RuntimeException;

final class DuskDatabaseGuard
{
    public const DATABASE = 'labterpadu_dusk_test';

    public const APP_URL = 'http://127.0.0.1:8001';

    private const LOCAL_HOSTS = ['127.0.0.1', 'localhost', '::1'];

    /**
     * @param  array<string, mixed>  $environment
     */
    public static function assertEnvironment(array $environment): void
    {
        $connection = $environment['DB_CONNECTION'] ?? null;
        $database = $environment['DB_DATABASE'] ?? null;
        $host = $environment['DB_HOST'] ?? null;
        $appEnvironment = strtolower((string) ($environment['APP_ENV'] ?? ''));
        $appUrl = rtrim((string) ($environment['APP_URL'] ?? ''), '/');

        if ($connection !== 'mysql'
            || $database !== self::DATABASE
            || ! in_array($host, self::LOCAL_HOSTS, true)
            || $appUrl !== self::APP_URL
            || $appEnvironment !== 'local') {
            throw new RuntimeException(self::message());
        }
    }

    public static function assertConnection(
        string $connection,
        string $database,
        string $host,
        string $appUrl,
        string $appEnvironment
    ): void {
        if ($connection !== 'mysql'
            || $database !== self::DATABASE
            || ! in_array($host, self::LOCAL_HOSTS, true)
            || rtrim($appUrl, '/') !== self::APP_URL
            || strtolower($appEnvironment) !== 'local') {
            throw new RuntimeException(self::message());
        }
    }

    public static function assertDatabaseIsEmpty(int $tableCount): void
    {
        if ($tableCount !== 0) {
            throw new RuntimeException(
                'Dusk requires an empty dedicated database; found '.$tableCount.' tables or views. No database changes were made.'
            );
        }
    }

    private static function message(): string
    {
        return 'Dusk stopped before accessing the database. Configure .env.dusk.local with '
            .'DB_CONNECTION=mysql, DB_DATABASE='.self::DATABASE
            .' and a local DB_HOST (127.0.0.1, localhost, or ::1), APP_ENV=local, and APP_URL='.self::APP_URL.'.';
    }
}
