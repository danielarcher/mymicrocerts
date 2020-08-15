<?php

namespace MyCertsTests\System;

use MyCerts\Domain\Model\Certificate;
use MyCertsTests\TestCase;

class PerformExamTest extends TestCase
{
    public function test_it_should_create_complete_exam_entities_and_be_approved()
    {
        /**
         * Create category
         */
        $this->json('POST', '/api/category', [
            "name" => $this->faker->word
        ], ['Authorization' => $this->companyToken()]);

        $categoryId = $this->response->getOriginalContent()->id;

        /**
         * Create question
         */
        $questionPayload = [
            "description" => $this->faker->paragraph,
            "categories"  => [$categoryId],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);

        /**
         * Create exam
         */

        $examPayload = [
            "title"                    => $this->faker->jobTitle,
            "description"              => $this->faker->paragraph,
            "visible_external"         => false,
            "success_score_in_percent" => 100,
            "questions_per_categories" => [
                [
                    "category_id"           => $categoryId,
                    "quantity_of_questions" => 1
                ]
            ]
        ];

        $this->json('POST', '/api/exam', $examPayload, ['Authorization' => $this->companyToken()]);
        $exam = $this->response->getOriginalContent();

        /**
         * Get Plan and Buy Credits
         */
        $this->json('GET', '/api/plans');
        $plan = $this->response->getOriginalContent()->first();
        $this->json('POST', "/api/plans/{$plan->id}/buy", $this->faker->creditCardDetails,
            ['Authorization' => $this->companyToken()]);

        /**
         * Get Candidate
         */
        $candidate = $this->json('GET', '/api/me')->response->getOriginalContent();

        /**
         * Start Exam
         */
        $data      = $this->json('POST', "/api/exam/{$exam->id}/start", [
            'candidate_id' => $candidate->id
        ], ['Authorization' => $this->companyToken()])
            ->response
            ->getOriginalContent();
        $attempt   = $data['attempt'];
        $questions = $data['questions'];

        /**
         * Finish Exam
         */
        $finishExamPayload = [
            "candidate_id" => $candidate->id,
            "attempt_id"   => $attempt->id,
            "answers"      => [
                [
                    "question_id"         => $questions->first()->id,
                    "selected_option_ids" => [
                        $questions->first()->options()->first()->id
                    ]
                ]
            ]
        ];

        $this->json(
            'POST',
            "/api/exam/{$exam->id}/finish",
            $finishExamPayload,
            ['Authorization' => $this->companyToken()]
        );
        $finishData      = $this->response->getOriginalContent();
        $attemptFinished = $finishData['attempt'];
        $certificate     = $finishData['certificate'];

        $this->assertTrue($attemptFinished->approved);
        $this->assertInstanceOf(Certificate::class, $certificate);
    }

    public function test_it_should_not_be_approved_for_wrong_answer()
    {

        /**
         * Create category
         */
        $this->json('POST', '/api/category', [
            "name" => $this->faker->word
        ], ['Authorization' => $this->companyToken()]);

        $categoryId = $this->response->getOriginalContent()->id;

        /**
         * Create question
         */
        $questionPayload = [
            "description" => $this->faker->paragraph,
            "categories"  => [$categoryId],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);

        /**
         * Create exam
         */

        $examPayload = [
            "title"                    => $this->faker->jobTitle,
            "description"              => $this->faker->paragraph,
            "visible_external"         => false,
            "success_score_in_percent" => 100,
            "questions_per_categories" => [
                [
                    "category_id"           => $categoryId,
                    "quantity_of_questions" => 1
                ]
            ]
        ];

        $this->json('POST', '/api/exam', $examPayload, ['Authorization' => $this->companyToken()]);
        $exam = $this->response->getOriginalContent();

        /**
         * Get Plan and Buy Credits
         */
        $this->json('GET', '/api/plans');
        $plan = $this->response->getOriginalContent()->first();
        $this->json('POST', "/api/plans/{$plan->id}/buy", $this->faker->creditCardDetails,
            ['Authorization' => $this->companyToken()]);

        /**
         * Get Candidate
         */
        $candidate = $this->json('GET', '/api/me')->response->getOriginalContent();

        /**
         * Start Exam
         */
        $data      = $this->json('POST', "/api/exam/{$exam->id}/start", [
            'candidate_id' => $candidate->id
        ], ['Authorization' => $this->companyToken()])
            ->response
            ->getOriginalContent();
        $attempt   = $data['attempt'];
        $questions = $data['questions'];

        /**
         * Finish Exam
         */
        $finishExamPayload = [
            "candidate_id" => $candidate->id,
            "attempt_id"   => $attempt->id,
            "answers"      => [
                [
                    "question_id"         => $questions->first()->id,
                    "selected_option_ids" => [
                        $this->faker->uuid
                    ]
                ]
            ]
        ];

        $this->json(
            'POST',
            "/api/exam/{$exam->id}/finish",
            $finishExamPayload,
            ['Authorization' => $this->companyToken()]
        );
        $finishData      = $this->response->getOriginalContent();
        $attemptFinished = $finishData['attempt'];

        $this->assertFalse($attemptFinished->approved);
    }
}