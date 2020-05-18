<?php

namespace MyCerts\Domain\Model;

class Option extends BaseModel
{
    protected $table = 'option';

    protected $fillable = [
        'question_id',
        'text',
        'correct',
    ];
    protected $hidden = [
        'correct',
        'created_at',
        'updated_at',
        'question_id',
    ];
}