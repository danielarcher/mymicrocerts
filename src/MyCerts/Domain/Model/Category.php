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
        'company_id',
    ];
}