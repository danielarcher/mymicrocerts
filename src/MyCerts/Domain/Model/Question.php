<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static where(array $array)
 * @method first()
 */
class Question extends BaseModel
{
    protected $table = 'question';

    protected $fillable = [
        'number',
        'description',
        'company_id',
    ];

    protected $hidden = ['created_at', 'updated_at', 'company_id', 'deleted_at', 'pivot'];

    public function options(): hasMany
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'question_category');
    }

    public function isCorrectAnswer(array $selectedOptions): bool
    {
        sort($selectedOptions);
        return $selectedOptions == $this->correctOptionsGrouped();
    }

    public function correctOptionsGrouped()
    {
        return array_map(function ($option) {
            return $option['id'];
        }, $this->correctOptions()->get()->toArray());
    }

    public function correctOptions()
    {
        return $this->hasMany(Option::class, 'question_id')->where('correct', true);
    }
}