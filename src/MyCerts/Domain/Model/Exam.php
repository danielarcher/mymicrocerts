<?php

namespace MyCerts\Domain\Model;

use Mattiasgeniar\Percentage\Percentage;

/**
 * @property string       company_id
 * @property string       title
 * @property string       description
 * @property int          max_time_in_minutes
 * @property int          max_attempts_per_candidate
 * @property int          success_score_in_percent
 * @property boolean      visible_internal
 * @property boolean      visible_external
 * @property boolean      private
 * @property string       access_id
 * @property string       access_password
 * @property Company      company
 * @property mixed|string link
 * @method static where(string[] $array)
 */
class Exam extends BaseModel
{
    protected $table = 'exam';

    protected $casts = [
        'dynamic_fields' => 'array',
        'custom'         => 'json',
        'rewards'         => 'json'
    ];

    protected $appends = [
        'categories'
    ];

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'max_time_in_minutes',
        'max_attempts_per_candidate',
        'success_score_in_percent',
        'visible_internal',
        'visible_external',
        'questions_per_categories',
        'fixed_questions',
        'private',
        'access_id',
        'access_password',
        'categories',
        'custom',
        'rewards',
    ];

    protected $hidden = ['created_at', 'updated_at', 'access_password', 'deleted_at'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function checkIsApproved(int $score)
    {
        $scoreInPercent = Percentage::calculate($score, $this->numberOfQuestions());
        return $scoreInPercent >= $this->success_score_in_percent;
    }

    public function numberOfQuestions()
    {
        $sumFromCategories = array_sum(array_map(function ($category) {
            return $category['pivot']['quantity_of_questions'];
        }, $this->questionsPerCategory()->get()->toArray()));

        return $this->fixedQuestions()->count() + $sumFromCategories;
    }

    public function questionsPerCategory()
    {
        return $this->belongsToMany(Category::class, 'exam_category')->withPivot('quantity_of_questions');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class, 'exam_id');
    }

    public function fixedQuestions()
    {
        return $this->belongsToMany(Question::class, 'exam_question');
    }

    public function getCategoriesAttribute()
    {
        return $this->questionsPerCategory()->get()->map(function ($item) {
            return $item->name;
        })->toArray();
    }
}