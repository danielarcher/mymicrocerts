<?php

namespace MyCerts\Domain\Model;

/**
 * @method static where(array $array)
 */
class Category extends BaseModel
{
    protected $table = 'category';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'icon',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'company_id',
    ];

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_category');
    }
}