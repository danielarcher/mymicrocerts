<?php

namespace MyCertsTests\System;

use Illuminate\Http\Response;
use MyCerts\Domain\Model\Candidate;
use MyCerts\Domain\Model\Category;
use MyCerts\Domain\Model\Certificate;
use MyCertsTests\TestCase;

class PerformExamTest extends TestCase
{
    /**
     * @var Category
     */
    protected $category;
    /**
     * @var Candidate
     */
    protected $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * Get Candidate
         */
        $this->candidate = $this->json('GET', '/api/me',
            [], ['Authorization' => $this->companyToken()])->response->getOriginalContent();

        /**
         * Get Plan and Buy Credits
         */
        $plan = $this->json('GET', '/api/plans')->response->getOriginalContent()->first();
        $this->json('POST', "/api/plans/{$plan->id}/buy", $this->faker->creditCardDetails,
            ['Authorization' => $this->companyToken()]);

        /**
         * Create category
         */
        $this->json('POST', '/api/category', [
            "name" => $this->faker->word
        ], ['Authorization' => $this->companyToken()]);

        $this->category = $this->response->getOriginalContent();

        /**
         * Create question
         */
        $questionPayload = [
            "description" => $this->faker->paragraph,
            "categories"  => [$this->category->id],
            "options"     => [
                [
                    "text"    => $this->faker->text,
                    "correct" => true
                ]
            ]
        ];
        $this->json('POST', '/api/question', $questionPayload, ['Authorization' => $this->companyToken()]);
    }

    public function test_it_should_create_complete_exam_entities_and_be_approved()
    {
        /**
         * Create exam
         */
        $this->json('POST',
            '/api/exam',
            [
                "title"                      => $this->faker->jobTitle,
                "description"                => $this->faker->paragraph,
                "visible_external"           => false,
                "success_score_in_percent"   => 100,
                "questions_per_categories"   => [
                    [
                        "category_id"           => $this->category->id,
                        "quantity_of_questions" => 1
                    ]
                ]
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $exam = $this->response->getOriginalContent();

        /**
         * Start Exam
         */
        $this->json(
            'POST',
            "/api/exam/{$exam->id}/start",
            [
                'candidate_id' => $this->candidate->id
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );

        $examStartResponse = $this->response->getOriginalContent();
        $attempt           = $examStartResponse['attempt'];
        $questions         = $examStartResponse['questions'];

        /**
         * Finish Exam
         */
        $this->json(
            'POST',
            "/api/exam/{$exam->id}/finish",
            [
                "candidate_id" => $this->candidate->id,
                "attempt_id"   => $attempt->id,
                "answers"      => [
                    [
                        "question_id"         => $questions->first()->id,
                        "selected_option_ids" => [
                            $questions->first()->options()->first()->id
                        ]
                    ]
                ]
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $examFinishResponse = $this->response->getOriginalContent();
        $attemptFinished    = $examFinishResponse['attempt'];
        $certificate        = $examFinishResponse['certificate'];

        $this->assertTrue($attemptFinished->approved);
        $this->assertInstanceOf(Certificate::class, $certificate);
    }

    public function test_it_should_not_be_approved_for_wrong_answer()
    {
        /**
         * Create exam
         */
        $this->json('POST',
            '/api/exam',
            [
                "title"                      => $this->faker->jobTitle,
                "description"                => $this->faker->paragraph,
                "visible_external"           => false,
                "success_score_in_percent"   => 100,
                "questions_per_categories"   => [
                    [
                        "category_id"           => $this->category->id,
                        "quantity_of_questions" => 1
                    ]
                ]
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $exam = $this->response->getOriginalContent();

        /**
         * Start Exam
         */
        $this->json(
            'POST',
            "/api/exam/{$exam->id}/start",
            [
                'candidate_id' => $this->candidate->id
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );

        $examStartResponse = $this->response->getOriginalContent();
        $attempt           = $examStartResponse['attempt'];
        $questions         = $examStartResponse['questions'];

        /**
         * Finish Exam
         */
        $this->json(
            'POST',
            "/api/exam/{$exam->id}/finish",
            [
                "candidate_id" => $this->candidate->id,
                "attempt_id"   => $attempt->id,
                "answers"      => [
                    [
                        "question_id"         => $questions->first()->id,
                        "selected_option_ids" => [
                            $this->faker->uuid
                        ]
                    ]
                ]
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $examFinishResponse = $this->response->getOriginalContent();
        $attemptFinished    = $examFinishResponse['attempt'];

        $this->assertFalse($attemptFinished->approved);
    }

    public function test_it_should_block_when_the_same_candidate_try_more_then_configured()
    {
        /**
         * Create exam
         */
        $this->json('POST',
            '/api/exam',
            [
                "title"                      => $this->faker->jobTitle,
                "description"                => $this->faker->paragraph,
                "visible_external"           => false,
                "max_attempts_per_candidate" => 1,
                "success_score_in_percent"   => 100,
                "questions_per_categories"   => [
                    [
                        "category_id"           => $this->category->id,
                        "quantity_of_questions" => 1
                    ]
                ]
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $exam = $this->response->getOriginalContent();

        /**
         * Start Exam First time
         */
        $this->json(
            'POST',
            "/api/exam/{$exam->id}/start",
            [
                'candidate_id' => $this->candidate->id
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );

        /**
         * Start Exam Second time
         */
        $this->json(
            'POST',
            "/api/exam/{$exam->id}/start",
            [
                'candidate_id' => $this->candidate->id
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $this->assertResponseStatus(Response::HTTP_CONFLICT);
    }

    public function test_should_not_create_exam_with_non_existing_questions()
    {
        /**
         * Create exam
         */
        $this->json('POST',
            '/api/exam',
            [
                "title"                      => $this->faker->jobTitle,
                "description"                => $this->faker->paragraph,
                "visible_external"           => false,
                "success_score_in_percent"   => 100,
                "fixed_questions" => [
                    $this->faker->uuid
                ]
            ],
            [
                'Authorization' => $this->companyToken()
            ]
        );
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }
}