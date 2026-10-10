<?php

namespace Tests\Unit;

use RuntimeException;
use Tests\Support\DuskDatabaseGuard;
use Tests\TestCase;

class DuskDatabaseGuardTest extends TestCase
{
    public function test_fast_suite_forces_testing_environment_and_in_memory_sqlite(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    public function test_accepts_only_the_named_local_mysql_test_database(): void
    {
        $this->expectNotToPerformAssertions();

        DuskDatabaseGuard::assertEnvironment([
            'APP_ENV' => 'local',
            'APP_URL' => DuskDatabaseGuard::APP_URL,
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => DuskDatabaseGuard::DATABASE,
            'DB_HOST' => '127.0.0.1',
        ]);
        DuskDatabaseGuard::assertConnection(
            'mysql',
            DuskDatabaseGuard::DATABASE,
            '127.0.0.1',
            DuskDatabaseGuard::APP_URL,
            'local'
        );
    }

    public function test_rejects_an_unexpected_database_name(): void
    {
        $this->expectException(RuntimeException::class);

        DuskDatabaseGuard::assertEnvironment([
            'APP_ENV' => 'local',
            'APP_URL' => DuskDatabaseGuard::APP_URL,
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => 'labterpadu',
            'DB_HOST' => '127.0.0.1',
        ]);
    }

    public function test_rejects_a_non_mysql_connection(): void
    {
        $this->expectException(RuntimeException::class);

        DuskDatabaseGuard::assertEnvironment([
            'APP_ENV' => 'local',
            'APP_URL' => DuskDatabaseGuard::APP_URL,
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => DuskDatabaseGuard::DATABASE,
            'DB_HOST' => '127.0.0.1',
        ]);
    }

    public function test_rejects_a_non_local_database_host(): void
    {
        $this->expectException(RuntimeException::class);

        DuskDatabaseGuard::assertEnvironment([
            'APP_ENV' => 'local',
            'APP_URL' => DuskDatabaseGuard::APP_URL,
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => DuskDatabaseGuard::DATABASE,
            'DB_HOST' => 'production.example.com',
        ]);
    }

    public function test_rejects_production_environment(): void
    {
        $this->expectException(RuntimeException::class);

        DuskDatabaseGuard::assertEnvironment([
            'APP_ENV' => 'production',
            'APP_URL' => DuskDatabaseGuard::APP_URL,
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => DuskDatabaseGuard::DATABASE,
            'DB_HOST' => '127.0.0.1',
        ]);
    }

    public function test_rejects_a_non_local_application_url(): void
    {
        $this->expectException(RuntimeException::class);

        DuskDatabaseGuard::assertEnvironment([
            'APP_ENV' => 'local',
            'APP_URL' => 'https://labterpadu.example.com',
            'DB_CONNECTION' => 'mysql',
            'DB_DATABASE' => DuskDatabaseGuard::DATABASE,
            'DB_HOST' => '127.0.0.1',
        ]);
    }

    public function test_accepts_an_empty_test_database(): void
    {
        $this->expectNotToPerformAssertions();

        DuskDatabaseGuard::assertDatabaseIsEmpty(0);
    }

    public function test_rejects_a_test_database_that_already_has_tables(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No database changes were made.');

        DuskDatabaseGuard::assertDatabaseIsEmpty(1);
    }

    public function test_runtime_guard_rejects_production_environment(): void
    {
        $this->expectException(RuntimeException::class);

        DuskDatabaseGuard::assertConnection(
            'mysql',
            DuskDatabaseGuard::DATABASE,
            '127.0.0.1',
            DuskDatabaseGuard::APP_URL,
            'production'
        );
    }
}
