<?php

namespace MyCertsTests;

use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\AfterLastTestHook;

final class TestListener implements BeforeFirstTestHook, AfterLastTestHook
{
    public function executeBeforeFirstTest(): void
    {

    }

    public function executeAfterLastTest(): void
    {
        shell_exec('php lumen/artisan migration:refresh --seed');
        echo 'Database refreshed...';
    }
}