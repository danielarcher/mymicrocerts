<?php

namespace MyCertsTests;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;

final class TestListener implements BeforeFirstTestHook, AfterLastTestHook
{
    public function executeBeforeFirstTest(): void
    {
        echo shell_exec('touch /var/www/lumen/database/database.sqlite');
        echo shell_exec('APP_ENV=testing php /var/www/lumen/artisan migrate:refresh --seed -q');
    }

    public function executeAfterLastTest(): void
    {
        echo shell_exec('rm -rf /var/www/lumen/database/database.sqlite');
    }
}