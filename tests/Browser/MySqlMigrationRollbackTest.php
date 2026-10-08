<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class MySqlMigrationRollbackTest extends DuskTestCase
{
    public function test_fresh_migrations_can_be_rolled_back_on_mysql(): void
    {
        $this->artisan('migrate:rollback')->assertExitCode(0);
    }
}
