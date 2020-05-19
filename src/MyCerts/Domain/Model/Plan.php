<?php

namespace MyCerts\Domain\Model;

class Plan extends BaseModel
{
    protected $table = 'plan';

    protected $fillable = ['name','description','price', 'credits'];

    protected $hidden = ['created_at','updated_at', 'active'];
}