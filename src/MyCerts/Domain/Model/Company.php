<?php

namespace MyCerts\Domain\Model;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Company extends BaseModel
{
    protected $table = 'company';

    protected $fillable = ['name','country','plan_id'];

    public function plans()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}