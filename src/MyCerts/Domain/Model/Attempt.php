<?php

namespace MyCerts\Domain\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * @property mixed finished_at
 */
class Attempt extends BaseModel
{
    protected $table = 'attempt';

    protected $casts = [
        'dynamic_fields' => 'array',
    ];

    protected $appends = [
        'remaining_time_in_seconds'
    ];

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

    public function calculateScore(): int
    {
        foreach ($this->drawnQuestions()->withPivot(['correct_answer','received_answer'])->get() as $question) {
            $isCorrect = $question->pivot->correct_answer == $question->pivot->received_answer;
            $this->drawnQuestions()->updateExistingPivot($question->id, ['is_correct' => $isCorrect]);
        }

        return $this->drawnQuestions()->wherePivot('is_correct', '=', true)->count();
    }

    public function getRemainingTimeInSecondsAttribute()
    {
        $limitDate = Carbon::parse($this->created_at)->addMinutes($this->exam()->first()->max_time_in_minutes);

        return Carbon::now()->diffInSeconds($limitDate);
    }

    public function timeForCompletion()
    {
        return Carbon::parse($this->created_at)->diffInSeconds(Carbon::parse($this->finished_at));
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