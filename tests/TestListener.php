<?php

namespace MyCertsTests;

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;

final class TestListener implements BeforeFirstTestHook, AfterLastTestHook
{
    public function executeBeforeFirstTest(): void
    {
        echo shell_exec('php /var/www/lumen/artisan migrate:refresh --seed -q');
    }

    public function executeAfterLastTest(): void
    {
    }
}