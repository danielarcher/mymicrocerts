<?php

namespace MyCerts\Application;

use Illuminate\Support\Facades\Hash;
use MyCerts\Domain\Model\Exam;
use Ramsey\Uuid\Uuid;

class ExamHandler
{
    /**
     * @param string|null $title
     * @param string|null $description
     * @param int|null    $max_time_in_minutes
     * @param int|null    $max_attempts_per_candidate
     * @param string|null $success_score_in_percent
     * @param bool|null   $visible_internal
     * @param bool|null   $visible_external
     * @param bool|null   $private
     * @param string|null $company_id
     * @param string|null $password
     * @param array|null  $fixed_questions
     * @param array|null  $questions_per_categories
     *
     * @return Exam
     */
    public function create(
        ?string $title,
        ?string $description,
        ?int $max_time_in_minutes,
        ?int $max_attempts_per_candidate,
        ?string $success_score_in_percent,
        ?bool $visible_internal,
        ?bool $visible_external,
        ?bool $private,
        ?string $company_id,
        ?string $password,
        ?array $fixed_questions,
        ?array $questions_per_categories
    ): Exam {

        $exam = new Exam(array_filter([
            'company_id'                 => $company_id,
            'title'                      => $title,
            'description'                => $description,
            'max_time_in_minutes'        => $max_time_in_minutes,
            'max_attempts_per_candidate' => $max_attempts_per_candidate,
            'success_score_in_percent'   => $success_score_in_percent,
            'visible_internal'           => $visible_internal,
            'visible_external'           => $visible_external,
            'private'                    => $private,
        ]));

        if ($visible_external) {
            $exam->access_id       = base64_encode(Uuid::uuid4()->toString());
            $exam->access_password = $password ? Hash::make($password) : null;
            $exam->link            = route('external.index', ['id' => $exam->access_id]);
        }

        $exam->save();

        if ($fixed_questions) {
            $exam->fixedQuestions()->sync($fixed_questions);
        }
        if ($questions_per_categories) {
            $exam->questionsPerCategory()->sync($questions_per_categories);
        }

        return $exam;
    }
}