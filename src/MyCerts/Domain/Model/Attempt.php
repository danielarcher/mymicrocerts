<?php

namespace MyCerts\Domain\Model;

use Illuminate\Support\Facades\Log;

class Attempt extends BaseModel
{
    protected $table = 'attempt';

    protected $fillable = [
        'exam_id',
        'candidate_id',
        'score_in_percent',
        'score_absolute',
        'finished_at',
        'approved',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }

    public function drawnQuestions()
    {
        return $this->belongsToMany(Question::class, 'attempt_drawn_questions');
    }

    public function calculateScore(array $answers): int
    {
        $score = 0;
        $answers = $this->transformAnswersInAssociativeArray($answers);
        Log::info('answers', $answers);
        foreach ($this->drawnQuestions()->get() as $question) {
            Log::debug("checking...", ['question_id', $question->id]);
            /** @var Question $question */
            if ($question->isCorrectAnswer($answers[$question->id] ?? [])) {
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
}