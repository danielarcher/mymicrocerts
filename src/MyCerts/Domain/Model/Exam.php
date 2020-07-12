<?php

namespace MyCerts\Domain\Model;


use Illuminate\Database\Eloquent\Relations\HasMany;
use Mattiasgeniar\Percentage\Percentage;

/**
 * @property string company_id
 * @property string title
 * @property string description
 * @property int max_time_in_minutes
 * @property int max_attempts_per_candidate
 * @property int success_score_in_percent
 * @property boolean visible_internal
 * @property boolean visible_external
 * @property boolean private
 * @property string access_id
 * @property string access_password
 * @property HasMany questions
 */
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
        'access_id',
        'access_password',
    ];

    protected $hidden = ['created_at','updated_at','access_password'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function fixedQuestions()
    {
        return $this->belongsToMany(Question::class, 'exam_question');
    }

    public function questionsPerCategory()
    {
        return $this->belongsToMany(Category::class, 'exam_category')->withPivot('quantity_of_questions');
    }

    public function numberOfQuestions()
    {
        $sumFromCategories = array_sum(array_map(function ($category) {
            return $category['pivot']['quantity_of_questions'];
        }, $this->questionsPerCategory()->get()->toArray()));

        return $this->fixedQuestions()->count() + $sumFromCategories;
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

    public function checkIsApproved(int $score)
    {
        $scoreInPercent = Percentage::calculate($score, $this->questions()->count());
        return $scoreInPercent >= $this->success_score_in_percent;
    }
}