<?php

namespace MyCerts\Domain\Model;

/**
 * @property mixed company_id
 * @property mixed name
 * @property mixed description
 * @property mixed icon
 * @property mixed custom
 */
class Category extends BaseModel
{
    protected $table = 'category';

    protected $guarded = [];

    protected $casts = [
        'custom' => 'json'
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