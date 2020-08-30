<?php

namespace MyCerts\Domain\Model;

class Category extends BaseModel
{
    protected $table = 'category';

    protected $fillable = [
        'company_id',
        'name',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'company_id',
        #'pivot',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_category');
    }
}