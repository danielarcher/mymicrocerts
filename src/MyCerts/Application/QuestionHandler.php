<?php

namespace MyCerts\Application;

use MyCerts\Domain\Model\Question;

class QuestionHandler
{
    /**
     * @param string   $companyId
     * @param string   $description
     * @param string[] $categories
     * @param array    $options
     * @param int|null $questionNumber
     *
     * @return Question
     */
    public function create(
        string $companyId,
        string $description,
        array $categories,
        array $options,
        ?int $questionNumber
    ): Question {

        $question = new Question(array_filter([
            'company_id'  => $companyId,
            'number'      => $questionNumber,
            'description' => $description,
        ]));
        $question->save();
        $question->categories()->sync($categories);

        array_walk($options, function (&$answer) use ($question) {
            $question->options()->create([
                'text'        => $answer['text'],
                'correct'     => $answer['correct'] ?? false,
            ]);
        });

        return $question;
    }

    /**
     * @param string      $companyId
     * @param string      $questionId
     * @param string|null $description
     * @param array|null  $categories
     * @param array|null  $options
     * @param int|null    $questionNumber
     *
     * @return Question
     */
    public function update(
        string $companyId,
        string $questionId,
        ?string $description,
        ?array $categories,
        ?array $options,
        ?int $questionNumber
    ): Question {

        $question = Question::where(['id' => $questionId, 'company_id' => $companyId])->first();

        if ($description) {
            $question->description = $description;
        }
        if ($questionNumber) {
            $question->number = $questionNumber;
        }

        if ($categories) {
            $question->categories()->sync($categories);
        }

        if ($options) {
            $question->options()->delete();
            array_walk($options, function (&$answer) use ($question) {
                $question->options()->create([
                    'text'    => $answer['text'],
                    'correct' => $answer['correct'] ?? false,
                ]);
            });
        }

        $question->save();

        return $question;
    }

    public function delete($companyId, $questionId)
    {
        $question = Question::where(['id' => $questionId, 'company_id' => $companyId])->first();
        $question->options()->delete();
        $question->delete();
    }
}