<?php

namespace MyCerts\Domain\Model;

class ApiKey extends BaseModel
{
    protected $table = 'company_api_keys';

    protected $fillable = [
        'company_id',
        'name',
        'key',
    ];
    protected $hidden = [
        'company_id',
        'key',
        'updated_at',
        'deleted_at',
    ];
}