<?php

namespace MyCertsTests\System;

use MyCertsTests\TestCase;

class QuestionTest extends TestCase
{
    public function test_it_should_create_question()
    {
        /**
         * Create question
         */
        $optionText      = $this->faker->text;
        $description     = $this->faker->paragraph;
        $questionPayload = [
            "description" => $description,
            "categories"  => [],
            "options"     => [
                [
                    "text"    => $optionText,
                    "correct" => true
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);
        $question = $this->response->getOriginalContent();

        $this->assertResponseCreated();
        $this->seeInDatabase('question', ['description' => $description]);
        $this->seeInDatabase('option', ['question_id' => $question->id, 'text' => $optionText, 'correct' => true]);
    }

    public function test_it_should_stop_invalid_payload_with_tree_errors()
    {
        /**
         * Create question
         */
        $optionText      = $this->faker->text;
        $description     = $this->faker->paragraph;
        $questionPayload = [
            "description" => $description,
            "categories"  => "test", // 1. must be array
            "options"     => [
                [
                    // 2. missing text
                    "correct" => true
                ],
                [
                    'text' => $optionText,
                    "correct" => "true" // 3. must be boolean
                ],
                [
                    'text' => $optionText,
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);

        $this->assertResponseUnprocessableEntity();
        $this->assertValidationErrorStructure(3);
    }

    public function test_it_should_delete_question()
    {
        /**
         * Create question
         */
        $description     = $this->faker->paragraph;
        $questionPayload = [
            "description" => $description,
            "categories"  => [],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);
        $question = $this->response->getOriginalContent();

        /**
         * delete
         */
        $this->json('DELETE', '/api/question/' . $question->id, [], ['Authorization' => $this->companyToken()]);
        $this->assertResponseNoContent();
        $this->notSeeInDatabase('question', ['description' => $description, 'deleted_at' => null]);
    }

    public function test_it_should_update_question()
    {
        /**
         * Create question
         */
        $description     = $this->faker->paragraph;
        $questionPayload = [
            "description" => $description,
            "categories"  => [$this->faker->uuid, $this->faker->uuid],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);
        $question = $this->response->getOriginalContent();

        /**
         * update
         */
        $newDescription  = $this->faker->paragraph;
        $newOptionText = $this->faker->text;
        $questionPayload = [
            "description" => $newDescription,
            "categories"  => [$this->faker->uuid, $this->faker->uuid],
            "options"     => [
                [
                    "text"    => $newOptionText,
                    "correct" => true
                ]
            ]
        ];

        $this->json('PATCH', '/api/question/' . $question->id, $questionPayload,
            ['Authorization' => $this->companyToken()]);
        $this->assertResponseOk();
        $this->seeInDatabase('question', ['id' => $question->id, 'description' => $newDescription, 'deleted_at' => null]);
        $this->seeInDatabase('option', ['question_id' => $question->id, 'text' => $newOptionText, 'deleted_at' => null]);
    }

}