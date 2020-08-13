<?php

namespace MyCertsTests;

use Faker\Factory;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication ()
    {
        return require __DIR__ . '/../lumen/bootstrap/app.php';
    }

    protected function setUp (): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }
}
