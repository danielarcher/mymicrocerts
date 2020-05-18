<?php

namespace MyCerts\Domain\Model;

class Plan extends BaseModel
{
    protected $table = 'plan';

    protected $fillable = ['name','description','price', 'max_users', 'exams_per_month'];

    protected $hidden = ['created_at','updated_at', 'active'];
}