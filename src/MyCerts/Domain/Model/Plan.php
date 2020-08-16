<?php

namespace MyCerts\Domain\Model;

/**
 * @property mixed name
 * @property mixed description
 * @property mixed price
 * @property mixed credits
 */
class Plan extends BaseModel
{
    protected $table = 'plan';

    protected $fillable = ['name','description','price', 'credits', 'api_requests_per_hour'];

    protected $hidden = ['created_at','updated_at', 'active', 'deleted_at'];
}