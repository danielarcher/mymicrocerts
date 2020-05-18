<?php

namespace MyCerts\Domain\Model;

class Exam extends BaseModel
{
    protected $table = 'exam';

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'max_time_in_minutes',
        'max_attempts_per_candidate',
        'success_score_in_percent',
        'visible_internal',
        'visible_external',
        'private',
        'access_code',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'exam_id', 'id');
    }

    public function questionsAsAssociativeArray()
    {
        $return = [];
        foreach ($this->questions as $question) {
            /** @var Question $question */
            $optionsGrouped = $question->correctOptionsGrouped();
            sort($optionsGrouped);
            $return[$question['id']] = $optionsGrouped;
        }
        return $return;
    }

    public function calculateScore(array $answers)
    {
        $candidateAnswers = $this->transformAnswersInAssociativeArray($answers);
        $questions = $this->questionsAsAssociativeArray();
        $score = 0;

        foreach ($questions as $questionId => $expectedAnswers) {
            $candidateAnswersForThisQuestion = $candidateAnswers[$questionId] ?? [];
            sort($candidateAnswersForThisQuestion);
            if ($expectedAnswers == $candidateAnswersForThisQuestion ?? null) {
                $score++;
            }
        }
        return $score;
    }

    protected function transformAnswersInAssociativeArray($answers): array
    {
        $return = null;
        foreach ($answers as $answer) {
            if (empty($answer['question_id'])) continue;
            $return[$answer['question_id']] = $answer['selected_option_ids'];
        }
        return $return;
    }

    public function checkIsApproved(int $scoreInPercent)
    {
        return $scoreInPercent >= $this->success_score_in_percent;
    }
}