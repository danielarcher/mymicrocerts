<?php

namespace MyCerts\Application;

use MyCerts\Domain\Model\Option;
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
            $option = new Option([
                'question_id' => $question->id,
                'text'        => $answer['text'],
                'correct'     => $answer['correct'] ?? false,
            ]);
            $option->save();
        });

        return $question;
    }
}