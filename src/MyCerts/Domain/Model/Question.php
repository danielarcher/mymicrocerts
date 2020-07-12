<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Collection;

class Question extends BaseModel
{
    protected $table = 'question';

    protected $fillable = [
        'number',
        'description',
        'company_id',
    ];

    protected $hidden = ['created_at','updated_at', 'company_id', 'deleted_at', 'pivot'];

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function correctOptions()
    {
        return $this->hasMany(Option::class, 'question_id')->where('correct', true);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'question_category');
    }

    public function correctOptionsGrouped()
    {
        return array_map(function($option) {
            return $option['id'];
        }, $this->correctOptions()->get()->toArray());
    }

    public function isCorrectAnswer(array $selectedOptions): bool
    {
        sort($selectedOptions);
        return $selectedOptions == $this->correctOptionsGrouped();
    }
}