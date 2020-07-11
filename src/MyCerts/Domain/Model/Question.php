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

    protected $hidden = ['created_at','updated_at', 'company_id'];

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'question_category');
    }

    public function correctOptionsOnly(): array
    {
        /** @var Collection $options */
        $options = $this->options;
        return array_filter($options->all(), function($option) {
            return $option->correct;
        });
    }

    public function correctOptionsGrouped()
    {
        return array_map(function($option) {
            return $option->id;
        }, $this->correctOptionsOnly());
    }
}