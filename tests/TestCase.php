<?php

namespace MyCertsTests;

use Faker\Factory;
use Faker\Generator;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    use RetrieveTokenTrait;

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        return require __DIR__ . '/../lumen/bootstrap/app.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }
}
