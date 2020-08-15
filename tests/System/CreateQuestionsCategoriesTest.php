<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCertsTests\TestCase;

class CreateQuestionsCategoriesTest extends TestCase
{
    public function test_it_should_create_question_without_category()
    {
        $payload = [
            "description" => $this->faker->paragraph,
            "categories"  => [],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ],
                [
                    "text" => $this->faker->text
                ],
                [
                    "text" => $this->faker->text
                ]
            ]
        ];

        $this->json('POST', '/api/question', $payload, ['Authorization' => $this->companyToken()]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function test_it_should_create_question_with_many_categories()
    {
        $payload = [
            "description" => $this->faker->paragraph,
            "categories"  => [
                $this->faker->uuid,
                $this->faker->uuid,
                $this->faker->uuid
            ],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ],
                [
                    "text" => $this->faker->text
                ],
                [
                    "text" => $this->faker->text
                ]
            ]
        ];

        $this->json('POST', '/api/question', $payload, ['Authorization' => $this->companyToken()]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }

    public function test_it_should_create_category()
    {
        $this->json('POST', '/api/category', ["name" => $this->faker->word], ['Authorization' => $this->companyToken()]);

        $this->assertResponseStatus(Response::HTTP_CREATED);
    }
}