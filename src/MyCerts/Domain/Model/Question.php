<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Collection;

class Question extends BaseModel
{
    protected $table = 'question';

    protected $fillable = [
        'exam_id',
        'number',
        'description'
    ];

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
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