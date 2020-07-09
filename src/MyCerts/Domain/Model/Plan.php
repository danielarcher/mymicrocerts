<?php

namespace MyCerts\Domain\Model;

class Plan extends BaseModel
{
    protected $table = 'plan';

    protected $fillable = ['name','description','price', 'credits', 'api_requests_per_hour'];

    protected $hidden = ['created_at','updated_at', 'active', 'deleted_at'];
}