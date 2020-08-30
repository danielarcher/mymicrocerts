<?php

namespace MyCertsTests;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\Response;
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

    protected function assertErrorStructure()
    {
        $this->assertStructureIsCorrect([
            'errors' => [
                [
                    'description',
                    'code'
                ]
            ]
        ]);
    }

    public function assertResponseCreated()
    {
        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function assertResponseNoContent()
    {
        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
    }

    public function assertResponseUnprocessableEntity()
    {
        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function assertStructureIsCorrect($expectedStructure)
    {
        $returnArray = json_decode($this->response->content(), true);
        $this->assertEqualsCanonicalizing($expectedStructure, $this->array_keys_recursive($returnArray));
    }

    protected function array_keys_recursive($input, $maxDepth = INF, $depth = 0, $arrayKeys = [])
    {
        if ($depth < $maxDepth) {
            $depth++;
            $keys = array_keys($input);
            foreach ($keys as $key) {
                if (is_array($input[$key])) {
                    $arrayKeys[$key] = $this->array_keys_recursive($input[$key], $maxDepth, $depth);
                } else {
                    $arrayKeys[] = $key;
                }
            }
        }
        return $arrayKeys;
    }

    protected function assertValidationErrorStructure(int $numberOfErrors = 1)
    {
        $this->assertStructureIsCorrect([
            'errors' => array_fill(0, $numberOfErrors, [
                'description',
                'code',
                'param',
            ])
        ]);
    }

    protected function assertErrorList(int $numberOfErrors)
    {
        $this->assertStructureIsCorrect([
            'errors' => array_fill(0, $numberOfErrors, [
                'description',
                'code'
            ])
        ]);
    }
}
